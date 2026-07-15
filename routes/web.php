<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Livewire\ChangePassword;
use App\Livewire\MyFiles;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    if (! $user) {
        return redirect()->route('login');
    }

    return $user->is_admin
        ? redirect('/admin')
        : redirect()->route('my-files.index');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/my-files', MyFiles::class)->name('my-files.index');
    Route::get('/account/password', ChangePassword::class)->name('account.password');
});
