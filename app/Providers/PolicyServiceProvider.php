<?php

namespace App\Providers;

use App\Support\PolicyRegistrar;
use Illuminate\Support\ServiceProvider;

class PolicyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        PolicyRegistrar::register($this->app->make('request'));
    }
}
