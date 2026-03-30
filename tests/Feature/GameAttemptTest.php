<?php

use App\Models\GameAttempt;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

test('approved user can log a game attempt', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/attempts', [
        'award_level' => 'bronze',
        'total_questions' => 144,
        'correct_answers' => 130,
        'incorrect_answers' => 14,
        'time_remaining' => 45,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('game_attempts', [
        'user_id' => $user->id,
        'award_level' => 'bronze',
        'total_questions' => 144,
        'correct_answers' => 130,
        'incorrect_answers' => 14,
        'time_remaining' => 45,
    ]);
});

test('unauthenticated user cannot log a game attempt', function () {
    $response = $this->post('/attempts', [
        'award_level' => 'bronze',
        'total_questions' => 144,
        'correct_answers' => 144,
        'incorrect_answers' => 0,
        'time_remaining' => 60,
    ]);

    $response->assertRedirect('/login');
    $this->assertDatabaseCount('game_attempts', 0);
});

test('unapproved user cannot log a game attempt', function () {
    $user = User::factory()->unapproved()->create();

    $response = $this->actingAs($user)->post('/attempts', [
        'award_level' => 'gold',
        'total_questions' => 50,
        'correct_answers' => 50,
        'incorrect_answers' => 0,
        'time_remaining' => 10,
    ]);

    $response->assertRedirect(route('approval.pending'));
    $this->assertDatabaseCount('game_attempts', 0);
});

test('validation rejects invalid award level', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/attempts', [
        'award_level' => 'platinum',
        'total_questions' => 50,
        'correct_answers' => 50,
        'incorrect_answers' => 0,
        'time_remaining' => 10,
    ]);

    $response->assertSessionHasErrors('award_level');
    $this->assertDatabaseCount('game_attempts', 0);
});

test('validation rejects missing fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/attempts', []);

    $response->assertSessionHasErrors(['award_level', 'total_questions', 'correct_answers', 'incorrect_answers', 'time_remaining']);
});

test('game attempt belongs to user', function () {
    $user = User::factory()->create();
    $attempt = GameAttempt::create([
        'user_id' => $user->id,
        'award_level' => 'silver',
        'total_questions' => 144,
        'correct_answers' => 100,
        'incorrect_answers' => 44,
        'time_remaining' => 0,
    ]);

    expect($attempt->user->id)->toBe($user->id);
    expect($user->gameAttempts)->toHaveCount(1);
});
