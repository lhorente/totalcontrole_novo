<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Bem;
use App\Models\Transaction;
use App\Http\Requests\StorePlanejamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PlanejamentoController extends Controller
{
  const MODULO = 'planejamento';

  public function index(Request $request){
    $tipo       = $request->input('tipo');
    $idBem      = $request->input('id_bem');
    $prioridade = $request->input('prioridade');
    $status     = $request->input('status');

    $query = Evento::modulo(self::MODULO)->with('planejamento.bem');

    if ($tipo){
      $query->where('tipo', $tipo);
    }
    if ($status){
      $query->where('status', $status);
    }
    if ($request->filled('id_bem') || $prioridade){
      $query->whereHas('planejamento', function ($q) use ($idBem, $prioridade) {
        if ($idBem === '0'){
          $q->whereNull('id_bem');
        } elseif ($idBem){
          $q->where('id_bem', $idBem);
        }
        if ($prioridade){
          $q->where('prioridade', $prioridade);
        }
      });
    }

    $itens = $query->orderByRaw('data_vencimento IS NULL, data_vencimento')->get();
    $bens  = Bem::orderBy('nome')->get();
    $resumo = $this->resumo();

    return view('planejamento/index', compact('itens', 'bens', 'tipo', 'idBem', 'prioridade', 'status', 'resumo'));
  }

  /**
   * Números do painel de resumo, sempre sobre a base completa (não respeita
   * os filtros da listagem) para servir como um retrato geral do módulo.
   */
  private function resumo(){
    $pendentes = Evento::modulo(self::MODULO)->whereNotIn('status', ['concluido', 'cancelado']);

    return [
      'valor_pendente' => (clone $pendentes)->sum('valor'),
      'atrasados'       => (clone $pendentes)
        ->whereNotNull('data_vencimento')
        ->where('data_vencimento', '<', now()->startOfDay())
        ->count(),
      'proximos_30'     => (clone $pendentes)
        ->whereNotNull('data_vencimento')
        ->whereBetween('data_vencimento', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
        ->count(),
      'bens_cadastrados' => Bem::count(),
    ];
  }

  public function create(){
    $bens = Bem::where('ativo', true)->orderBy('nome')->get();
    $transacoes = $this->transacoesRecentes();

    return view('planejamento/create', compact('bens', 'transacoes'));
  }

  public function store(StorePlanejamento $request){
    $evento = new Evento;
    $evento->id_workspace = session('active_workspace_id');
    $evento->id_usuario   = Auth::id();
    $evento->modulo       = self::MODULO;
    $evento->data_evento  = now();

    $this->fillEvento($evento, $request);
    $evento->save();

    $planData = $this->planejamentoData($request);
    $evento->planejamento()->updateOrCreate([], $planData);

    if ($evento->status === 'concluido' && $planData['recorrente']){
      $this->gerarProximaOcorrencia($evento, $planData);
    }

    return redirect('/planejamento')->with('success', 'Item salvo com sucesso');
  }

  public function edit($id){
    $item = Evento::modulo(self::MODULO)->with('planejamento')->findOrFail($id);
    $bens = Bem::where('ativo', true)->orderBy('nome')->get();
    $transacoes = $this->transacoesRecentes();

    return view('planejamento/edit', compact('item', 'bens', 'transacoes'));
  }

  public function update(StorePlanejamento $request, $id){
    $evento = Evento::modulo(self::MODULO)->findOrFail($id);
    $statusAnterior = $evento->status;

    $this->fillEvento($evento, $request);
    $evento->save();

    $planData = $this->planejamentoData($request);
    $evento->planejamento()->updateOrCreate([], $planData);

    $recemConcluido = $statusAnterior !== 'concluido' && $evento->status === 'concluido';
    if ($recemConcluido && $planData['recorrente']){
      $this->gerarProximaOcorrencia($evento, $planData);
    }

    return redirect('/planejamento')->with('success', 'Item salvo com sucesso');
  }

  public function destroy($id){
    $evento = Evento::modulo(self::MODULO)->findOrFail($id);

    if ($evento->delete()){
      return redirect('/planejamento')->with('success', 'Item excluído com sucesso');
    } else {
      return redirect('/planejamento')->with('error', 'Não foi possível excluir o item');
    }
  }

  private function fillEvento(Evento $evento, StorePlanejamento $request){
    $evento->tipo             = $request->input('tipo');
    $evento->titulo           = $request->input('titulo');
    $evento->data_vencimento  = $request->input('data_vencimento') ?: null;
    $evento->valor            = $request->input('valor') ?: null;
    $evento->status           = $request->input('status', 'planejado');
  }

  private function planejamentoData(StorePlanejamento $request){
    $recorrente = $request->input('tipo') === 'manutencao' && $request->boolean('recorrente');
    $concluido  = $request->input('status') === 'concluido';

    return [
      'id_bem'                => $request->input('id_bem') ?: null,
      'categoria'             => $request->input('categoria') ?: null,
      'prioridade'            => $request->input('prioridade', 'necessidade'),
      'recorrente'            => $recorrente,
      'recorrencia_intervalo' => $recorrente ? $request->input('recorrencia_intervalo') : null,
      'recorrencia_unidade'   => $recorrente ? $request->input('recorrencia_unidade') : null,
      'data_conclusao'        => $concluido ? ($request->input('data_conclusao') ?: null) : null,
      'valor_pago'            => $concluido ? ($request->input('valor_pago') ?: null) : null,
      'id_transacao'          => $concluido ? ($request->input('id_transacao') ?: null) : null,
      'observacoes'           => $request->input('observacoes') ?: null,
    ];
  }

  /**
   * Ao concluir um item recorrente, cria a próxima ocorrência a partir da
   * data de conclusão (ou de hoje, se não informada) + o intervalo configurado,
   * encadeada via id_evento_pai.
   */
  private function gerarProximaOcorrencia(Evento $evento, array $planData){
    $base = $planData['data_conclusao'] ? Carbon::parse($planData['data_conclusao']) : now();

    $proximaData = $planData['recorrencia_unidade'] === 'anos'
      ? $base->copy()->addYears((int) $planData['recorrencia_intervalo'])
      : $base->copy()->addMonths((int) $planData['recorrencia_intervalo']);

    $novoEvento = new Evento;
    $novoEvento->id_workspace    = $evento->id_workspace;
    $novoEvento->id_usuario      = $evento->id_usuario;
    $novoEvento->id_evento_pai   = $evento->id;
    $novoEvento->modulo          = self::MODULO;
    $novoEvento->tipo            = $evento->tipo;
    $novoEvento->titulo          = $evento->titulo;
    $novoEvento->data_evento     = now();
    $novoEvento->data_vencimento = $proximaData;
    $novoEvento->valor           = $evento->valor;
    $novoEvento->status          = 'planejado';
    $novoEvento->save();

    $novoEvento->planejamento()->create([
      'id_bem'                => $planData['id_bem'],
      'categoria'             => $planData['categoria'],
      'prioridade'            => $planData['prioridade'],
      'recorrente'            => true,
      'recorrencia_intervalo' => $planData['recorrencia_intervalo'],
      'recorrencia_unidade'   => $planData['recorrencia_unidade'],
      'observacoes'           => $planData['observacoes'],
    ]);
  }

  private function transacoesRecentes(){
    return Transaction::orderByDesc('data')->limit(200)->get();
  }
}
