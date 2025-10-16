<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('permission', \App\Models\Permissao::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'grupo_educacional' => \App\Models\GrupoEducacional::class,
            'mantenedora' => \App\Models\Mantenedora::class,
            'instituicao' => \App\Models\Instituicao::class,
            'campus' => \App\Models\Campus::class,
            'setor' => \App\Models\Setor::class,
            'user' => \App\Models\User::class,
        ]);
    }

    
}
