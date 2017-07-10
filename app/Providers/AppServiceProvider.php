<?php

namespace App\Providers;

<<<<<<< HEAD
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Extensions\Neo4jUserProvider;
use Illuminate\Support\Facades\Validator;
use App\Repositories\App\Repositories\CollectionRepository;
=======
use App\Brokers\Neo4jPasswordBroker;
use App\Extensions\Neo4jUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
>>>>>>> 781d3bd4a9c44d10d51da5445ad9b5198fd9dfe4

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

<<<<<<< HEAD
        Validator::extend('collectionTitle', function ($attribute, $value, $parameters, $validator) {
            try {
                // The title that identifies the collection must be unique
                $existingCollection = app(CollectionRepository::class)->getByTitle($value);

                if (! empty($existingCollection)) {
                    return false;
                }
            } catch (\Exception $ex) {
                \Log::error($ex->getMessage());
                return false;
            }
        });
=======
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
>>>>>>> 781d3bd4a9c44d10d51da5445ad9b5198fd9dfe4
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
