<?php

use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Index')->name('home');
Route::post('/upload', [SubmissionController::class, 'store'])->name('upload');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Index')->name('dashboard');
});

require __DIR__.'/settings.php';
