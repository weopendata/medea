<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Extensions\Neo4jUserProvider;
use Illuminate\Support\Facades\Validator;
use App\Repositories\CollectionRepository;

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

        Validator::extend('collectionTitle', function ($attribute, $value, $parameters, $validator) {
            try {
                // The title that identifies the collection must be unique
                $existingCollection = app(CollectionRepository::class)->getByTitle($value);

                if (empty($existingCollection)) {
                    return true;
                }
            } catch (\Exception $ex) {
                \Log::error($ex->getMessage());
            }
        });

        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
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
