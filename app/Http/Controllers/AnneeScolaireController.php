<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnneeScolaire;

class AnneeScolaireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $annees = AnneeScolaire::orderBy('date_debut', 'desc')->paginate(20);
        return view('annees-scolaires.index', compact('annees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('annees-scolaires.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255|unique:annee_scolaires,nom',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'description' => 'nullable|string|max:1000',
        ]);

        $anneeScolaire = AnneeScolaire::create($request->all());

        // Si c'est la première année créée, l'activer automatiquement
        if (AnneeScolaire::count() == 1) {
            $anneeScolaire->activer();
        }

        return redirect()->route('annees-scolaires.index')
            ->with('success', 'Année scolaire créée avec succès');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AnneeScolaire $anneesScolaire)
    {
        return view('annees-scolaires.edit', compact('anneesScolaire'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AnneeScolaire $anneesScolaire)
    {
        $request->validate([
            'nom' => 'required|string|max:255|unique:annee_scolaires,nom,' . $anneesScolaire->id,
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'description' => 'nullable|string|max:1000',
        ]);

        $anneesScolaire->update($request->all());

        return redirect()->route('annees-scolaires.index')
            ->with('success', 'Année scolaire mise à jour avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnneeScolaire $anneesScolaire)
    {
        if ($anneesScolaire->active) {
            return redirect()->route('annees-scolaires.index')
                ->with('error', 'Impossible de supprimer l\'année scolaire active');
        }

        $anneesScolaire->delete();

        return redirect()->route('annees-scolaires.index')
            ->with('success', 'Année scolaire supprimée avec succès');
    }

    /**
     * Activer une année scolaire
     */
    public function activer(AnneeScolaire $anneesScolaire)
    {
        $anneesScolaire->activer();

        return redirect()->route('annees-scolaires.index')
            ->with('success', 'Année scolaire ' . $anneesScolaire->nom . ' activée avec succès');
    }
}
