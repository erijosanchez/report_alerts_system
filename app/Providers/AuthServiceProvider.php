<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        // Gates existentes
        Gate::define('asignar-ticket', function ($user) {
            return $user->esAdmin();
        });

        Gate::define('gestionar-usuarios', function ($user) {
            return $user->esStaff();
        });
        
        // Nuevos gates para automatización
        Gate::define('ver-monitoreo', function ($user) {
            return $user->esStaff();
        });
        
        Gate::define('gestionar-monitoreo', function ($user) {
            return $user->esAdmin();
        });
        
        Gate::define('gestionar-sedes', function ($user) {
            return $user->esAdmin();
        });
        
        Gate::define('ver-alarmas', function ($user) {
            return $user->esStaff();
        });
        
        Gate::define('gestionar-alarmas', function ($user) {
            return $user->esAdmin();
        });
    }
}
