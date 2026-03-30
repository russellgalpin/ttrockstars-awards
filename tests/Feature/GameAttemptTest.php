<?php

use App\Models\GameAttempt;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

function sampleAnswers(int $count = 3): array
{
    return array_map(fn ($i) => [
        'table_number' => ($i % 12) + 1,
        'multiplier' => ($i % 12) + 1,
        'operator' => $i % 2 === 0 ? '×' : '÷',
        'user_input_type' => 'answer',
        'is_correct' => true,
    ], range(0, $count - 1));
}

test('approved user can log a game attempt with answers', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/attempts', [
        'award_level' => 'bronze',
        'total_questions' => 144,
        'correct_answers' => 130,
        'incorrect_answers' => 14,
        'time_remaining' => 45,
        'answers' => sampleAnswers(5),
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('game_attempts', [
        'user_id' => $user->id,
        'award_level' => 'bronze',
    ]);

    $this->assertDatabaseCount('game_attempt_answers', 5);
});

test('unauthenticated user cannot log a game attempt', function () {
    $response = $this->post('/attempts', [
        'award_level' => 'bronze',
        'total_questions' => 144,
        'correct_answers' => 144,
        'incorrect_answers' => 0,
        'time_remaining' => 60,
        'answers' => sampleAnswers(),
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
        'answers' => sampleAnswers(),
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
        'answers' => sampleAnswers(),
    ]);

    $response->assertSessionHasErrors('award_level');
    $this->assertDatabaseCount('game_attempts', 0);
});

test('validation rejects missing answers', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/attempts', [
        'award_level' => 'bronze',
        'total_questions' => 144,
        'correct_answers' => 130,
        'incorrect_answers' => 14,
        'time_remaining' => 45,
    ]);

    $response->assertSessionHasErrors('answers');
});

test('validation rejects invalid answer data', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/attempts', [
        'award_level' => 'bronze',
        'total_questions' => 144,
        'correct_answers' => 130,
        'incorrect_answers' => 14,
        'time_remaining' => 45,
        'answers' => [
            ['table_number' => 13, 'multiplier' => 1, 'operator' => '×', 'user_input_type' => 'answer', 'is_correct' => true],
        ],
    ]);

    $response->assertSessionHasErrors('answers.0.table_number');
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
