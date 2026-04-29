<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\WalletsController;
use App\Http\Controllers\CreditCardsController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\WorkspaceController;

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
Route::middleware(['auth:sanctum', 'verified', 'workspace'])->group(function () {
  Route::get('/',[DashboardController::class, 'index']);

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

  Route::get('/transactions',[TransactionsController::class, 'index'])->name('transactions.index');
  Route::get('/transactions/month/{year?}/{month?}',[TransactionsController::class, 'month'])->name('transactions.month');
  Route::get('/transactions/search',[TransactionsController::class, 'search'])->name('transactions.search');
  Route::get('/transactions/view/{id}',[TransactionsController::class, 'view'])->name('transactions.view');
  Route::get('/transactions/new',[TransactionsController::class, 'create'])->name('transactions.create');
  Route::post('/transactions/new',[TransactionsController::class, 'store'])->name('transactions.store');
  Route::get('/transactions/edit/{id}',[TransactionsController::class, 'edit'])->name('transactions.edit');
  Route::post('/transactions/edit/{id}',[TransactionsController::class, 'update'])->name('transactions.update');
  Route::delete('/transactions/{id}',[TransactionsController::class, 'destroy'])->name('transactions.destroy');
  Route::post('/transactions/quick-update/{id}',[TransactionsController::class, 'quickUpdate'])->name('transactions.quickUpdate');
  Route::post('/transactions/pay-card-bill/{cardId}/{year}/{month}',[TransactionsController::class, 'payCardBill'])->name('transactions.payCardBill');
  Route::get('/transactions/modal_save',[TransactionsController::class, 'saveModal']);
  Route::get('/transactions/import',[TransactionsController::class, 'import'])->name('transactions.import');
  Route::post('/transactions/import-preview',[TransactionsController::class, 'importPreview'])->name('transactions.importPreview');
  Route::post('/transactions/import-store',[TransactionsController::class, 'importStore'])->name('transactions.importStore');

  Route::post('/workspace/switch/{id}', [WorkspaceController::class, 'switch'])->name('workspace.switch');
});
