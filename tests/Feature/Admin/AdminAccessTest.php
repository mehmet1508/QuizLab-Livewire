<?php

use App\Models\Category;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

test('non-admin users are forbidden from the admin panel', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $this->actingAs($user);

    $this->get(route('admin.categories.index'))->assertForbidden();
});

test('admins can reach every admin page', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);

    $category = Category::factory()->create();
    $quiz = Quiz::factory()->create();
    $question = Question::factory()->create();
    $question->options()->createMany([
        ['sik_metni' => 'A', 'dogru_mu' => true],
        ['sik_metni' => 'B', 'dogru_mu' => false],
    ]);
    $quiz->questions()->attach($question->id);

    $this->get(route('admin.categories.index'))->assertOk();

    $this->get(route('admin.users.index'))->assertOk();
    $this->get(route('admin.users.create'))->assertOk();
    $this->get(route('admin.users.edit', $admin))->assertOk();

    $this->get(route('admin.quizzes.index'))->assertOk();
    $this->get(route('admin.quizzes.create'))->assertOk();
    $this->get(route('admin.quizzes.edit', $quiz))->assertOk();
    $this->get(route('admin.quizzes.leaderboard', $quiz))->assertOk();
    $this->get(route('admin.quizzes.puanlar', $quiz))->assertOk();

    $this->get(route('admin.questions.index', $quiz))->assertOk();
    $this->get(route('admin.questions.create', $quiz))->assertOk();
    $this->get(route('admin.questions.edit', [$quiz, $question]))->assertOk();
    $this->get(route('admin.questions.pool', $quiz))->assertOk();
    $this->get(route('admin.questions.import', $quiz))->assertOk();
    $this->get(route('admin.questions.createGlobal'))->assertOk();
    $this->get(route('admin.questions.ornek'))->assertOk();

    expect($category)->not->toBeNull();
});

test('an admin can create a category, quiz and question through the admin panel', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);

    Livewire\Livewire::test('pages::admin.categories.index')
        ->set('ad', 'Test Kategori')
        ->call('ekle')
        ->assertHasNoErrors();

    expect(Category::where('ad', 'Test Kategori')->exists())->toBeTrue();

    Livewire\Livewire::test('pages::admin.questions.create-global')
        ->set('soruMetni', 'Deneme sorusu?')
        ->set('siklar.0', 'A')
        ->set('siklar.1', 'B')
        ->set('siklar.2', 'C')
        ->set('siklar.3', 'D')
        ->set('dogruSik', 0)
        ->call('kaydet')
        ->assertHasNoErrors();

    expect(Question::where('soru_metni', 'Deneme sorusu?')->exists())->toBeTrue();
});
