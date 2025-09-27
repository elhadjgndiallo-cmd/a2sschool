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
    public function index()
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('classes.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }        
        $classes = Classe::orderBy('niveau')->orderBy('nom')->paginate(20);
        return view('classes.index', compact('classes'));
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
        $classe->load(['eleves.utilisateur', 'emploisTemps.matiere', 'emploisTemps.enseignant.utilisateur']);
        
        $statistiques = [
            'total_eleves' => $classe->eleves->count(),
            'total_cours' => $classe->emploisTemps->count(),
            'total_matieres' => $classe->emploisTemps->pluck('matiere_id')->unique()->count(),
            'total_enseignants' => $classe->emploisTemps->pluck('enseignant_id')->unique()->count(),
        ];
        
        return view('classes.show', compact('classe', 'statistiques'));
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
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:50|unique:classes,nom,' . $classe->id,
            'niveau' => 'required|string|max:20',
            'section' => 'required|string|max:50',
            'effectif_max' => 'required|integer|min:' . $classe->effectif_actuel,
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
     * Supprimer une classe
     */
    public function destroy(Classe $classe)
    {
        // Vérifier si la classe a des élèves
        if ($classe->eleves()->count() > 0) {
            return redirect()->route('classes.index')
                ->with('error', 'Impossible de supprimer cette classe car elle contient des élèves');
        }
        
        // Vérifier si la classe a des emplois du temps
        if ($classe->emploisTemps()->count() > 0) {
            return redirect()->route('classes.index')
                ->with('error', 'Impossible de supprimer cette classe car elle est utilisée dans des emplois du temps');
        }
        
        // Désactiver la classe au lieu de la supprimer
        $classe->update(['actif' => false]);
        
        return redirect()->route('classes.index')
            ->with('success', 'Classe désactivée avec succès');
    }
}