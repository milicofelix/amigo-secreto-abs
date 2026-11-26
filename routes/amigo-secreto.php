<?php

use App\Http\Controllers\AmigoSecreto\AsDrawController;
use App\Http\Controllers\AmigoSecreto\DashboardController;
use App\Http\Controllers\AmigoSecreto\GroupController;
use App\Http\Controllers\AmigoSecreto\ParticipantController;
use App\Http\Controllers\AmigoSecreto\ParticipantStatusController;
use App\Http\Controllers\AmigoSecreto\RestrictionController;
use App\Http\Controllers\AmigoSecreto\RevealController;
use App\Http\Controllers\WhatsAppLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'home'])->name('home');
Route::resource('groups', GroupController::class);

Route::prefix('groups/{group}')->group(function () {
    Route::resource('participants', ParticipantController::class)->except(['index', 'show']);
    Route::get('participants/{id}/qr', [ParticipantController::class, 'accessByKey'])->name('participants.qr');
    Route::post('restrictions', [RestrictionController::class, 'store'])->name('restrictions.store');
    Route::delete('restrictions/{restriction}', [RestrictionController::class, 'destroy'])->name('restrictions.destroy');
    Route::post('draw', [AsDrawController::class, 'store'])->name('draw.store');
    Route::get('draw/result', [AsDrawController::class, 'show'])->name('draw.show');
    Route::get('whatsapp/logs', [WhatsAppLogController::class, 'index'])->name('whatsapp.logs');
    Route::get('start-draw', [DashboardController::class, 'startDraw'])->name('dashboard.start-draw');
});

Route::prefix('reveal')->group(function () {
    Route::get('{participant}', [RevealController::class, 'showForm'])->name('reveal.form');
    Route::post('{participant}', [RevealController::class, 'store'])->name('reveal.store');
    Route::post('verify/{participant}', [RevealController::class, 'verify'])->name('reveal.verify');
    Route::get('result/{participant}', [RevealController::class, 'result'])->name('reveal.result');
    Route::get('list/{group}', [RevealController::class, 'listRevealed'])->name('reveal.list');
    Route::get('key/{key}', [RevealController::class, 'showBySecretKey'])->name('reveal.by.key');
});

Route::post('participant/status/update', [ParticipantStatusController::class, 'update'])->name('participant.status.update');

Route::prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'home'])->name('dashboard.home');
    Route::get('select-group', [DashboardController::class, 'selectGroup'])->name('dashboard.select');
    Route::get('{group}', [DashboardController::class, 'index'])->whereNumber('group')->name('amigo.dashboard');
    Route::get('{group}/revelados', [DashboardController::class, 'revealedFriends'])->whereNumber('group')->name('dashboard.revealed-friends');
});
