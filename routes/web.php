<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\GoiController;
use App\Http\Controllers\KanjiController;
use App\Http\Controllers\KotobaController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::get('/n4/kotoba/{chapter?}', [GoiController::class, 'n4'])->whereNumber('chapter')->name('n4.kotoba');
Route::get('/n3/kotoba/{chapter?}', [GoiController::class, 'n3'])->whereNumber('chapter')->name('n3.kotoba');
Route::get('/n3/kanji/{chapter?}', [GoiController::class, 'n3Kanji'])->whereNumber('chapter')->name('n3.kanji');
Route::get('/n5/kanji', [KanjiController::class, 'index'])->name('kanji.index');
Route::get('/n5/kanji/words', [KanjiController::class, 'words'])->name('kanji.words');
Route::get('/n5/kanji/{kanji}', [KanjiController::class, 'show'])->whereNumber('kanji')->name('kanji.show');
Route::get('/n5/kotoba/{chapter?}', [GoiController::class, 'show'])->whereNumber('chapter')->name('goi.show');
Route::get('/gio_n5/{chapter?}', function (?int $chapter = null) {
    return redirect()->route('goi.show', array_filter(['chapter' => $chapter]));
})->whereNumber('chapter');
Route::get('/goi/{chapter?}', function (?int $chapter = null) {
    return redirect()->route('goi.show', array_filter(['chapter' => $chapter]));
})->whereNumber('chapter');
Route::get('/kanji/{kanji}', function (string $kanji) {
    return redirect()->route('kanji.show', ['kanji' => $kanji], 301);
})->whereNumber('kanji');
Route::get('/kotoba', [KotobaController::class, 'index'])->name('kotoba.index');
Route::get('/kotoba/pdf', [KotobaController::class, 'pdf'])->name('kotoba.pdf');
Route::post('/kotoba/learned', [KotobaController::class, 'toggleLearned'])->name('kotoba.learned');
Route::get('/quiz', [KanjiController::class, 'quiz'])->name('kanji.quiz');
Route::post('/quiz', [KanjiController::class, 'answer'])->name('kanji.quiz.answer');
Route::post('/quiz/reset', [KanjiController::class, 'resetQuiz'])->name('kanji.quiz.reset');

Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->middleware('throttle:10,1')->name('admin.login.store');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
Route::middleware(EnsureAdmin::class)->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
});
