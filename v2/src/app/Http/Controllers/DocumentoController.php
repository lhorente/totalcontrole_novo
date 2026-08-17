<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Contact;
use App\Http\Requests\StoreDocumento;
use Illuminate\Support\Facades\Auth;

class DocumentoController extends Controller
{
  const MODULO = 'documento';

  public function index(){
    $documentos = Evento::modulo(self::MODULO)
      ->with('documento', 'contact')
      ->orderBy('data_vencimento')
      ->get();

    return view('documentos/index', compact('documentos'));
  }

  public function create(){
    $contacts = Contact::getCurrentUserContacts();

    return view('documentos/create', compact('contacts'));
  }

  public function store(StoreDocumento $request){
    $evento = new Evento;
    $evento->id_workspace = session('active_workspace_id');
    $evento->id_usuario   = Auth::id();
    $evento->modulo       = self::MODULO;

    $this->fillEvento($evento, $request);
    $evento->save();

    $evento->documento()->updateOrCreate([], [
      'numero_documento' => $request->input('numero_documento'),
      'emissor'          => $request->input('emissor'),
      'arquivo_url'      => $request->input('arquivo_url'),
      'observacoes'      => $request->input('observacoes'),
    ]);

    return redirect('/documentos')->with('success', 'Documento salvo com sucesso');
  }

  public function edit($id){
    $documento = Evento::modulo(self::MODULO)->with('documento')->findOrFail($id);
    $contacts = Contact::getCurrentUserContacts();

    return view('documentos/edit', compact('documento', 'contacts'));
  }

  public function update(StoreDocumento $request, $id){
    $evento = Evento::modulo(self::MODULO)->findOrFail($id);

    $this->fillEvento($evento, $request);
    $evento->save();

    $evento->documento()->updateOrCreate([], [
      'numero_documento' => $request->input('numero_documento'),
      'emissor'          => $request->input('emissor'),
      'arquivo_url'      => $request->input('arquivo_url'),
      'observacoes'      => $request->input('observacoes'),
    ]);

    return redirect('/documentos')->with('success', 'Documento salvo com sucesso');
  }

  public function destroy($id){
    $evento = Evento::modulo(self::MODULO)->findOrFail($id);

    if ($evento->delete()){
      return redirect('/documentos')->with('success', 'Documento excluído com sucesso');
    } else {
      return redirect('/documentos')->with('error', 'Não foi possível excluir o documento');
    }
  }

  private function fillEvento(Evento $evento, StoreDocumento $request){
    $evento->tipo             = $request->input('tipo');
    $evento->titulo           = $request->input('titulo');
    $evento->data_evento      = $request->input('data_evento');
    $evento->data_vencimento  = $request->input('data_vencimento') ?: null;
    $evento->id_cliente       = $request->input('id_cliente') ?: null;
    $evento->valor            = $request->input('valor') ?: null;
    $evento->status           = $request->input('status', 'ativo');
  }
}
