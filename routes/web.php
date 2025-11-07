<?php

use App\Http\Controllers\Web\RedirectController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

// Home page with link creation form (Livewire component)
Route::get('/', App\Livewire\Home::class)->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

// Anonymous redirect warning page - must be last to avoid conflicts with other routes
// Handles both hash (6 chars) and custom slug formats
Route::get('/{hashOrSlug}', [RedirectController::class, 'show'])
    ->where('hashOrSlug', '[a-zA-Z0-9\-]{3,50}')
    ->name('redirect.show');
