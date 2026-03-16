<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::view('chats', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('chats');

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
