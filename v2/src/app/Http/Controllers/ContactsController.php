<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Http\Requests\StoreContact;
use Illuminate\Support\Facades\Auth;

class ContactsController extends Controller
{
    public function index(){
      $contacts = Contact::getCurrentUserContacts();

      return view('contacts/index',compact('contacts'));
    }

    public function create(){
      return view('contacts/create');
    }

    public function edit($id){
      $contact = Contact::getContact($id);
      return view('contacts/edit',compact('contact'));
    }

    public function store(StoreContact $request){
      $contact = new Contact;
      $contact->id_workspace = session('active_workspace_id');
      $contact->id_usuario = Auth::id();

      $this->fillContact($contact, $request);
      $contact->save();
      $this->syncTipoAuxiliar($contact, $request);

      return redirect()->route('contacts.index')->with('success', 'Contato salvo com sucesso');
    }

    public function update(StoreContact $request, $id){
      $contact = Contact::getContact($id);

      if (!$contact){
        return redirect()->route('contacts.index')->with('error', 'Contato não encontrado');
      }

      $this->fillContact($contact, $request);
      $contact->save();
      $this->syncTipoAuxiliar($contact, $request);

      return redirect()->route('contacts.index')->with('success', 'Contato salvo com sucesso');
    }

    public function destroy($id){
      $contact = Contact::getContact($id);
      if (!$contact){
        return redirect()->route('contacts.index')->with('error', 'Contato não encontrado');
      }

      if ($contact->delete()){
        return redirect()->route('contacts.index')->with('success', 'Contato excluído com sucesso');
      } else {
        return redirect()->route('contacts.index')->with('error', 'Não foi possível excluir o contato');
      }
    }

    private function fillContact(Contact $contact, StoreContact $request){
      $contact->nome = $request->input('nome');
      $contact->tipo = $request->input('tipo');
      $contact->status = $request->input('status');
      $contact->documento = $request->input('documento') ?: null;
      $contact->email = $request->input('email') ?: null;
      $contact->telefone = $request->input('telefone') ?: null;
      $contact->observacoes = $request->input('observacoes') ?: null;
    }

    // Mantém apenas a linha auxiliar correspondente ao tipo atual do contato,
    // removendo a de um tipo anterior quando o usuário troca o tipo.
    private function syncTipoAuxiliar(Contact $contact, StoreContact $request){
      if ($contact->tipo === 'fornecedor') {
        $contact->fornecedor()->updateOrCreate([], [
          'tipo_servico' => $request->input('tipo_servico') ?: null,
          'razao_social' => $request->input('razao_social') ?: null,
          'cnpj' => $request->input('cnpj') ?: null,
          'contato_responsavel' => $request->input('contato_responsavel') ?: null,
          'forma_pagamento_preferida' => $request->input('forma_pagamento_preferida') ?: null,
          'observacoes' => $request->input('observacoes_fornecedor') ?: null,
        ]);
        $contact->clienteComercial()->delete();
      } elseif ($contact->tipo === 'cliente') {
        $contact->clienteComercial()->updateOrCreate([], [
          'valor_hora' => $request->input('valor_hora') ?: null,
          'forma_cobranca' => $request->input('forma_cobranca') ?: null,
          'contrato_url' => $request->input('contrato_url') ?: null,
          'observacoes' => $request->input('observacoes_cliente') ?: null,
        ]);
        $contact->fornecedor()->delete();
      } else {
        $contact->fornecedor()->delete();
        $contact->clienteComercial()->delete();
      }
    }
}
