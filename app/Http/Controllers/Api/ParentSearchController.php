<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ParentSearchController extends Controller
{
    /**
     * Rechercher des parents avec pagination et filtres
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = ParentModel::with(['utilisateur', 'eleves.utilisateur'])
                ->where('actif', true);

            // Recherche par nom, prénom ou téléphone
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('utilisateur', function($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('telephone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filtre par profession
            if ($request->filled('profession')) {
                $query->where('profession', 'like', "%{$request->profession}%");
            }

            // Filtre par nombre d'enfants
            if ($request->filled('min_enfants')) {
                $query->has('eleves', '>=', $request->min_enfants);
            }

            if ($request->filled('max_enfants')) {
                $query->has('eleves', '<=', $request->max_enfants);
            }

            // Tri simple par défaut
            $query->orderBy('created_at', 'desc');

            // Pagination
            $perPage = min($request->get('per_page', 10), 50); // Limite à 50 par page
            $parents = $query->paginate($perPage);

            // Formater les données pour l'affichage
            $formattedParents = $parents->map(function($parent) {
                return [
                    'id' => $parent->id,
                    'nom_complet' => $parent->utilisateur->nom . ' ' . $parent->utilisateur->prenom,
                    'nom' => $parent->utilisateur->nom,
                    'prenom' => $parent->utilisateur->prenom,
                    'telephone' => $parent->utilisateur->telephone,
                    'email' => $parent->utilisateur->email,
                    'adresse' => $parent->utilisateur->adresse,
                    'profession' => $parent->profession,
                    'employeur' => $parent->employeur,
                    'nb_enfants' => $parent->eleves->count(),
                    'enfants' => $parent->eleves->map(function($eleve) {
                        return [
                            'id' => $eleve->id,
                            'nom_complet' => $eleve->utilisateur->nom . ' ' . $eleve->utilisateur->prenom,
                            'classe' => $eleve->classe ? $eleve->classe->nom : 'Non assigné'
                        ];
                    })
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'parents' => $formattedParents,
                    'pagination' => [
                        'current_page' => $parents->currentPage(),
                        'last_page' => $parents->lastPage(),
                        'per_page' => $parents->perPage(),
                        'total' => $parents->total(),
                        'from' => $parents->firstItem(),
                        'to' => $parents->lastItem(),
                        'has_more' => $parents->hasMorePages()
                    ]
                ],
                'message' => 'Recherche effectuée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la recherche: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les détails d'un parent spécifique
     */
    public function show(ParentModel $parent): JsonResponse
    {
        try {
            $parent->load(['utilisateur', 'eleves.utilisateur', 'eleves.classe']);

            $formattedParent = [
                'id' => $parent->id,
                'nom_complet' => $parent->utilisateur->nom . ' ' . $parent->utilisateur->prenom,
                'nom' => $parent->utilisateur->nom,
                'prenom' => $parent->utilisateur->prenom,
                'telephone' => $parent->utilisateur->telephone,
                'email' => $parent->utilisateur->email,
                'adresse' => $parent->utilisateur->adresse,
                'profession' => $parent->profession,
                'employeur' => $parent->employeur,
                'telephone_travail' => $parent->telephone_travail,
                'nb_enfants' => $parent->eleves->count(),
                'enfants' => $parent->eleves->map(function($eleve) {
                    return [
                        'id' => $eleve->id,
                        'nom_complet' => $eleve->utilisateur->nom . ' ' . $eleve->utilisateur->prenom,
                        'classe' => $eleve->classe ? $eleve->classe->nom : 'Non assigné',
                        'statut' => $eleve->statut
                    ];
                })
            ];

            return response()->json([
                'status' => 'success',
                'data' => $formattedParent,
                'message' => 'Parent récupéré avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération: ' . $e->getMessage()
            ], 500);
        }
    }
}
