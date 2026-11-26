<?php

use App\Http\Controllers\AmigoSecreto\AsDrawController;
use App\Http\Controllers\AmigoSecreto\DashboardController;
use App\Http\Controllers\AmigoSecreto\GroupController;
use App\Http\Controllers\AmigoSecreto\ParticipantController;
use App\Http\Controllers\AmigoSecreto\ParticipantImportController;
use App\Http\Controllers\AmigoSecreto\ParticipantInvitationController;
use App\Http\Controllers\AmigoSecreto\ParticipantPwaController;
use App\Http\Controllers\AmigoSecreto\ParticipantStatusController;
use App\Http\Controllers\AmigoSecreto\RestrictionController;
use App\Http\Controllers\AmigoSecreto\RevealController;
use App\Http\Controllers\AmigoSecreto\SuperAdminGroupsController;
use App\Http\Middleware\VerifyPwaParticipant;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'home'])->middleware(VerifyPwaParticipant::class)->name('home');

Route::get('super-admin/grupos', [SuperAdminGroupsController::class, 'index'])
    ->middleware(['auth', 'can:access-super-admin', 'cache.headers:private;no_store'])
    ->name('super-admin.groups');

Route::middleware(['auth', 'can:access-organizer', 'cache.headers:private;no_store'])->group(function () {
    Route::resource('groups', GroupController::class)->only(['index', 'create', 'store']);
    Route::resource('groups', GroupController::class)->only(['show', 'edit', 'update', 'destroy'])
        ->middleware('can:manage,group');

    Route::prefix('groups/{group}')->middleware('can:manage,group')->scopeBindings()->group(function () {
        Route::get('participants/import', [ParticipantImportController::class, 'create'])->name('participants.import');
        Route::post('participants/import', [ParticipantImportController::class, 'store'])->name('participants.import.store');
        Route::resource('participants', ParticipantController::class)->except(['index', 'show']);
        Route::get('participants/{participant}/qr', [ParticipantInvitationController::class, 'show'])->name('participants.qr');
        Route::post('participants/{participant}/qr', [ParticipantInvitationController::class, 'store'])->name('participants.invite.store');
        Route::delete('participants/{participant}/qr', [ParticipantInvitationController::class, 'destroy'])->name('participants.invite.destroy');
        Route::get('participants/{participant}/photo', [ParticipantController::class, 'photo'])->name('participants.photo');
        Route::post('restrictions', [RestrictionController::class, 'store'])->name('restrictions.store');
        Route::delete('restrictions/{restriction}', [RestrictionController::class, 'destroy'])->name('restrictions.destroy');
        Route::patch('draw/revelacao', [GroupController::class, 'scheduleReveal'])->name('draw.schedule');
        Route::post('draw/cancelar', [AsDrawController::class, 'cancel'])->name('draw.cancel');
        Route::post('draw', [AsDrawController::class, 'store'])->name('draw.store');
        Route::get('draw/result', [AsDrawController::class, 'show'])->name('draw.show');
        Route::get('start-draw', [DashboardController::class, 'startDraw'])->name('dashboard.start-draw');
    });

    Route::get('reveal/list/{group}', [RevealController::class, 'listRevealed'])
        ->can('manage', 'group')->name('reveal.list');

    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'home'])->name('dashboard.home');
        Route::get('select-group', [DashboardController::class, 'selectGroup'])
            ->middleware(VerifyPwaParticipant::class)->name('dashboard.select');
        Route::get('{group}', [DashboardController::class, 'index'])
            ->whereNumber('group')->can('manage', 'group')->name('amigo.dashboard');
        Route::get('{group}/revelados', [DashboardController::class, 'revealedFriends'])
            ->whereNumber('group')->can('manage', 'group')->name('dashboard.revealed-friends');
    });
});

Route::prefix('reveal')->middleware(VerifyPwaParticipant::class)->group(function () {
    Route::get('{participant}', [RevealController::class, 'legacy'])->name('reveal.form');
    Route::get('result/{participant}', [RevealController::class, 'legacy'])->name('reveal.result');
    Route::get('key/{key}', [RevealController::class, 'legacy'])->name('reveal.by.key');
});

Route::middleware(VerifyPwaParticipant::class)->group(function () {
    Route::get('meu-amigo', [ParticipantPwaController::class, 'show'])->name('pwa.participant');
    Route::post('meu-amigo/verificar', [ParticipantPwaController::class, 'verify'])
        ->middleware('throttle:8,1')->name('pwa.participant.verify');
    Route::patch('meu-amigo/email', [ParticipantPwaController::class, 'emailPreference'])->name('pwa.participant.email');
    Route::post('meu-amigo/sair', [ParticipantPwaController::class, 'logout'])->name('pwa.participant.logout');
    Route::post('meu-amigo/notificacoes', [ParticipantPwaController::class, 'subscribe'])
        ->middleware('throttle:20,1')->name('pwa.push.subscribe');
    Route::delete('meu-amigo/notificacoes', [ParticipantPwaController::class, 'unsubscribe'])->name('pwa.push.unsubscribe');
    Route::post('participant/status/update', [ParticipantStatusController::class, 'update'])->name('participant.status.update');
});
