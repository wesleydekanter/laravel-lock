<?php

namespace WesleyDeKanter\LaravelLock;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->bind(ManagerContract::class, LockManager::class);
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/lock.php' => config_path('lock.php')
        ]);
    }
}