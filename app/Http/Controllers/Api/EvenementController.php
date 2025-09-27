<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evenement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class EvenementController extends Controller
{
    /**
     * Afficher la liste des événements
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Evenement::query();
        
        // Filtrage par type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        // Filtrage par date de début
        if ($request->has('date_debut')) {
            $query->where('date_debut', '>=', $request->date_debut);
        }
        
        // Filtrage par date de fin
        if ($request->has('date_fin')) {
            $query->where('date_fin', '<=', $request->date_fin);
        }
        
        // Filtrage par période (mois/année)
        if ($request->has('mois') && $request->has('annee')) {
            $mois = $request->mois;
            $annee = $request->annee;
            $query->whereRaw("(MONTH(date_debut) = ? AND YEAR(date_debut) = ?) OR "
                . "(MONTH(date_fin) = ? AND YEAR(date_fin) = ?) OR "
                . "(date_debut <= LAST_DAY(?) AND date_fin >= ?)", 
                [$mois, $annee, $mois, $annee, "$annee-$mois-01", "$annee-$mois-01"]);
        }
        
        // Filtrage par visibilité
        if ($request->has('public')) {
            $query->where('public', $request->boolean('public'));
        }
        
        // Filtrage par classe (si spécifié)
        if ($request->has('classe_id')) {
            $query->where(function($q) use ($request) {
                $q->where('classe_id', $request->classe_id)
                  ->orWhereNull('classe_id');
            });
        }
        
        // Tri
        $sortField = $request->input('sort_field', 'date_debut');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $evenements = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $evenements,
            'message' => 'Liste des événements récupérée avec succès'
        ]);
    }

    /**
     * Créer un nouvel événement
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'lieu' => 'nullable|string|max:100',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin' => 'nullable|date_format:H:i',
            'journee_entiere' => 'boolean',
            'type' => 'required|string|in:cours,examen,reunion,conge,autre',
            'couleur' => 'nullable|string|max:7',
            'public' => 'boolean',
            'classe_id' => 'nullable|exists:classes,id',
            'rappel' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation des données'
            ], 422);
        }

        try {
            $evenement = Evenement::create([
                'titre' => $request->titre,
                'description' => $request->description,
                'lieu' => $request->lieu,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'heure_debut' => $request->heure_debut,
                'heure_fin' => $request->heure_fin,
                'journee_entiere' => $request->boolean('journee_entiere', false),
                'type' => $request->type,
                'couleur' => $request->couleur,
                'public' => $request->boolean('public', true),
                'classe_id' => $request->classe_id,
                'rappel' => $request->rappel,
                'createur_id' => Auth::id(),
            ]);
            
            return response()->json([
                'status' => 'success',
                'data' => $evenement,
                'message' => 'Événement créé avec succès'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création de l\'événement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un événement spécifique
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $evenement = Evenement::findOrFail($id);
            
            // Vérifier si l'événement est public ou si l'utilisateur est autorisé à le voir
            if (!$evenement->public) {
                $user = Auth::user();
                $userRole = $user->role;
                
                // Si l'événement est lié à une classe et que l'utilisateur n'est pas admin, enseignant de cette classe ou parent/élève de cette classe
                if ($evenement->classe_id && $userRole !== 'admin') {
                    $authorized = false;
                    
                    if ($userRole === 'enseignant') {
                        $authorized = $user->enseignant->classes()->where('classes.id', $evenement->classe_id)->exists();
                    } elseif ($userRole === 'parent') {
                        $authorized = $user->parent->eleves()->whereHas('classe', function($q) use ($evenement) {
                            $q->where('id', $evenement->classe_id);
                        })->exists();
                    } elseif ($userRole === 'eleve') {
                        $authorized = $user->eleve->classe_id === $evenement->classe_id;
                    }
                    
                    if (!$authorized) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Vous n\'êtes pas autorisé à voir cet événement'
                        ], 403);
                    }
                }
            }
            
            // Charger le créateur et la classe associée
            $evenement->load(['createur', 'classe']);
            
            return response()->json([
                'status' => 'success',
                'data' => $evenement,
                'message' => 'Événement récupéré avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Événement non trouvé'
            ], 404);
        }
    }

    /**
     * Mettre à jour un événement spécifique
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $evenement = Evenement::findOrFail($id);
            
            // Vérifier si l'utilisateur est autorisé à modifier cet événement
            $user = Auth::user();
            if ($user->role !== 'admin' && $evenement->createur_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vous n\'êtes pas autorisé à modifier cet événement'
                ], 403);
            }
            
            $validator = Validator::make($request->all(), [
                'titre' => 'sometimes|required|string|max:100',
                'description' => 'nullable|string|max:500',
                'lieu' => 'nullable|string|max:100',
                'date_debut' => 'sometimes|required|date',
                'date_fin' => 'sometimes|required|date|after_or_equal:date_debut',
                'heure_debut' => 'nullable|date_format:H:i',
                'heure_fin' => 'nullable|date_format:H:i',
                'journee_entiere' => 'boolean',
                'type' => 'sometimes|required|string|in:cours,examen,reunion,conge,autre',
                'couleur' => 'nullable|string|max:7',
                'public' => 'boolean',
                'classe_id' => 'nullable|exists:classes,id',
                'rappel' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                    'message' => 'Erreur de validation des données'
                ], 422);
            }

            $evenement->update($request->all());
            
            // Recharger l'événement avec ses relations
            $evenement->load(['createur', 'classe']);
            
            return response()->json([
                'status' => 'success',
                'data' => $evenement,
                'message' => 'Événement mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Événement non trouvé ou erreur lors de la mise à jour: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Supprimer un événement spécifique
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $evenement = Evenement::findOrFail($id);
            
            // Vérifier si l'utilisateur est autorisé à supprimer cet événement
            $user = Auth::user();
            if ($user->role !== 'admin' && $evenement->createur_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vous n\'êtes pas autorisé à supprimer cet événement'
                ], 403);
            }
            
            $evenement->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Événement supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Événement non trouvé ou erreur lors de la suppression: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Récupérer les événements pour une classe spécifique
     *
     * @param  int  $classeId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function evenementsClasse($classeId, Request $request)
    {
        try {
            $query = Evenement::where(function($q) use ($classeId) {
                $q->where('classe_id', $classeId)
                  ->orWhere('public', true);
            });
            
            // Filtrage par période (mois/année)
            if ($request->has('mois') && $request->has('annee')) {
                $mois = $request->mois;
                $annee = $request->annee;
                $query->whereRaw("(MONTH(date_debut) = ? AND YEAR(date_debut) = ?) OR "
                    . "(MONTH(date_fin) = ? AND YEAR(date_fin) = ?) OR "
                    . "(date_debut <= LAST_DAY(?) AND date_fin >= ?)", 
                    [$mois, $annee, $mois, $annee, "$annee-$mois-01", "$annee-$mois-01"]);
            }
            
            // Tri
            $sortField = $request->input('sort_field', 'date_debut');
            $sortDirection = $request->input('sort_direction', 'asc');
            $query->orderBy($sortField, $sortDirection);
            
            // Pagination
            $perPage = $request->input('per_page', 15);
            $evenements = $query->paginate($perPage);
            
            return response()->json([
                'status' => 'success',
                'data' => $evenements,
                'message' => 'Événements de la classe récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des événements: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les événements pour l'utilisateur connecté
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function mesEvenements(Request $request)
    {
        try {
            $user = Auth::user();
            $userRole = $user->role;
            
            $query = Evenement::where(function($q) use ($user, $userRole) {
                // Événements publics
                $q->where('public', true);
                
                // Événements créés par l'utilisateur
                $q->orWhere('createur_id', $user->id);
                
                // Événements spécifiques à la classe de l'utilisateur
                if ($userRole === 'eleve' && $user->eleve && $user->eleve->classe_id) {
                    $q->orWhere('classe_id', $user->eleve->classe_id);
                } elseif ($userRole === 'parent' && $user->parent) {
                    $classeIds = $user->parent->eleves()->pluck('classe_id')->unique()->filter()->toArray();
                    if (!empty($classeIds)) {
                        $q->orWhereIn('classe_id', $classeIds);
                    }
                } elseif ($userRole === 'enseignant' && $user->enseignant) {
                    $classeIds = $user->enseignant->classes()->pluck('classes.id')->toArray();
                    if (!empty($classeIds)) {
                        $q->orWhereIn('classe_id', $classeIds);
                    }
                }
            });
            
            // Filtrage par période (mois/année)
            if ($request->has('mois') && $request->has('annee')) {
                $mois = $request->mois;
                $annee = $request->annee;
                $query->whereRaw("(MONTH(date_debut) = ? AND YEAR(date_debut) = ?) OR "
                    . "(MONTH(date_fin) = ? AND YEAR(date_fin) = ?) OR "
                    . "(date_debut <= LAST_DAY(?) AND date_fin >= ?)", 
                    [$mois, $annee, $mois, $annee, "$annee-$mois-01", "$annee-$mois-01"]);
            }
            
            // Filtrage par type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            
            // Tri
            $sortField = $request->input('sort_field', 'date_debut');
            $sortDirection = $request->input('sort_direction', 'asc');
            $query->orderBy($sortField, $sortDirection);
            
            // Pagination
            $perPage = $request->input('per_page', 15);
            $evenements = $query->paginate($perPage);
            
            return response()->json([
                'status' => 'success',
                'data' => $evenements,
                'message' => 'Mes événements récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des événements: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les événements à venir
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function evenementsAVenir(Request $request)
    {
        try {
            $user = Auth::user();
            $userRole = $user->role;
            
            $query = Evenement::where('date_debut', '>=', now()->startOfDay())
                ->where(function($q) use ($user, $userRole) {
                    // Événements publics
                    $q->where('public', true);
                    
                    // Événements créés par l'utilisateur
                    $q->orWhere('createur_id', $user->id);
                    
                    // Événements spécifiques à la classe de l'utilisateur
                    if ($userRole === 'eleve' && $user->eleve && $user->eleve->classe_id) {
                        $q->orWhere('classe_id', $user->eleve->classe_id);
                    } elseif ($userRole === 'parent' && $user->parent) {
                        $classeIds = $user->parent->eleves()->pluck('classe_id')->unique()->filter()->toArray();
                        if (!empty($classeIds)) {
                            $q->orWhereIn('classe_id', $classeIds);
                        }
                    } elseif ($userRole === 'enseignant' && $user->enseignant) {
                        $classeIds = $user->enseignant->classes()->pluck('classes.id')->toArray();
                        if (!empty($classeIds)) {
                            $q->orWhereIn('classe_id', $classeIds);
                        }
                    }
                });
            
            // Limiter le nombre d'événements
            $limit = $request->input('limit', 5);
            $query->orderBy('date_debut', 'asc')->limit($limit);
            
            $evenements = $query->get();
            
            return response()->json([
                'status' => 'success',
                'data' => $evenements,
                'message' => 'Événements à venir récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des événements: ' . $e->getMessage()
            ], 500);
        }
    }
}