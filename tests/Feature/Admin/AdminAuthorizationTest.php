<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Admin Route Authorization', function () {
    test('non-admin users cannot access admin dashboard', function () {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user);

        $response = $this->get('/admin');

        $response->assertForbidden();
    });

    test('guests are redirected to login', function () {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    });

    test('non-admin users cannot access admin links page', function () {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user);

        $response = $this->get('/admin/links');

        $response->assertForbidden();
    });

    test('non-admin users cannot access admin notes page', function () {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user);

        $response = $this->get('/admin/notes');

        $response->assertForbidden();
    });

    test('non-admin users cannot access admin users page', function () {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user);

        $response = $this->get('/admin/users');

        $response->assertForbidden();
    });

    test('non-admin users cannot access admin reports page', function () {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user);

        $response = $this->get('/admin/reports');

        $response->assertForbidden();
    });

    test('non-admin users cannot access admin allowlist page', function () {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user);

        $response = $this->get('/admin/allowlist');

        $response->assertForbidden();
    });
});
