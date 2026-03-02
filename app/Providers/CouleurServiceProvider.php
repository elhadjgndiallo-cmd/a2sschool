<?php

namespace App\Providers;

use App\Models\CouleurParametre;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class CouleurServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Partager les couleurs avec toutes les vues de manière plus fiable
        View::share('couleurs', [
            'general' => CouleurParametre::getCouleursParCategorie('general'),
            'bulletin' => CouleurParametre::getCouleursParCategorie('bulletin'),
            'resultat' => CouleurParametre::getCouleursParCategorie('resultat'),
            'document' => CouleurParametre::getCouleursParCategorie('document'),
        ]);

        // Créer des directives Blade pour faciliter l'utilisation des couleurs
        $blade = View::getEngineResolver()->resolve('blade')->getCompiler();
        
        $blade->directive('couleur', function ($expression) {
            return "<?php echo App\\Models\\CouleurParametre::getCouleur($expression); ?>";
        });

        $blade->directive('couleurStyle', function ($expression) {
            return "<?php echo 'color: ' . App\\Models\\CouleurParametre::getCouleur($expression) . ';'; ?>";
        });

        $blade->directive('couleurBg', function ($expression) {
            return "<?php echo 'background-color: ' . App\\Models\\CouleurParametre::getCouleur($expression) . ';'; ?>";
        });
    }
}
