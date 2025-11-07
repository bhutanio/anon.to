<?php

use App\Models\User;
use Laravel\Fortify\Features;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $response = $this->withSession(['_token' => 'test-token'])
        ->post(route('login.store'), [
            '_token' => 'test-token',
            'email' => $user->email,
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->withSession(['_token' => 'test-token'])
        ->post(route('login.store'), [
            '_token' => 'test-token',
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

    $response->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->create();

    $response = $this->withSession(['_token' => 'test-token'])
        ->post(route('login.store'), [
            '_token' => 'test-token',
            'email' => $user->email,
            'password' => 'password',
        ]);

    $response->assertRedirect(route('two-factor.login'));
    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->post(route('logout'), ['_token' => 'test-token']);

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});
