<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AmiqusController;
use App\Http\Controllers\ApplicantController;

Route::get('/', [ApplicantController::class, 'index'])->name('applicants.index');
Route::get('/applicants/{applicant}', [ApplicantController::class, 'show'])->name('applicants.show');

// Update status (excluding background check trigger)
Route::patch('/applicants/{applicant}', [ApplicantController::class, 'update'])->name('applicants.update');

// Amiqus OAuth & background check routes
Route::get('/applicants/{applicant}/start-check', [AmiqusController::class, 'startCheck'])->name('amiqus.start');
Route::prefix('amiqus')->group(function () {
    Route::post('/{backgroundCheck}/refresh-record', [AmiqusController::class, 'refreshRecord'])->name('background-checks.refresh');
    Route::get('/records/{backgroundCheck}', [AmiqusController::class, 'showRecord'])->name('amiqus.records.show');
    Route::get('/callback', [AmiqusController::class, 'handleCallback'])->name('amiqus.callback');
    Route::get('/connect', [AmiqusController::class, 'connectPage'])->name('amiqus.connect');
    Route::get('/authorize', [AmiqusController::class, 'redirectToAmiqus'])->name('amiqus.authorize');
    Route::get('/callback', [AmiqusController::class, 'handleCallback'])->name('amiqus.callback');
});
