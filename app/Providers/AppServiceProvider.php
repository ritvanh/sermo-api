<?php

namespace App\Providers;

use App\Services\FriendshipService;
use Illuminate\Support\ServiceProvider;
use App\Services\UserService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(UserService::class, function ($app) {
            return new UserService();
        });
        $this->app->scoped(FriendshipService::class, function ($app){
            return new FriendshipService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
