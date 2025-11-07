<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->registerObservers();
    }

    /**
     * Register model observers.
     */
    protected function registerObservers(): void
    {
        \App\Models\Link::observe(\App\Observers\LinkObserver::class);
    }

    /**
     * Configure rate limiting for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Rate limiting for link creation (tiered by user type)
        RateLimiter::for('create-link', function (Request $request) {
            // Check if user is authenticated
            if ($request->user()) {
                // Registered users: 100 links per hour
                return Limit::perHour(100)
                    ->by($request->user()->id)
                    ->response(function (Request $request, array $headers) {
                        return response()->json([
                            'message' => 'Too many link creations. Please try again later.',
                            'retry_after' => $headers['Retry-After'] ?? 3600,
                        ], 429, $headers);
                    });
            }

            // Anonymous users: 20 links per hour (IP-based)
            return Limit::perHour(20)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Too many link creations. Please try again in an hour or create an account for higher limits.',
                        'retry_after' => $headers['Retry-After'] ?? 3600,
                    ], 429, $headers);
                });
        });

        // Standard API rate limiting (used by other endpoints)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
