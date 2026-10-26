<?php

namespace App\Providers;

use App\Models\User;
use App\Services\IgdbService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(IgdbService::class, fn () => new IgdbService(
            config('services.igdb.client_id'),
            config('services.igdb.secret'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.layouts.app', function ($view) {
            $view->with('memberCount', User::count());
        });
    }
}
