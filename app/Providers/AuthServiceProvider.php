<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        // Gate para asignar tickets
        Gate::define('asignar-ticket', function ($user) {
            return $user->esAdmin();
        });

        // Gate para gestionar usuarios
        Gate::define('gestionar-usuarios', function ($user) {
            return $user->esStaff();
        });
    }
}
