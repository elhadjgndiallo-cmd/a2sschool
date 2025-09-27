<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EvenementController extends Controller
{
    /**
     * Afficher la liste des événements
     */
    public function index(Request $request)
    {
        // Vérifier la permission de voir les événements
        if (!auth()->user()->hasPermission('evenements.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à voir les événements.');
        }

        $query = Evenement::with(['createur', 'classe']);
        
        // Pour les enseignants, parents et élèves, filtrer selon leurs permissions
        if (!auth()->user()->hasPermission('evenements.manage_all')) {
            $user = auth()->user();
            $query->where(function($q) use ($user) {
                // Événements publics
                $q->where('public', true);
                
                // Événements créés par l'utilisateur
                $q->orWhere('createur_id', $user->id);
                
                // Événements spécifiques à la classe de l'utilisateur
                if ($user->role === 'teacher' && $user->enseignant) {
                    $classeIds = $user->enseignant->classes()->pluck('classes.id')->toArray();
                    if (!empty($classeIds)) {
                        $q->orWhereIn('classe_id', $classeIds);
                    }
                } elseif ($user->role === 'parent' && $user->parent) {
                    $classeIds = $user->parent->eleves()->pluck('classe_id')->unique()->filter()->toArray();
                    if (!empty($classeIds)) {
                        $q->orWhereIn('classe_id', $classeIds);
                    }
                } elseif ($user->role === 'student' && $user->eleve && $user->eleve->classe_id) {
                    $q->orWhere('classe_id', $user->eleve->classe_id);
                }
            });
        }
        
        // Filtrage par type
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }
        
        // Filtrage par date de début
        if ($request->has('date_debut') && $request->date_debut !== '') {
            $query->where('date_debut', '>=', $request->date_debut);
        }
        
        // Filtrage par date de fin
        if ($request->has('date_fin') && $request->date_fin !== '') {
            $query->where('date_fin', '<=', $request->date_fin);
        }
        
        // Filtrage par visibilité
        if ($request->has('public') && $request->public !== '') {
            $query->where('public', $request->boolean('public'));
        }
        
        // Filtrage par classe
        if ($request->has('classe_id') && $request->classe_id !== '') {
            $query->where('classe_id', $request->classe_id);
        }
        
        // Filtrage par créateur (pour les admins)
        if ($request->has('createur_id') && $request->createur_id !== '') {
            $query->where('createur_id', $request->createur_id);
        }
        
        // Tri
        $sortField = $request->input('sort_field', 'date_debut');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);
        
        $evenements = $query->paginate(15);
        $classes = Classe::all();
        
        return view('evenements.index', compact('evenements', 'classes'));
    }

    /**
     * Afficher le formulaire de création d'événement
     */
    public function create()
    {
        // Vérifier la permission de créer des événements
        if (!auth()->user()->hasPermission('evenements.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à créer des événements.');
        }

        $classes = Classe::all();
        return view('evenements.create', compact('classes'));
    }

    /**
     * Enregistrer un nouvel événement
     */
    public function store(Request $request)
    {
        // Vérifier la permission de créer des événements
        if (!auth()->user()->hasPermission('evenements.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à créer des événements.');
        }

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
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
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
                'couleur' => $request->couleur ?? '#3788d8',
                'public' => $request->boolean('public', true),
                'classe_id' => $request->classe_id,
                'rappel' => $request->rappel,
                'createur_id' => Auth::id(),
            ]);
            
            return redirect()->route('evenements.index')
                ->with('success', 'Événement créé avec succès');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création de l\'événement: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher un événement spécifique
     */
    public function show($id)
    {
        // Vérifier la permission de voir les événements
        if (!auth()->user()->hasPermission('evenements.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à voir les événements.');
        }

        $evenement = Evenement::with(['createur', 'classe'])->findOrFail($id);
        return view('evenements.show', compact('evenement'));
    }

    /**
     * Afficher le formulaire d'édition d'un événement
     */
    public function edit($id)
    {
        // Vérifier la permission de modifier les événements
        if (!auth()->user()->hasPermission('evenements.edit')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à modifier des événements.');
        }

        $evenement = Evenement::findOrFail($id);
        
        // Vérifier si l'utilisateur est autorisé à modifier cet événement
        // Les admins peuvent modifier tous les événements, les autres seulement les leurs
        if (!auth()->user()->hasPermission('evenements.manage_all') && $evenement->createur_id !== Auth::id()) {
            return redirect()->route('evenements.index')
                ->with('error', 'Vous n\'êtes pas autorisé à modifier cet événement');
        }
        
        $classes = Classe::all();
        return view('evenements.edit', compact('evenement', 'classes'));
    }

    /**
     * Mettre à jour un événement
     */
    public function update(Request $request, $id)
    {
        // Vérifier la permission de modifier les événements
        if (!auth()->user()->hasPermission('evenements.edit')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à modifier des événements.');
        }

        $evenement = Evenement::findOrFail($id);
        
        // Vérifier si l'utilisateur est autorisé à modifier cet événement
        // Les admins peuvent modifier tous les événements, les autres seulement les leurs
        if (!auth()->user()->hasPermission('evenements.manage_all') && $evenement->createur_id !== Auth::id()) {
            return redirect()->route('evenements.index')
                ->with('error', 'Vous n\'êtes pas autorisé à modifier cet événement');
        }
        
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
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $evenement->update($request->all());
            
            return redirect()->route('evenements.index')
                ->with('success', 'Événement mis à jour avec succès');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour de l\'événement: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer un événement
     */
    public function destroy($id)
    {
        // Vérifier la permission de supprimer les événements
        if (!auth()->user()->hasPermission('evenements.delete')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à supprimer des événements.');
        }

        $evenement = Evenement::findOrFail($id);
        
        // Vérifier si l'utilisateur est autorisé à supprimer cet événement
        // Les admins peuvent supprimer tous les événements, les autres seulement les leurs
        if (!auth()->user()->hasPermission('evenements.manage_all') && $evenement->createur_id !== Auth::id()) {
            return redirect()->route('evenements.index')
                ->with('error', 'Vous n\'êtes pas autorisé à supprimer cet événement');
        }
        
        try {
            $evenement->delete();
            
            return redirect()->route('evenements.index')
                ->with('success', 'Événement supprimé avec succès');
        } catch (\Exception $e) {
            return redirect()->route('evenements.index')
                ->with('error', 'Erreur lors de la suppression de l\'événement: ' . $e->getMessage());
        }
    }

    /**
     * Afficher le calendrier des événements
     */
    public function calendrier(Request $request)
    {
        // Vérifier la permission de voir les événements
        if (!auth()->user()->hasPermission('evenements.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à voir les événements.');
        }

        $mois = $request->input('mois', now()->month);
        $annee = $request->input('annee', now()->year);
        
        $query = Evenement::where(function($q) use ($mois, $annee) {
            $q->whereRaw("(MONTH(date_debut) = ? AND YEAR(date_debut) = ?) OR "
                . "(MONTH(date_fin) = ? AND YEAR(date_fin) = ?) OR "
                . "(date_debut <= LAST_DAY(?) AND date_fin >= ?)", 
                [$mois, $annee, $mois, $annee, "$annee-$mois-01", "$annee-$mois-01"]);
        });
        
        $evenements = $query->orderBy('date_debut', 'asc')->get();
        $classes = Classe::all();
        
        return view('evenements.calendrier', compact('evenements', 'classes', 'mois', 'annee'));
    }
}
