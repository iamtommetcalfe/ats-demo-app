<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AmiqusController;
use App\Http\Controllers\ApplicantController;

Route::get('/', [ApplicantController::class, 'index'])->name('applicants.index');
Route::get('/applicants/{applicant}', [ApplicantController::class, 'show'])->name('applicants.show');

// Update status (excluding background check trigger)
Route::patch('/applicants/{applicant}', [ApplicantController::class, 'update'])->name('applicants.update');
Route::post('/amiqus/{backgroundCheck}/refresh-record', [AmiqusController::class, 'refreshRecord'])->name('background-checks.refresh');

// Amiqus OAuth & background check routes
Route::get('/applicants/{applicant}/start-check', [AmiqusController::class, 'startCheck'])->name('amiqus.start');
Route::get('/amiqus/callback', [AmiqusController::class, 'handleCallback'])->name('amiqus.callback');
Route::get('/amiqus/connect', [AmiqusController::class, 'connectPage'])->name('amiqus.connect');
Route::get('/amiqus/authorize', [AmiqusController::class, 'redirectToAmiqus'])->name('amiqus.authorize');
Route::get('/amiqus/callback', [AmiqusController::class, 'handleCallback'])->name('amiqus.callback');
