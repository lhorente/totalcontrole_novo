<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\WalletsController;
use App\Http\Controllers\CreditCardsController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\TransactionMappingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;

use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\SmartposImportController;
use App\Http\Controllers\ProfileController;

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
Route::middleware(['auth:sanctum', 'verified', 'two_factor.enabled', 'workspace'])->group(function () {
  Route::get('/',[DashboardController::class, 'index'])->name('dashboard');

  Route::prefix('contacts')->name('contacts.')->group(function () {
    Route::get('/', [ContactsController::class, 'index'])->name('index');
    Route::get('/new', [ContactsController::class, 'create'])->name('create');
    Route::post('/', [ContactsController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [ContactsController::class, 'edit'])->name('edit');
    Route::post('/edit/{id}', [ContactsController::class, 'update'])->name('update');
    Route::delete('/{id}', [ContactsController::class, 'destroy'])->name('destroy');
  });

  Route::get('/categories',[CategoriesController::class, 'index']);
  Route::get('/categories/new',[CategoriesController::class, 'new']);
  Route::get('/categories/edit/{id}',[CategoriesController::class, 'edit']);
  Route::post('/categories/store',[CategoriesController::class, 'store']);
  Route::get('/categories/remove/{id}',[CategoriesController::class, 'remove']);

  Route::get('/transaction-mappings',[TransactionMappingsController::class, 'index']);
  Route::get('/transaction-mappings/new',[TransactionMappingsController::class, 'new']);
  Route::get('/transaction-mappings/edit/{id}',[TransactionMappingsController::class, 'edit']);
  Route::post('/transaction-mappings/store',[TransactionMappingsController::class, 'store']);
  Route::get('/transaction-mappings/remove/{id}',[TransactionMappingsController::class, 'remove']);
  Route::post('/transaction-mappings/quick-toggle/{id}',[TransactionMappingsController::class, 'quickToggle']);

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

  Route::get('/transactions/credit_cards',[TransactionsController::class, 'creditCards'])->name('transactions.creditCards');
  Route::get('/transactions/card/{cardId}/{year?}/{month?}',[TransactionsController::class, 'cardTransactions'])->name('transactions.cardTransactions');
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
  Route::post('/transactions/modal-update/{id}',[TransactionsController::class, 'modalUpdate'])->name('transactions.modalUpdate');
  Route::post('/transactions/modal-store',[TransactionsController::class, 'storeModal'])->name('transactions.storeModal');
  Route::post('/transactions/bulk-update',[TransactionsController::class, 'bulkUpdate'])->name('transactions.bulkUpdate');
  Route::post('/transactions/pay-card-bill/{cardId}/{year}/{month}',[TransactionsController::class, 'payCardBill'])->name('transactions.payCardBill');
  Route::get('/transactions/modal_save',[TransactionsController::class, 'saveModal']);
  Route::get('/transactions/import',[TransactionsController::class, 'import'])->name('transactions.import');
  Route::post('/transactions/import-preview',[TransactionsController::class, 'importPreview'])->name('transactions.importPreview');
  Route::post('/transactions/import-preview-json',[TransactionsController::class, 'importPreviewJson'])->name('transactions.importPreviewJson');
  Route::post('/transactions/import-store',[TransactionsController::class, 'importStore'])->name('transactions.importStore');

  // Módulo Documentos e Prazos
  Route::prefix('documentos')->name('documentos.')->group(function () {
    Route::get('/', [DocumentoController::class, 'index'])->name('index');
    Route::get('/new', [DocumentoController::class, 'create'])->name('create');
    Route::post('/', [DocumentoController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [DocumentoController::class, 'edit'])->name('edit');
    Route::post('/edit/{id}', [DocumentoController::class, 'update'])->name('update');
    Route::delete('/{id}', [DocumentoController::class, 'destroy'])->name('destroy');
  });

  Route::post('/workspace/switch/{id}', [WorkspaceController::class, 'switch'])->name('workspace.switch');

  Route::get('/smartpos/import', [SmartposImportController::class, 'index'])->name('smartpos.import');
  Route::post('/smartpos/import/preview', [SmartposImportController::class, 'preview'])->name('smartpos.preview');
  Route::post('/smartpos/import/store', [SmartposImportController::class, 'store'])->name('smartpos.store');
});

// Perfil do usuário: fora do middleware two_factor.enabled de propósito, pois é
// nesta tela que o usuário ativa o 2FA (evita loop de redirecionamento).
Route::middleware(['auth:sanctum', 'verified', 'workspace'])->group(function () {
  Route::get('/user/profile', [ProfileController::class, 'show'])->name('profile.show');
  Route::delete('/user/other-browser-sessions', [ProfileController::class, 'destroyOtherBrowserSessions'])->name('other-browser-sessions.destroy');
  Route::delete('/user', [ProfileController::class, 'destroy'])->name('current-user.destroy');
});
