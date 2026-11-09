<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContaReceberCargaController;
use App\Http\Controllers\ContaReceberController;
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

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/resumo', [DashboardController::class, 'resumo'])->name('dashboard.resumo');

Route::get('contas-a-receber/carga', [ContaReceberCargaController::class, 'create'])
    ->name('contas-receber.carga');
Route::post('contas-a-receber/carga', [ContaReceberCargaController::class, 'store'])
    ->name('contas-receber.importar');
Route::get('contas-a-receber/modelo/{formato}', [ContaReceberCargaController::class, 'modelo'])
    ->where('formato', 'csv|ofx')
    ->name('contas-receber.modelo');

// Dedicated action for the "Registrar recebimento" shortcut.
Route::post('contas-a-receber/{conta}/receber', [ContaReceberController::class, 'registrarRecebimento'])
    ->name('contas-receber.receber');

Route::resource('contas-a-receber', ContaReceberController::class)
    ->except(['show'])
    ->names('contas-receber')
    ->parameters(['contas-a-receber' => 'conta']);
