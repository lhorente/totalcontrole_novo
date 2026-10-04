<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Orcamento;
use App\Models\OrcamentoItem;
use App\Models\Transaction;
use App\Models\TransactionMapping;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Cruza as despesas lançadas com os itens do orçamento (as provisões).
 *
 * Cada despesa vai para no máximo um item:
 *  1. o item cujo "descrição contém" (padrao_normalizado) aparece na descrição ou na
 *     descrição do banco — o padrão mais longo vence, como no De <> Para;
 *  2. senão, o item sem padrão da mesma categoria. Se vários itens sem padrão dividem
 *     a categoria, o valor é rateado entre eles na proporção do planejado no mês;
 *  3. senão, fica "fora do orçamento".
 *
 * Lançamentos "Prov …" são provisões antigas digitadas à mão: não contam como gasto real.
 */
class OrcamentoService
{
  const PREFIXO_PROV = 'Prov ';

  public static function isProv(Transaction $t): bool
  {
    return stripos(ltrim((string) $t->descricao), self::PREFIXO_PROV) === 0;
  }

  /**
   * Realizado do ano inteiro, por mês: [mes => ['itens' => [id_item => valor], 'qtd' => [id_item => n], 'fora' => Collection]]
   */
  public function realizadoAno(Orcamento $orcamento): array
  {
    $despesas = Transaction::search(['year' => $orcamento->ano, 'tipo' => 'despesa'])
      ->reject(fn($t) => self::isProv($t))
      ->groupBy(fn($t) => (int) $t->data->format('n'));

    $itens = $orcamento->itens;
    $resultado = [];
    for ($mes = 1; $mes <= 12; $mes++) {
      $resultado[$mes] = $this->distribuir($itens, $despesas->get($mes, collect()), $mes);
    }

    return $resultado;
  }

  public function realizadoMes(Orcamento $orcamento, int $mes, bool $incluirProv = false): array
  {
    $despesas = Transaction::search(['year' => $orcamento->ano, 'month' => $mes, 'tipo' => 'despesa'])
      ->reject(fn($t) => !$incluirProv && self::isProv($t));

    return $this->distribuir($orcamento->itens, $despesas, $mes);
  }

  /**
   * Quanto do planejado ainda não saiu no mês (provisões em aberto). Só faz sentido no
   * mês corrente e nos futuros: num mês que já passou, o que não saiu não vai mais sair.
   */
  public function provisoesEmAberto(int $ano, int $mes): float
  {
    if (Carbon::create($ano, $mes, 1)->lt(now()->startOfMonth())) {
      return 0;
    }

    $orcamento = Orcamento::where('ano', $ano)->with('itens')->first();
    if (!$orcamento) {
      return 0;
    }

    // Aqui um "Prov" ainda não convertido conta como coberto: ele já entra no Nosso Mês como
    // lançamento a pagar, e somar a provisão do item de novo duplicaria o valor
    $realizado = $this->realizadoMes($orcamento, $mes, true);

    return $orcamento->itens->sum(fn($item) => max(0, $item->valorMes($mes) - ($realizado['itens'][$item->id] ?? 0)));
  }

  private function distribuir(Collection $itens, Collection $despesas, int $mes): array
  {
    $comPadrao = $itens->filter(fn($i) => $i->padrao_normalizado)->sortByDesc(fn($i) => strlen($i->padrao_normalizado));
    $semPadraoPorCategoria = $itens->filter(fn($i) => !$i->padrao_normalizado && $i->id_categoria)->groupBy('id_categoria');

    $porItem = [];
    $qtd = [];
    $fora = [];

    foreach ($despesas as $t) {
      $texto = TransactionMapping::normalize((string) $t->descricao).' | '.TransactionMapping::normalize((string) $t->descricao_banco);
      $item = $comPadrao->first(fn($i) => str_contains($texto, $i->padrao_normalizado));

      if ($item) {
        $porItem[$item->id] = ($porItem[$item->id] ?? 0) + $t->valor;
        $qtd[$item->id] = ($qtd[$item->id] ?? 0) + 1;
        continue;
      }

      $candidatos = $semPadraoPorCategoria->get($t->id_categoria);
      if ($candidatos && $candidatos->isNotEmpty()) {
        $planejado = $candidatos->sum(fn($i) => $i->valorMes($mes));
        foreach ($candidatos as $c) {
          $peso = $planejado > 0 ? $c->valorMes($mes) / $planejado : 1 / $candidatos->count();
          $porItem[$c->id] = ($porItem[$c->id] ?? 0) + $t->valor * $peso;
          $qtd[$c->id] = ($qtd[$c->id] ?? 0) + 1;
        }
        continue;
      }

      $chave = $t->id_categoria ?: 0;
      $fora[$chave] = $fora[$chave] ?? ['id_categoria' => $t->id_categoria, 'nome' => optional($t->category)->nome ?? 'Sem categoria', 'qtd' => 0, 'total' => 0];
      $fora[$chave]['qtd']++;
      $fora[$chave]['total'] += $t->valor;
    }

    return [
      'itens' => $porItem,
      'qtd'   => $qtd,
      'fora'  => collect($fora)->sortByDesc('total')->values(),
    ];
  }

