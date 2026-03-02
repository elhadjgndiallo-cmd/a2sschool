<?php

namespace App\Http\Controllers;

use App\Models\CouleurParametre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class CouleurParametreController extends Controller
{
    /**
     * Afficher le formulaire de personnalisation des couleurs
     */
    public function index()
    {
        $categories = [
            'general' => 'Général',
            'bulletin' => 'Bulletins',
            'resultat' => 'Résultats',
            'document' => 'Documents'
        ];

        $couleurs = [];
        foreach ($categories as $cle => $nom) {
            $couleurs[$cle] = CouleurParametre::getCouleursParCategorie($cle);
        }

        // Forcer le rafraîchissement des couleurs pour cette vue
        View::share('couleurs', $couleurs);

        return view('parametres.couleurs.index', compact('categories', 'couleurs'));
    }

    /**
     * Mettre à jour les couleurs
     */
    public function update(Request $request)
    {
        $request->validate([
            'couleurs' => 'required|array',
            'couleurs.*' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/'
        ]);

        foreach ($request->couleurs as $cle => $valeur) {
            CouleurParametre::setCouleur($cle, $valeur);
        }

        return redirect()->route('parametres.couleurs.index')
            ->with('success', 'Les couleurs ont été mises à jour avec succès.');
    }

    /**
     * Réinitialiser les couleurs par défaut
     */
    public function reset()
    {
        CouleurParametre::initialiserCouleursDefaut();

        return redirect()->route('parametres.couleurs.index')
            ->with('success', 'Les couleurs ont été réinitialisées aux valeurs par défaut.');
    }

    /**
     * Obtenir les couleurs pour l'affichage (API)
     */
    public function getCouleurs()
    {
        $couleurs = CouleurParametre::all()->pluck('valeur', 'cle');
        return response()->json($couleurs);
    }

    /**
     * Obtenir les couleurs pour une catégorie spécifique (API)
     */
    public function getCouleursByCategorie($categorie)
    {
        $couleurs = CouleurParametre::getCouleursParCategorie($categorie);
        return response()->json($couleurs);
    }
}
