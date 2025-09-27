<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Helpers\SchoolHelper;
use App\Helpers\PermissionHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix pour MySQL et les index trop longs
        Schema::defaultStringLength(191);
        
        // Partager les informations de l'école avec toutes les vues
        View::composer('*', function ($view) {
            $view->with('schoolInfo', SchoolHelper::getDocumentInfo());
            $view->with('schoolHeader', SchoolHelper::getDocumentHeader());
        });
        
        // Enregistrer le helper PermissionHelper
        if (!class_exists('PermissionHelper')) {
            class_alias(PermissionHelper::class, 'PermissionHelper');
        }
    }
}
