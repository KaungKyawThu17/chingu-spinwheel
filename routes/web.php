<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SurveyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('survey.form');
});
    // Show survey form
Route::get('/survey', [SurveyController::class, 'showSurveyForm'])->name('survey.form');

// Handle survey submit
Route::post('/survey-submit', [SurveyController::class, 'submitSurvey'])->name('survey.submit');

// Show spin wheel
Route::get('/spin-wheel', [SurveyController::class, 'spinWheel'])->name('survey.spin');

// backend spin API (called by JS)
Route::post('/spin-wheel/spin', [SurveyController::class, 'processSpin'])->name('survey.spin.process');

