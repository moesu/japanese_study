<?php

use App\Http\Controllers\GoiController;
use App\Http\Controllers\KanjiController;
use App\Http\Controllers\KotobaController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::get('/n5/kanji', [KanjiController::class, 'index'])->name('kanji.index');
Route::get('/n5/kotoba/{chapter?}', [GoiController::class, 'show'])->whereNumber('chapter')->name('goi.show');
Route::get('/gio_n5/{chapter?}', function (?int $chapter = null) {
    return redirect()->route('goi.show', array_filter(['chapter' => $chapter]));
})->whereNumber('chapter');
Route::get('/goi/{chapter?}', function (?int $chapter = null) {
    return redirect()->route('goi.show', array_filter(['chapter' => $chapter]));
})->whereNumber('chapter');
Route::get('/kanji/{kanji}', [KanjiController::class, 'show'])->name('kanji.show');
Route::get('/kotoba', [KotobaController::class, 'index'])->name('kotoba.index');
Route::get('/kotoba/pdf', [KotobaController::class, 'pdf'])->name('kotoba.pdf');
Route::post('/kotoba/learned', [KotobaController::class, 'toggleLearned'])->name('kotoba.learned');
Route::get('/quiz', [KanjiController::class, 'quiz'])->name('kanji.quiz');
Route::post('/quiz', [KanjiController::class, 'answer'])->name('kanji.quiz.answer');
Route::post('/quiz/reset', [KanjiController::class, 'resetQuiz'])->name('kanji.quiz.reset');
