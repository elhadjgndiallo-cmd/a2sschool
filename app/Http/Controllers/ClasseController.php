<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClasseController extends Controller
{
    /**
     * Afficher la liste des classes
     */
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('classes.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();

        $classes = Classe::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($inner) use ($search) {
                    $inner->where('nom', 'like', "%{$search}%")
                        ->orWhere('niveau', 'like', "%{$search}%")
                        ->orWhere('section', 'like', "%{$search}%");
                });
            })
            ->withCount([
                'eleves as effectif_annee' => function ($q) use ($anneeScolaireActive) {
                    if ($anneeScolaireActive) {
                        $q->where('annee_scolaire_id', $anneeScolaireActive->id);
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                },
            ])
            ->orderBy('niveau')
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('classes.index', compact('classes', 'anneeScolaireActive'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('classes.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }        
        return view('classes.create');
    }

    /**
     * Enregistrer une nouvelle classe
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:50|unique:classes',
            'niveau' => 'required|string|max:20',
            'section' => 'required|string|max:50',
            'effectif_max' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Classe::create([
            'nom' => $request->nom,
            'niveau' => $request->niveau,
            'section' => $request->section,
            'effectif_max' => $request->effectif_max,
            'effectif_actuel' => 0,
            'description' => $request->description,
            'actif' => true,
        ]);

        return redirect()->route('classes.index')
            ->with('success', 'Classe créée avec succès');
    }

    /**
     * Afficher les détails d'une classe
     */
    public function show(Classe $classe)
    {
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();

        $classe->load([
            'eleves' => function ($q) use ($anneeScolaireActive) {
                if ($anneeScolaireActive) {
                    $q->where('annee_scolaire_id', $anneeScolaireActive->id);
                } else {
                    $q->whereRaw('1 = 0');
                }
                $q->with('utilisateur');
            },
            'emploisTemps' => function ($q) use ($anneeScolaireActive) {
                $q->where('actif', true)
                    ->when($anneeScolaireActive, fn ($qq) => $qq->where('annee_scolaire_id', $anneeScolaireActive->id))
                    ->with(['matiere', 'enseignant.utilisateur'])
                    ->orderBy('jour_semaine')
                    ->orderBy('heure_debut');
            },
        ]);

        $effectifAnnee = $classe->eleves->count();

        $matieresEnseignants = $classe->emploisTemps
            ->groupBy('matiere_id')
            ->map(function ($cours) {
                $premier = $cours->first();
                $enseignants = $cours
                    ->pluck('enseignant')
                    ->filter()
                    ->unique('id')
                    ->map(fn ($ens) => $ens->nom_complet)
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'nom' => $premier->matiere?->nom ?? '—',
                    'enseignants' => $enseignants,
                    'heures' => $cours->count(),
                ];
            })
            ->sortBy('nom')
            ->values();

        $statistiques = [
            'total_eleves' => $effectifAnnee,
            'total_cours' => $classe->emploisTemps->count(),
            'total_matieres' => $matieresEnseignants->count(),
            'total_enseignants' => $classe->emploisTemps->pluck('enseignant_id')->unique()->count(),
        ];

        return view('classes.show', compact(
            'classe',
            'statistiques',
            'anneeScolaireActive',
            'effectifAnnee',
            'matieresEnseignants'
        ));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Classe $classe)
    {        
        return view('classes.edit', compact('classe'));
    }

    /**
     * Mettre à jour une classe
     */
    public function update(Request $request, Classe $classe)
    {
        $classe->updateEffectifActuel();
        $classe->refresh();

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:50|unique:classes,nom,' . $classe->id,
            'niveau' => 'required|string|max:20',
            'section' => 'required|string|max:50',
            'effectif_max' => 'required|integer|min:' . max(1, (int) $classe->effectif_actuel),
            'description' => 'nullable|string|max:255',
            'actif' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $classe->update([
            'nom' => $request->nom,
            'niveau' => $request->niveau,
            'section' => $request->section,
            'effectif_max' => $request->effectif_max,
            'description' => $request->description,
            'actif' => $request->has('actif'),
        ]);

        return redirect()->route('classes.index')
            ->with('success', 'Classe mise à jour avec succès');
    }

    /**
     * Désactiver une classe (destroy = désactiver)
     */
    public function destroy(Classe $classe)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('classes.delete')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à désactiver des classes.');
        }
        
        // Désactiver la classe au lieu de la supprimer
        $classe->update(['actif' => false]);
        
        return redirect()->route('classes.index')
            ->with('success', 'Classe désactivée avec succès');
    }

    /**
     * Supprimer définitivement une classe
     */
    public function deletePermanently(Classe $classe)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('classes.delete')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à supprimer des classes.');
        }
        
        // Vérifier si la classe a des élèves
        if ($classe->eleves()->count() > 0) {
            return redirect()->route('classes.index')
                ->with('error', 'Impossible de supprimer cette classe car elle contient des élèves. Veuillez d\'abord transférer ou supprimer les élèves.');
        }
        
        // Vérifier si la classe a des emplois du temps
        if ($classe->emploisTemps()->count() > 0) {
            // Supprimer les emplois du temps associés
            $classe->emploisTemps()->delete();
        }
        
        // Supprimer la classe
        $classe->delete();
        
        return redirect()->route('classes.index')
            ->with('success', 'Classe supprimée définitivement avec succès');
    }

    /**
     * Désactiver une classe
     */
    public function deactivate(Classe $classe)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('classes.delete')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à désactiver des classes.');
        }
        
        $classe->update(['actif' => false]);
        
        return redirect()->route('classes.index')
            ->with('success', 'Classe désactivée avec succès');
    }

    /**
     * Réactiver une classe
     */
    public function reactivate(Classe $classe)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('classes.edit')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à réactiver des classes.');
        }
        
        $classe->update(['actif' => true]);
        
        return redirect()->route('classes.index')
            ->with('success', 'Classe réactivée avec succès');
    }
}