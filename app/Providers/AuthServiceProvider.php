<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Opcional: definir scopes de Passport
        Passport::tokensCan([
            'admin' => 'Acceso total a la plataforma',
            'editor' => 'Puede editar contenidos',
            'viewer' => 'Solo lectura de contenidos',
        ]);

        // Opcional: expirar tokens
        Passport::tokensExpireIn(now()->addHours(2));
        Passport::refreshTokensExpireIn(now()->addDays(15));
    }
}