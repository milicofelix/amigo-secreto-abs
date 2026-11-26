<?php

use App\Http\Controllers\Rifas\CompraController;
use App\Http\Controllers\Rifas\RifaAdminController;
use App\Http\Controllers\Rifas\RifaCrudController;
use Illuminate\Support\Facades\Route;

Route::resource('rifas', RifaCrudController::class);
Route::get('rifas/{rifa}/gerar-nomes', [RifaCrudController::class, 'gerarNomesForm'])->name('rifas.gerarNomes.form');
Route::post('rifas/{rifa}/gerar-nomes', [RifaCrudController::class, 'gerarNomes'])->name('rifas.gerarNomes');
Route::get('rifas/pix/{nome}', [CompraController::class, 'pix'])->name('rifas.pix');
Route::get('rifas/pagar/{nome}', [CompraController::class, 'pix'])->name('rifas.pagar');
Route::post('rifas/confirmar/{nome}', [RifaAdminController::class, 'confirmarPagamento'])->name('rifas.confirmar.pagamento');
Route::post('comprar/{nome}', [CompraController::class, 'comprar'])->name('comprar.nome');

Route::prefix('admin/rifas')->name('admin.rifas.')->group(function () {
    Route::get('pendentes', [RifaAdminController::class, 'pendentes'])->name('pendentes');
    Route::post('pagar/{nome}', [RifaAdminController::class, 'confirmar'])->name('confirmar');
    Route::get('{rifa}/sortear', [RifaAdminController::class, 'sortear'])->name('sortear');
    Route::get('{rifa}/resultado/{ganhador?}', [RifaAdminController::class, 'resultado'])->name('resultado');
    Route::get('{rifa}/sorteio-animado', [RifaAdminController::class, 'sorteioAnimado'])->name('sorteio.animado');
    Route::post('{rifa}/salvar-ganhador', [RifaAdminController::class, 'salvarGanhador'])->name('salvarGanhador');
});

Route::prefix('crud/rifas')->name('crud.rifas.')->group(function () {
    Route::get('/', [RifaCrudController::class, 'index'])->name('index');
    Route::get('create', [RifaCrudController::class, 'create'])->name('create');
    Route::post('/', [RifaCrudController::class, 'store'])->name('store');
    Route::get('{rifa}', [RifaCrudController::class, 'show'])->name('show');
    Route::get('{rifa}/edit', [RifaCrudController::class, 'edit'])->name('edit');
    Route::put('{rifa}', [RifaCrudController::class, 'update'])->name('update');
    Route::delete('{rifa}', [RifaCrudController::class, 'destroy'])->name('destroy');
});
