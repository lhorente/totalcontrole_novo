<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Http\Requests\StoreContact;
use Illuminate\Support\Facades\Auth;

class ContactsController extends Controller
{
    public function index(){
      $contacts = Contact::getCurrentUserContacts();

      return view('contacts/index',compact('contacts'));
    }

    public function new(){
      return view('contacts/new');
    }

    public function edit($id){
      $contact = Contact::getContact($id);
      return view('contacts/edit',compact('contact'));
    }

    // public function store(){
    public function store(StoreContact $request){
      $contact = new Contact;
      $contact->id_usuario = Auth::id();

      if ($request->input('id')){
        $contact = Contact::getContact($request->input('id'));
        if (!$contact){
          // EXIBIR ERRO
        }
      }

      $contact->nome = $request->input('nome');

      if ($contact->save()){
        return redirect('/contacts')->with('success', 'Contato salvo com sucesso');
        // redirect()->back()->withSuccess('Contato salvo com sucesso');
      }

      // die("OK");
      // $validated = $request->validated();
      //
      // var_dump($contact);
    }

    public function remove($id){
      $contact = Contact::getContact($id);
      if (!$contact){
        // Exibir erro
      }

      if ($contact->delete()){
        return redirect('/contacts')->with('success', 'Contato excluído com sucesso');
      } else {
        return redirect('/contacts')->with('error', 'Não foi possível excluir o contato');
      }
    }
}
