<?php

use App\Http\Controllers\Web\RedirectController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

// Home page with link creation form (Livewire component)
Route::get('/', App\Livewire\Home::class)->name('home');

// Note creation and viewing routes
Route::get('/notes/create', App\Livewire\Notes\Create::class)->name('notes.create');
Route::get('/n/{hash}', App\Livewire\Notes\View::class)
    ->where('hash', '[a-zA-Z0-9]{6}')
    ->name('notes.view');

// QR code generation route
Route::get('/qr', App\Livewire\QrCode\Create::class)->name('qr.create');

Route::get('dashboard', App\Livewire\Dashboard\Index::class)
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

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', App\Livewire\Admin\Dashboard::class)->name('dashboard');
    Route::get('/links', App\Livewire\Admin\Links::class)->name('links');
    Route::get('/notes', App\Livewire\Admin\Notes::class)->name('notes');
    Route::get('/users', App\Livewire\Admin\Users::class)->name('users');
    Route::get('/reports', App\Livewire\Admin\Reports::class)->name('reports');
    Route::get('/allowlist', App\Livewire\Admin\AllowList::class)->name('allowlist');
});

// Anonymous redirect warning page - must be last to avoid conflicts with other routes
// Handles 6-character hash only
Route::get('/{hash}', [RedirectController::class, 'show'])
    ->where('hash', '[a-zA-Z0-9]{6}')
    ->name('redirect.show');
