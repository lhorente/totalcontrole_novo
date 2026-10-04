<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\OrcamentoItem;
use App\Services\OrcamentoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrcamentoController extends Controller
{
  private OrcamentoService $service;

  public function __construct(OrcamentoService $service)
  {
    $this->service = $service;
  }

  public function index(Request $request, $ano = null){
    $ano  = (int) ($ano ?? date('Y'));
    $modo = in_array($request->input('modo'), ['real', 'dif']) ? $request->input('modo') : 'plan';

    $orcamento = Orcamento::where('ano', $ano)->with('itens.category')->first();
    if (!$orcamento) {
      $anoAnterior = Orcamento::where('ano', $ano - 1)->exists();
      return view('orcamento/index', compact('ano', 'orcamento', 'modo', 'anoAnterior'));
    }

    $itens = $orcamento->itens;
    $realizado = $modo === 'plan' ? null : $this->service->realizadoAno($orcamento);

    // Valor mostrado na célula: planejado; realizado (só até o mês corrente); ou realizado − planejado
    $mesCorrente = $ano < now()->year ? 12 : ($ano > now()->year ? 0 : now()->month);
    $valorCelula = function (OrcamentoItem $item, int $mes) use ($modo, $realizado, $mesCorrente) {
      $plan = $item->valorMes($mes);
      if ($modo === 'plan' || $mes > $mesCorrente) {
        return ['valor' => $plan, 'plan' => $plan, 'real' => null];
      }
      $real = $realizado[$mes]['itens'][$item->id] ?? 0;
      return ['valor' => $modo === 'real' ? $real : $real - $plan, 'plan' => $plan, 'real' => $real];
    };

    $grade = collect(Orcamento::GRUPOS)->map(function ($nome, $grupo) use ($itens, $valorCelula) {
      $linhas = $itens->where('grupo', $grupo)->map(function ($item) use ($valorCelula) {
        $celulas = [];
        for ($m = 1; $m <= 12; $m++) {
          $celulas[$m] = $valorCelula($item, $m);
        }
        return ['item' => $item, 'celulas' => $celulas, 'total' => array_sum(array_column($celulas, 'valor'))];
      })->values();

      $totais = [];
      for ($m = 1; $m <= 12; $m++) {
        $totais[$m] = $linhas->sum(fn($l) => $l['celulas'][$m]['valor']);
      }

      return ['nome' => $nome, 'linhas' => $linhas, 'totais' => $totais, 'total' => array_sum($totais)];
    });

    $planejadoMes = [];
    for ($m = 1; $m <= 12; $m++) {
      $planejadoMes[$m] = $itens->sum(fn($i) => $i->valorMes($m));
    }
    $planejadoAno = array_sum($planejadoMes);
    $rendaAno     = $orcamento->rendaAno();
    $sobraMes     = array_map(fn($m) => $orcamento->rendaMes($m) - $planejadoMes[$m], array_combine(range(1, 12), range(1, 12)));
    $mesMaisApertado = $itens->isEmpty() ? null : array_search(min($sobraMes), $sobraMes);

    // Metas por grupo: planejado médio por mês contra a meta (% da renda média mensal)
    $rendaMedia = $rendaAno / 12;
    $metas = collect(Orcamento::GRUPOS)->map(function ($nome, $grupo) use ($orcamento, $itens, $rendaMedia) {
      $pct  = (float) ($orcamento->metas[$grupo] ?? 0);
      $meta = $rendaMedia * $pct / 100;
      $plan = $itens->where('grupo', $grupo)->sum(fn($i) => $i->totalAno()) / 12;
      return [
        'nome'     => $nome,
        'meta_pct' => $pct,
        'meta'     => $meta,
        'plan'     => $plan,
        'plan_pct' => $rendaMedia > 0 ? $plan / $rendaMedia * 100 : 0,
      ];
    });

    $provisoesManuais = $this->service->provisoesManuais($ano);

    return view('orcamento/index', compact(
      'ano',
      'orcamento',
      'modo',
      'grade',
      'planejadoMes',
      'planejadoAno',
      'rendaAno',
      'sobraMes',
      'mesMaisApertado',
      'mesCorrente',
      'metas',
      'provisoesManuais'
    ));
  }

  public function store($ano){
    $ano = (int) $ano;
    $orcamento = Orcamento::firstOrCreate(
      ['id_workspace' => session('active_workspace_id'), 'ano' => $ano],
      ['id_usuario' => Auth::id(), 'renda' => array_fill(0, 12, 0), 'metas' => Orcamento::METAS_PADRAO]
    );

    return redirect()->route('orcamento.config', $orcamento->ano)
      ->with('success', 'Orçamento '.$ano.' criado. Comece pela renda prevista.');
  }

  public function config($ano){
    $orcamento = Orcamento::where('ano', (int) $ano)->firstOrFail();

    return view('orcamento/config', compact('orcamento'));
  }

  public function updateConfig(Request $request, $ano){
    $orcamento = Orcamento::where('ano', (int) $ano)->firstOrFail();

    $request->validate([
      'renda'   => 'required|array|size:12',
      'renda.*' => 'nullable|numeric|min:0',
      'metas'   => 'required|array',
      'metas.*' => 'nullable|numeric|min:0|max:100',
    ]);

    $orcamento->renda = array_map(fn($v) => round((float) $v, 2), array_values($request->input('renda')));
    $orcamento->metas = collect(Orcamento::GRUPOS)->keys()->mapWithKeys(fn($g) => [$g => (float) $request->input("metas.$g", 0)])->all();
    $orcamento->save();

    return redirect()->route('orcamento.index', $orcamento->ano)->with('success', 'Renda e metas salvas.');
  }

  public function createItem(Request $request, $ano){
    $orcamento = Orcamento::where('ano', (int) $ano)->firstOrFail();
    $item = new OrcamentoItem([
      'grupo'        => $request->input('grupo', 'necessidades'),
      'id_categoria' => $request->input('categoria'),
      'tipo'         => 'fixo',
      'distribuicao' => 'no_mes',
      'valores'      => array_fill(0, 12, 0),
    ]);
    $categorias = OrcamentoService::categorias();

    return view('orcamento/item', compact('orcamento', 'item', 'categorias'));
  }

  public function storeItem(Request $request, $ano){
    $orcamento = Orcamento::where('ano', (int) $ano)->firstOrFail();
    $item = new OrcamentoItem([
      'id_orcamento' => $orcamento->id,
      'id_workspace' => $orcamento->id_workspace,
      'ordem'        => ($orcamento->itens()->max('ordem') ?? 0) + 1,
    ]);
    $this->salvarItem($request, $item);

    return redirect()->route('orcamento.index', $orcamento->ano)->with('success', 'Item "'.e($item->nome).'" adicionado.');
  }

  public function editItem($id){
    $item = OrcamentoItem::with('subitens', 'orcamento')->findOrFail($id);
    $orcamento = $item->orcamento;
    $categorias = OrcamentoService::categorias();

    return view('orcamento/item', compact('orcamento', 'item', 'categorias'));
  }

  public function updateItem(Request $request, $id){
    $item = OrcamentoItem::with('orcamento')->findOrFail($id);
    $this->salvarItem($request, $item);

    return redirect()->route('orcamento.index', $item->orcamento->ano)->with('success', 'Item "'.e($item->nome).'" salvo.');
  }

  public function destroyItem($id){
    $item = OrcamentoItem::with('orcamento')->findOrFail($id);
    $item->delete();

    return redirect()->route('orcamento.index', $item->orcamento->ano)->with('success', 'Item "'.e($item->nome).'" excluído.');
  }

  private function salvarItem(Request $request, OrcamentoItem $item): void
  {
    $request->validate([
      'nome'                 => 'required|string|max:255',
      'grupo'                => ['required', Rule::in(array_keys(Orcamento::GRUPOS))],
      'id_categoria'         => 'nullable|integer',
      'padrao_descricao'     => 'nullable|string|max:255',
      'tipo'                 => ['required', Rule::in(array_keys(OrcamentoItem::TIPOS))],
      'valor_fixo'           => 'nullable|required_if:tipo,fixo|numeric|min:0',
      'valores'              => 'nullable|array',
      'valores.*'            => 'nullable|numeric|min:0',
      'distribuicao'         => ['nullable', Rule::in(array_keys(OrcamentoItem::DISTRIBUICOES))],
      'subitens'             => 'nullable|array',
      'subitens.*.mes'       => 'required_with:subitens.*.valor|integer|between:1,12',
      'subitens.*.descricao' => 'nullable|string|max:255',
      'subitens.*.valor'     => 'nullable|numeric|min:0',
    ]);

    DB::transaction(function () use ($request, $item) {
      $item->fill($request->only('nome', 'grupo', 'padrao_descricao', 'tipo'));
      $item->id_categoria = $request->input('id_categoria') ?: null;
      $item->valor_fixo   = $item->tipo === 'fixo' ? $request->input('valor_fixo') : null;
      $item->distribuicao = $item->tipo === 'detalhado' ? $request->input('distribuicao', 'no_mes') : null;
      $item->valores      = $item->valores ?? array_fill(0, 12, 0);
      $item->save();

      $item->subitens()->delete();
      if ($item->tipo === 'detalhado') {
        foreach ((array) $request->input('subitens', []) as $sub) {
          if (($sub['valor'] ?? '') === '' || empty($sub['mes'])) {
            continue;
          }
          $item->subitens()->create([
            'mes'       => (int) $sub['mes'],
            'descricao' => trim((string) ($sub['descricao'] ?? '')) ?: '—',
            'valor'     => (float) $sub['valor'],
          ]);
        }
      }

      $item->load('subitens');
      $item->recalcularValores(array_values((array) $request->input('valores', [])));
      $item->save();
    });
  }

  public function mes($ano, $mes){
    $ano = (int) $ano;
    $mes = (int) $mes;
    abort_unless($mes >= 1 && $mes <= 12, 404);

    $orcamento = Orcamento::where('ano', $ano)->with('itens.category')->firstOrFail();
    $realizado = $this->service->realizadoMes($orcamento, $mes);
    $mesPassado = \Carbon\Carbon::create($ano, $mes, 1)->lt(now()->startOfMonth());

    $linhas = $orcamento->itens->map(function ($item) use ($mes, $realizado) {
      $plan = $item->valorMes($mes);
      $real = $realizado['itens'][$item->id] ?? 0;
      if ($real > $plan + 0.005) {
        $situacao = 'estourou';
      } elseif ($real < $plan - 0.005) {
        $situacao = 'aberto';
      } else {
        $situacao = 'ok';
      }
      return ['item' => $item, 'plan' => $plan, 'real' => $real, 'qtd' => $realizado['qtd'][$item->id] ?? 0, 'situacao' => $situacao];
    })
      ->reject(fn($l) => $l['plan'] == 0 && $l['real'] == 0)
      ->sortBy(fn($l) => [array_search($l['situacao'], ['estourou', 'aberto', 'ok']), -$l['plan']])
      ->values();

    $planejado  = $linhas->sum('plan');
    $jaSaiu     = $linhas->sum('real');
    $emAberto   = $linhas->sum(fn($l) => max(0, $l['plan'] - $l['real']));
    $fora       = $realizado['fora'];
    $provisoesManuais = $this->service->provisoesManuais($ano)
      ->filter(fn($p) => $p['valores'][$mes - 1] > 0);

    return view('orcamento/mes', compact('orcamento', 'ano', 'mes', 'mesPassado', 'linhas', 'planejado', 'jaSaiu', 'emAberto', 'fora', 'provisoesManuais'));
  }

  public function provisoes($ano){
    $orcamento = Orcamento::where('ano', (int) $ano)->with('itens')->first();
    $provisoes = $this->service->provisoesManuais((int) $ano);
    $itensPorNome = $orcamento
      ? $orcamento->itens->keyBy(fn($i) => \App\Models\TransactionMapping::normalize($i->nome))
      : collect();

    return view('orcamento/provisoes', compact('ano', 'orcamento', 'provisoes', 'itensPorNome'));
  }

  public function converterProvisoes(Request $request, $ano){
    $ano = (int) $ano;
    $orcamento = Orcamento::firstOrCreate(
      ['id_workspace' => session('active_workspace_id'), 'ano' => $ano],
      ['id_usuario' => Auth::id(), 'renda' => array_fill(0, 12, 0), 'metas' => Orcamento::METAS_PADRAO]
    );

    $escolhidos = collect((array) $request->input('converter', []))
      // grupo[] vem indexado pelo md5 do nome: o nome normalizado pode ter "." e quebraria a notação de ponto do input()
      ->mapWithKeys(fn($chave) => [$chave => $request->input('grupo.'.md5($chave))])
      ->all();

    $n = $this->service->converterProvisoes($orcamento, $escolhidos);

    return redirect()->route('orcamento.index', $ano)
      ->with('success', count($escolhidos).' provisões viraram itens do orçamento; '.$n.' lançamentos "Prov" foram cancelados.');
  }
}
