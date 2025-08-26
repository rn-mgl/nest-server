<?php

namespace App\Providers;

use App\Models\Onboarding;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        Model::preventLazyLoading(true);

        Relation::enforceMorphMap([
            "user" => User::class,
            "onboarding" => Onboarding::class,
            "training" => Training::class
        ]);
    }
}
