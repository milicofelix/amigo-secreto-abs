<?php

use App\Http\Middleware\EnableRaffles;
use Illuminate\Support\Facades\Route;

// Cada funcionalidade registra suas rotas em um arquivo próprio.
require __DIR__.'/amigo-secreto.php';
require __DIR__.'/auth.php';
Route::middleware(EnableRaffles::class)->group(__DIR__.'/rifas.php');
