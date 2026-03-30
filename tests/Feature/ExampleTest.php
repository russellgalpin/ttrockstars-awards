<?php

use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

test('unauthenticated users are redirected to login', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});

test('approved users can access the home page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertOk();
});

test('unapproved users are redirected to approval pending page', function () {
    $user = User::factory()->unapproved()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertRedirect(route('approval.pending'));
});

test('unapproved users can see the approval pending page', function () {
    $user = User::factory()->unapproved()->create();

    $response = $this->actingAs($user)->get('/approval-pending');

    $response->assertOk();
});

test('approved users are redirected away from approval pending page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/approval-pending');

    $response->assertRedirect(route('home'));
});

test('login page is accessible', function () {
    $response = $this->get('/login');

    $response->assertOk();
});

test('register page is accessible', function () {
    $response = $this->get('/register');

    $response->assertOk();
});

test('approved users can login and access home', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    $this->assertAuthenticatedAs($user);
});

test('unapproved users login and are redirected to approval pending', function () {
    $user = User::factory()->unapproved()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('approval.pending'));
    $this->assertAuthenticatedAs($user);
});

test('users cannot login with invalid credentials', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('new registrations are unapproved and redirected to login', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('login'));
    $this->assertGuest();

    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->isApproved())->toBeFalse();
});

test('authenticated users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/login');
    $this->assertGuest();
});
