<?php

use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Index')->name('home');
Route::post('/upload', [SubmissionController::class, 'store'])->name('upload');

Route::middleware('auth')->group(function (): void {
    Route::get('/submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download');
    Route::get('/submissions/bulk-download', [SubmissionController::class, 'bulkDownload'])->name('submissions.bulk-download');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Index')->name('dashboard');
});

require __DIR__.'/settings.php';
