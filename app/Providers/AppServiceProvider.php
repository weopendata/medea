<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Extensions\Neo4jUserProvider;
use App\Brokers\Neo4jPasswordBroker;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Auth::provider('neo4j', function () {
            // Return an instance of Illuminate\Contracts\Auth\UserProvider...
            return new Neo4jUserProvider();
        });

         Validator::extend('jsonMax', function ($attribute, $value, $parameters, $validator) {
            return count(json_decode($value)) <= array_shift($parameters);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
