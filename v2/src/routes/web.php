<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\WalletsController;
use App\Http\Controllers\CreditCardsController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Módulo Básico: Padrão em todas as contas
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
  Route::get('/dashboard',[DashboardController::class, 'index']);

  Route::get('/contacts',[ContactsController::class, 'index']);
  Route::get('/contacts/new',[ContactsController::class, 'new']);
  Route::get('/contacts/edit/{id}',[ContactsController::class, 'edit']);
  Route::post('/contacts/store',[ContactsController::class, 'store']);
  Route::get('/contacts/remove/{id}',[ContactsController::class, 'remove']);

  Route::get('/categories',[CategoriesController::class, 'index']);
  Route::get('/categories/new',[CategoriesController::class, 'new']);
  Route::get('/categories/edit/{id}',[CategoriesController::class, 'edit']);
  Route::post('/categories/store',[CategoriesController::class, 'store']);
  Route::get('/categories/remove/{id}',[CategoriesController::class, 'remove']);

  Route::get('/wallets',[WalletsController::class, 'index']);
  Route::get('/wallets/new',[WalletsController::class, 'new']);
  Route::get('/wallets/edit/{id}',[WalletsController::class, 'edit']);
  Route::post('/wallets/store',[WalletsController::class, 'store']);
  Route::get('/wallets/remove/{id}',[WalletsController::class, 'remove']);

  Route::get('/wallets/dashboard',[WalletsController::class, 'dashboard']);

  Route::get('/credit_cards',[CreditCardsController::class, 'index']);
  Route::get('/credit_cards/new',[CreditCardsController::class, 'new']);
  Route::get('/credit_cards/edit/{id}',[CreditCardsController::class, 'edit']);
  Route::post('/credit_cards/store',[CreditCardsController::class, 'store']);
  Route::get('/credit_cards/remove/{id}',[CreditCardsController::class, 'remove']);

  Route::get('/transactions',[TransactionsController::class, 'index']);
  Route::get('/transactions/search',[TransactionsController::class, 'search']);
  Route::get('/transactions/modal_save',[TransactionsController::class, 'saveModal']);
});