  /**
   * Lançamentos "Prov …" do ano ainda ativos, agrupados pelo nome (sem o prefixo):
   * cada grupo vira um item do orçamento com o valor de cada mês.
   */
  public function provisoesManuais(int $ano): Collection
  {
    return Transaction::search(['year' => $ano, 'tipo' => 'despesa'])
      ->filter(fn($t) => self::isProv($t))
      ->groupBy(fn($t) => TransactionMapping::normalize(substr(ltrim($t->descricao), strlen(self::PREFIXO_PROV))))
      ->map(function ($g, $chave) {
        $valores = array_fill(0, 12, 0.0);
        foreach ($g as $t) {
          $valores[(int) $t->data->format('n') - 1] += (float) $t->valor;
        }
        $idCategoria = $g->groupBy('id_categoria')->sortByDesc(fn($c) => $c->count())->keys()->first();

        return [
          'chave'        => $chave,
          'nome'         => trim(substr(ltrim($g->first()->descricao), strlen(self::PREFIXO_PROV))),
          'id_categoria' => $idCategoria ?: null,
          'categoria'    => optional(optional($g->firstWhere('id_categoria', $idCategoria))->category)->nome,
          'valores'      => $valores,
          'total'        => array_sum($valores),
          'ids'          => $g->pluck('id')->all(),
        ];
      })
      ->sortBy('nome')
      ->values();
  }

  /**
   * Converte os "Prov …" escolhidos em itens do orçamento (somando num item de mesmo nome,
   * se já existir) e cancela os lançamentos — eles continuam no histórico, como cancelados.
   *
   * @param array $grupos chave normalizada => grupo do orçamento
   */
  public function converterProvisoes(Orcamento $orcamento, array $grupos): int
  {
    $provisoes = $this->provisoesManuais($orcamento->ano)->keyBy('chave');
    $convertidos = 0;

    DB::transaction(function () use ($orcamento, $grupos, $provisoes, &$convertidos) {
      foreach ($grupos as $chave => $grupo) {
        $prov = $provisoes->get($chave);
        if (!$prov || !array_key_exists($grupo, Orcamento::GRUPOS)) {
          continue;
        }

        $item = $orcamento->itens()->get()->first(fn($i) => TransactionMapping::normalize($i->nome) === $chave);
        if ($item) {
          // Os meses que tinham "Prov" passam a valer o que estava provisionado; os demais ficam como estavam
          $valores = $item->valores;
          foreach ($prov['valores'] as $i => $v) {
            if ($v > 0) {
              $valores[$i] = round($v, 2);
            }
          }
          $item->tipo = 'mensal';
          $item->valores = $valores;
          $item->save();
        } else {
          $item = new OrcamentoItem([
            'id_orcamento' => $orcamento->id,
            'id_workspace' => $orcamento->id_workspace,
            'grupo'        => $grupo,
            'nome'         => $prov['nome'],
            'id_categoria' => $prov['id_categoria'],
            'tipo'         => 'mensal',
            'ordem'        => ($orcamento->itens()->max('ordem') ?? 0) + 1,
          ]);
          $item->recalcularValores($prov['valores']);
          $item->save();
        }

        $convertidos += Transaction::whereIn('id', $prov['ids'])->update(['status' => 'cancelado']);
      }
    });

    return $convertidos;
  }

  /** Categorias ativas do workspace, para os selects. */
  public static function categorias(): Collection
  {
    return Category::where('id_workspace', session('active_workspace_id'))
      ->where('status', 'a')
      ->orderBy('nome')
      ->get();
  }
}
