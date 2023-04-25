<?php

namespace App\Providers;

use App\Services\FriendshipService;
use App\Services\MessageService;
use App\Services\UserProfileService;
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
        $this->app->scoped(UserProfileService::class, function ($app) {
            return new UserProfileService($app->make(FriendshipService::class));
        });
        $this->app->scoped(MessageService::class, function ($app) {
            return new MessageService($app->make(FriendshipService::class));
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
