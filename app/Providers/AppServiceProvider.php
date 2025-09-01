<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\Training;
use App\Models\User;
use App\Models\UserOnboardingRequiredDocuments;
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
            "document" => Document::class,
            "training" => Training::class,
            "user_required_documents" => UserOnboardingRequiredDocuments::class
        ]);
    }
}
