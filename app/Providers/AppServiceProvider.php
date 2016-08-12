<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Extensions\Neo4jUserProvider;
use App\Brokers\Neo4jPasswordBroker;

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
