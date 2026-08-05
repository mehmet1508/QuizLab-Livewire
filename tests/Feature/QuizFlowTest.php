<?php

use App\Models\Question;
use App\Models\Quiz;
use App\Models\Result;
use App\Models\User;

test('guests can browse the quiz list', function () {
    Quiz::factory()->count(2)->create();

    $response = $this->get(route('quizzes.index'));

    $response->assertOk();
});

test('guests are redirected to login when opening a quiz', function () {
    $quiz = Quiz::factory()->create();

    $response = $this->get(route('quizzes.show', $quiz));

    $response->assertRedirect(route('login'));
});

test('a user can take a quiz end to end and see it on the leaderboard', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->create();

    $question = Question::factory()->create();
    $correct = $question->options()->create(['sik_metni' => 'Doğru', 'dogru_mu' => true]);
    $question->options()->create(['sik_metni' => 'Yanlış', 'dogru_mu' => false]);
    $quiz->questions()->attach($question->id);

    $this->actingAs($user);

    // Giriş ekranı (henüz başlamadı)
    $this->get(route('quizzes.show', $quiz))->assertOk();

    Livewire\Livewire::test('pages::quizzes.show', ['quiz' => $quiz])
        ->call('basla')
        ->assertRedirect(route('quizzes.show', $quiz));

    $this->assertTrue(session()->has('quiz_baslangic_'.$quiz->id));

    Livewire\Livewire::test('pages::quizzes.show', ['quiz' => $quiz])
        ->set("answers.{$question->id}", $correct->id)
        ->call('submit')
        ->assertRedirect();

    $result = Result::first();

    expect($result)->not->toBeNull();
    expect($result->puan)->toBeGreaterThan(0);
    expect($result->user_id)->toBe($user->id);

    $this->get(route('quizzes.sonuc', [$quiz, $result]))->assertOk();
    $this->get(route('quizzes.leaderboard', $quiz))->assertOk();
});

test('a user cannot view another users result page', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $quiz = Quiz::factory()->create();

    $result = Result::factory()->create(['user_id' => $owner->id, 'quiz_id' => $quiz->id]);

    $this->actingAs($intruder);

    $this->get(route('quizzes.sonuc', [$quiz, $result]))->assertForbidden();
});

test('dashboard and genel siralama pages load for authenticated users', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('dashboard'))->assertOk();
    $this->get(route('genel-siralama'))->assertOk();
});
