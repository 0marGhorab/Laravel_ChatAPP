<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    /** @var \App\Models\User|null $user */
    $user = Auth::user();

    if (! $user || ! $user->hasVerifiedEmail()) {
        return redirect()->route('verification.notice');
    }

    return redirect()->route('chats');
});

// Keep Breeze/Fortify-style dashboard redirects compatible with this app.
Route::redirect('/dashboard', '/chats')->name('dashboard');

Route::view('chats', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('chats');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('users', 'users.index')
    ->middleware(['auth'])
    ->name('users.index');

Route::view('groups/create', 'groups.create')
    ->middleware(['auth', 'verified'])
    ->name('groups.create');

Route::get('chats/start/{user}', [ChatController::class, 'start'])
    ->middleware(['auth'])
    ->name('chats.start');

require __DIR__.'/auth.php';
