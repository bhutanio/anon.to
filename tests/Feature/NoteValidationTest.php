<?php

declare(strict_types=1);

use App\Http\Requests\CreateNoteRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

describe('Note Content Validation', function () {
    test('content is required', function () {
        $request = new CreateNoteRequest;
        $validator = Validator::make(
            ['content' => ''],
            $request->rules()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('content'))->toBeTrue();
    });

    test('content cannot exceed 1MB', function () {
        $request = new CreateNoteRequest;
        // Create content slightly over 1MB
        $largeContent = str_repeat('a', 1048577);

        $validator = Validator::make(
            ['content' => $largeContent],
            $request->rules()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('content'))->toBeTrue();
    });

    test('content within 1MB passes validation', function () {
        $request = new CreateNoteRequest;
        // Create content at exactly 1MB
        $validContent = str_repeat('a', 1048576);

        $validator = Validator::make(
            ['content' => $validContent],
            $request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });
});

describe('Note Password Validation', function () {
    test('password must be at least 8 characters', function () {
        $request = new CreateNoteRequest;
        $validator = Validator::make(
            [
                'content' => 'Test content',
                'password' => 'short',
                'password_confirmation' => 'short',
            ],
            $request->rules()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('password'))->toBeTrue();
    });

    test('password must match confirmation', function () {
        $request = new CreateNoteRequest;
        $validator = Validator::make(
            [
                'content' => 'Test content',
                'password' => 'password123',
                'password_confirmation' => 'different123',
            ],
            $request->rules()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('password'))->toBeTrue();
    });

    test('valid password with confirmation passes', function () {
        $request = new CreateNoteRequest;
        $validator = Validator::make(
            [
                'content' => 'Test content',
                'password' => 'securepassword',
                'password_confirmation' => 'securepassword',
            ],
            $request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });
});

describe('Note Expiration Validation', function () {
    test('expires_at must be in the future', function () {
        $request = new CreateNoteRequest;
        $validator = Validator::make(
            [
                'content' => 'Test content',
                'expires_at' => now()->subDay()->toDateTimeString(),
            ],
            $request->rules()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('expires_at'))->toBeTrue();
    });

    test('future expires_at passes validation', function () {
        $request = new CreateNoteRequest;
        $validator = Validator::make(
            [
                'content' => 'Test content',
                'expires_at' => now()->addDay()->toDateTimeString(),
            ],
            $request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    test('never expiration requires authentication', function () {
        // Anonymous user (not authenticated)
        $request = CreateNoteRequest::create('/test', 'POST', [
            'content' => 'Test content',
            'expires_at' => null,
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules()
        );

        // Run the withValidator callback
        $request->withValidator($validator);

        // Check that validation fails without throwing exception
        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('expires_at'))->toBeTrue();
    });

    test('authenticated user can set never expiration', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = CreateNoteRequest::create('/test', 'POST', [
            'content' => 'Test content',
            'expires_at' => null,
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules()
        );

        // Run the withValidator callback
        $request->withValidator($validator);

        expect($validator->passes())->toBeTrue();
    });
});

describe('Note View Limit Validation', function () {
    test('view_limit must be at least 1', function () {
        $request = new CreateNoteRequest;
        $validator = Validator::make(
            [
                'content' => 'Test content',
                'view_limit' => 0,
            ],
            $request->rules()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('view_limit'))->toBeTrue();
    });

    test('view_limit cannot exceed 100', function () {
        $request = new CreateNoteRequest;
        $validator = Validator::make(
            [
                'content' => 'Test content',
                'view_limit' => 101,
            ],
            $request->rules()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('view_limit'))->toBeTrue();
    });

    test('valid view_limit passes validation', function () {
        $request = new CreateNoteRequest;
        $validator = Validator::make(
            [
                'content' => 'Test content',
                'view_limit' => 50,
            ],
            $request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });
});
