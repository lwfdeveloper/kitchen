<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Validator::extend('mobile', function ($attribute, $value, $parameters, $validator) {
            $pattern = '/^1[3456789]{1}\d{9}$/';
            $res = preg_match($pattern, $value);
            return $res > 0;
        });
    }

}
