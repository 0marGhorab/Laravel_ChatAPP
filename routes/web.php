<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('users', 'users.index')
    ->middleware(['auth'])
    ->name('users.index');

Route::get('chats/start/{user}', [ChatController::class, 'start'])
    ->middleware(['auth'])
    ->name('chats.start');

require __DIR__.'/auth.php';
