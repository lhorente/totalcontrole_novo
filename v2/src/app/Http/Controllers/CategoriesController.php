<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Requests\StoreCategory;
use Illuminate\Support\Facades\Auth;

class CategoriesController extends Controller
{
  public function index(){
    $categories = Category::getCategories();

    return view('categories/index',compact('categories'));
  }

  public function new(){
    return view('categories/new');
  }

  public function edit($id){
    $category = Category::getCategory($id);
    return view('categories/edit',compact('category'));
  }

  // public function store(){
  public function store(StoreCategory $request){
    $category = new Category;
    $category->id_usuario = Auth::id();

    if ($request->input('id')){
      $category = Category::getCategory($request->input('id'));
      if (!$category){
        // EXIBIR ERRO
      }
    }

    $category->nome = $request->input('nome');

    if ($category->save()){
      return redirect('/categories')->with('success', 'Categoria salvo com sucesso');
    }
  }

  public function remove($id){
    $category = Category::getCategory($id);
    if (!$category){
      // Exibir erro
    }

    if ($category->delete()){
      return redirect('/categories')->with('success', 'Categoria excluído com sucesso');
    } else {
      return redirect('/categories')->with('error', 'Não foi possível excluir o categoria');
    }
  }
}
