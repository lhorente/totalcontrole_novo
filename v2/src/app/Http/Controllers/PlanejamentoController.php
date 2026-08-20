<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Bem;
use App\Http\Requests\StorePlanejamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    return view('planejamento/index', compact('itens', 'bens', 'tipo', 'idBem', 'prioridade', 'status'));
  }

  public function create(){
    $bens = Bem::where('ativo', true)->orderBy('nome')->get();

    return view('planejamento/create', compact('bens'));
  }

  public function store(StorePlanejamento $request){
    $evento = new Evento;
    $evento->id_workspace = session('active_workspace_id');
    $evento->id_usuario   = Auth::id();
    $evento->modulo       = self::MODULO;
    $evento->data_evento  = now();

    $this->fillEvento($evento, $request);
    $evento->save();

    $evento->planejamento()->updateOrCreate([], $this->planejamentoData($request));

    return redirect('/planejamento')->with('success', 'Item salvo com sucesso');
  }

  public function edit($id){
    $item = Evento::modulo(self::MODULO)->with('planejamento')->findOrFail($id);
    $bens = Bem::where('ativo', true)->orderBy('nome')->get();

    return view('planejamento/edit', compact('item', 'bens'));
  }

  public function update(StorePlanejamento $request, $id){
    $evento = Evento::modulo(self::MODULO)->findOrFail($id);

    $this->fillEvento($evento, $request);
    $evento->save();

    $evento->planejamento()->updateOrCreate([], $this->planejamentoData($request));

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

    return [
      'id_bem'                => $request->input('id_bem') ?: null,
      'categoria'             => $request->input('categoria') ?: null,
      'prioridade'            => $request->input('prioridade', 'necessidade'),
      'recorrente'            => $recorrente,
      'recorrencia_intervalo' => $recorrente ? $request->input('recorrencia_intervalo') : null,
      'recorrencia_unidade'   => $recorrente ? $request->input('recorrencia_unidade') : null,
      'observacoes'           => $request->input('observacoes') ?: null,
    ];
  }
}
