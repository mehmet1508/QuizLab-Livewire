<?php

use App\Http\Controllers\Admin\QuestionSampleDownloadController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::quizzes.index')->name('home');

Route::livewire('quizzes', 'pages::quizzes.index')->name('quizzes.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::livewire('quizzes/{quiz}', 'pages::quizzes.show')->name('quizzes.show');
    Route::livewire('quizzes/{quiz}/leaderboard', 'pages::quizzes.leaderboard')->name('quizzes.leaderboard');
    Route::livewire('quizzes/{quiz}/sonuc/{result}', 'pages::quizzes.sonuc')->name('quizzes.sonuc');
    Route::livewire('genel-siralama', 'pages::genel-siralama')->name('genel-siralama');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::livewire('categories', 'pages::admin.categories.index')->name('categories.index');

    Route::livewire('users', 'pages::admin.users.index')->name('users.index');
    Route::livewire('users/create', 'pages::admin.users.create')->name('users.create');
    Route::livewire('users/{user}/edit', 'pages::admin.users.edit')->name('users.edit');

    Route::livewire('quizzes', 'pages::admin.quizzes.index')->name('quizzes.index');
    Route::livewire('quizzes/create', 'pages::admin.quizzes.create')->name('quizzes.create');
    Route::livewire('quizzes/{quiz}/edit', 'pages::admin.quizzes.edit')->name('quizzes.edit');
    Route::livewire('quizzes/{quiz}/leaderboard', 'pages::admin.quizzes.leaderboard')->name('quizzes.leaderboard');
    Route::livewire('quizzes/{quiz}/puanlar', 'pages::admin.quizzes.puanlar')->name('quizzes.puanlar');

    Route::livewire('quizzes/{quiz}/questions', 'pages::admin.questions.index')->name('questions.index');
    Route::livewire('quizzes/{quiz}/questions/create', 'pages::admin.questions.create')->name('questions.create');
    Route::livewire('quizzes/{quiz}/questions/pool', 'pages::admin.questions.pool')->name('questions.pool');
    Route::livewire('quizzes/{quiz}/questions/import', 'pages::admin.questions.import')->name('questions.import');
    Route::livewire('quizzes/{quiz}/questions/{question}/edit', 'pages::admin.questions.edit')->name('questions.edit');

    Route::livewire('questions/havuz/ekle', 'pages::admin.questions.create-global')->name('questions.createGlobal');
    Route::get('questions/ornek-indir', QuestionSampleDownloadController::class)->name('questions.ornek');
});

require __DIR__.'/settings.php';
