<?php

use App\Models\GameAttempt;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

test('approved user can view stats page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/stats');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Stats'));
});

test('stats page defaults to gold award level', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/stats');

    $response->assertInertia(fn ($page) => $page->where('awardLevel', 'gold'));
});

test('stats page accepts award level query parameter', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/stats?award_level=bronze');

    $response->assertInertia(fn ($page) => $page->where('awardLevel', 'bronze'));
});

test('stats page returns recent attempts for the selected level', function () {
    $user = User::factory()->create();

    GameAttempt::create([
        'user_id' => $user->id,
        'award_level' => 'gold',
        'total_questions' => 50,
        'correct_answers' => 45,
        'incorrect_answers' => 5,
        'time_remaining' => 10,
    ]);

    GameAttempt::create([
        'user_id' => $user->id,
        'award_level' => 'bronze',
        'total_questions' => 144,
        'correct_answers' => 100,
        'incorrect_answers' => 44,
        'time_remaining' => 0,
    ]);

    $response = $this->actingAs($user)->get('/stats?award_level=gold');

    $response->assertInertia(fn ($page) => $page->has('recentAttempts', 1));
});

test('unauthenticated user cannot view stats', function () {
    $response = $this->get('/stats');

    $response->assertRedirect('/login');
});

test('unapproved user cannot view stats', function () {
    $user = User::factory()->unapproved()->create();

    $response = $this->actingAs($user)->get('/stats');

    $response->assertRedirect(route('approval.pending'));
});
