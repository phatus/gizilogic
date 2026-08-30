<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Livewire\StudentDashboard;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', StudentDashboard::class)->name('dashboard');
});
