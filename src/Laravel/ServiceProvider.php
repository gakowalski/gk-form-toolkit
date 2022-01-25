<?php

namespace Kowalski\Laravel;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot() {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    public function register() {

    }
}
