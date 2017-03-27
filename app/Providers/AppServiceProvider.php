<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Validator::extend('password_policy', function ($attribute, $value, $parameters, $validator) {
            if (preg_match('/^(?:(?=.*[a-z])(?:(?=.*[A-Z])(?=.*[\d\W])|(?=.*\W)(?=.*\d))|(?=.*\W)(?=.*[A-Z])(?=.*\d)).{8,}$/', $value)) {
                return true;
            }

            return false;
        });

        Validator::extend('recaptcha', function ($attribute, $value, $parameters, $validator) {
            $recaptcha = new \ReCaptcha\ReCaptcha(env('API_GOOGLE_RECAPTCHA'));
            $resp = $recaptcha->verify($value, get_ip());
            if ($resp->isSuccess()) {
                return true;
            }

            return false;
        });

        /**
         * Singletons
         */
        $this->app->singleton(\App\Services\MetaDataService::class);
        $this->app->singleton(\GeoIp2\Database\Reader::class, function () {
            return new \GeoIp2\Database\Reader(storage_path('geoip/geolite2-country.mmdb'));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (env('APP_DEBUG') && $this->app->environment() == 'local') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        }
    }
}
