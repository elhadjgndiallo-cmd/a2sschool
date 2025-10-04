<?php

namespace App\Http\Controllers;

use App\Models\Matiere;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MatiereController extends Controller
{
    /**
     * Afficher la liste des matières
     */
    public function index()
    {
        if (!auth()->user()->hasPermission('matieres.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à voir les matières.');
        }
        
        $matieres = Matiere::with(['enseignants.utilisateur'])
            ->orderBy('nom')
            ->paginate(20);
            
        return view('matieres.index', compact('matieres'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        if (!auth()->user()->hasPermission('matieres.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à créer des matières.');
        }
        
        $enseignants = Enseignant::with('utilisateur')
            ->where('enseignants.actif', true)
            ->join('utilisateurs', 'enseignants.utilisateur_id', '=', 'utilisateurs.id')
            ->orderBy('utilisateurs.name')
            ->select('enseignants.*')
            ->get();
            
        return view('matieres.create', compact('enseignants'));
    }

    /**
     * Enregistrer une nouvelle matière
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('matieres.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à créer des matières.');
        }
        
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100|unique:matieres,nom',
            'code' => 'required|string|max:10|unique:matieres,code',
            'coefficient' => 'required|integer|in:1,2,3,4',
            'description' => 'nullable|string|max:500',
            'couleur' => 'required|string|max:7',
            'enseignants' => 'array',
            'enseignants.*' => 'exists:enseignants,id'
        ], [
            'nom.required' => 'Le nom de la matière est obligatoire',
            'nom.unique' => 'Cette matière existe déjà',
            'code.required' => 'Le code de la matière est obligatoire',
            'code.unique' => 'Ce code existe déjà',
            'coefficient.required' => 'Le coefficient est obligatoire',
            'coefficient.in' => 'Le coefficient doit être 1, 2, 3 ou 4',
            'couleur.required' => 'La couleur est obligatoire'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $matiere = Matiere::create([
            'nom' => $request->nom,
            'code' => strtoupper($request->code),
            'coefficient' => $request->coefficient,
            'description' => $request->description,
            'couleur' => $request->couleur,
            'actif' => true
        ]);

        // Associer les enseignants
        if ($request->has('enseignants')) {
            $matiere->enseignants()->sync($request->enseignants);
        }

        return redirect()->route('matieres.index')
            ->with('success', 'Matière créée avec succès');
    }

    /**
     * Afficher une matière
     */
    public function show(Matiere $matiere)
    {
        $matiere->load(['enseignants.utilisateur', 'notes', 'emploisTemps.classe']);
        
        $statistiques = [
            'total_enseignants' => $matiere->enseignants->count(),
            'total_notes' => $matiere->notes->count(),
            'moyenne_generale' => $matiere->notes->whereNotNull('note_finale')->avg('note_finale'),
            'classes_enseignees' => $matiere->emploisTemps->unique('classe_id')->count()
        ];
        
        return view('matieres.show', compact('matiere', 'statistiques'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Matiere $matiere)
    {
        $enseignants = Enseignant::with('utilisateur')
            ->where('enseignants.actif', true)
            ->join('utilisateurs', 'enseignants.utilisateur_id', '=', 'utilisateurs.id')
            ->orderBy('utilisateurs.name')
            ->select('enseignants.*')
            ->get();
            
        $matiereEnseignants = $matiere->enseignants->pluck('id')->toArray();
        
        return view('matieres.edit', compact('matiere', 'enseignants', 'matiereEnseignants'));
    }

    /**
     * Mettre à jour une matière
     */
    public function update(Request $request, Matiere $matiere)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100|unique:matieres,nom,' . $matiere->id,
            'code' => 'required|string|max:10|unique:matieres,code,' . $matiere->id,
            'coefficient' => 'required|integer|in:1,2,3,4',
            'description' => 'nullable|string|max:500',
            'couleur' => 'required|string|max:7',
            'enseignants' => 'array',
            'enseignants.*' => 'exists:enseignants,id'
        ], [
            'nom.required' => 'Le nom de la matière est obligatoire',
            'nom.unique' => 'Cette matière existe déjà',
            'code.required' => 'Le code de la matière est obligatoire',
            'code.unique' => 'Ce code existe déjà',
            'coefficient.required' => 'Le coefficient est obligatoire',
            'coefficient.in' => 'Le coefficient doit être 1, 2, 3 ou 4',
            'couleur.required' => 'La couleur est obligatoire'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $matiere->update([
            'nom' => $request->nom,
            'code' => strtoupper($request->code),
            'coefficient' => $request->coefficient,
            'description' => $request->description,
            'couleur' => $request->couleur
        ]);

        // Mettre à jour les enseignants
        $matiere->enseignants()->sync($request->enseignants ?? []);

        return redirect()->route('matieres.index')
            ->with('success', 'Matière mise à jour avec succès');
    }

    /**
     * Supprimer définitivement une matière
     */
    public function destroy(Matiere $matiere)
    {
        // Supprimer les relations d'abord
        $matiere->enseignants()->detach();
        $matiere->notes()->delete();
        $matiere->emploisTemps()->delete();
        $matiere->absences()->delete();
        
        // Supprimer la matière
        $matiere->delete();
        
        return redirect()->route('matieres.index')
            ->with('success', 'Matière supprimée définitivement avec succès');
    }

    /**
     * Réactiver une matière
     */
    public function reactivate(Matiere $matiere)
    {
        $matiere->update(['actif' => true]);
        
        return redirect()->route('matieres.index')
            ->with('success', 'Matière réactivée avec succès');
    }

    /**
     * Mettre à jour le coefficient d'une matière (API)
     */
    public function updateCoefficient(Request $request, Matiere $matiere)
    {
        $validator = Validator::make($request->all(), [
            'coefficient' => 'required|numeric|min:0.5|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $matiere->update(['coefficient' => $request->coefficient]);

        return response()->json(['success' => true, 'message' => 'Coefficient mis à jour']);
    }

    /**
     * Effacer toutes les matières et créer les matières par défaut
     */
    public function deleteAll()
    {
        // Supprimer toutes les relations
        \DB::table('enseignant_matiere')->delete();
        \DB::table('notes')->delete();
        \DB::table('emplois_temps')->delete();
        
        // Supprimer toutes les matières
        Matiere::truncate();
        
        // Créer les matières par défaut avec coefficient 1 (l'admin pourra les personnaliser)
        $matieres = [
            ['nom' => 'Anglais', 'code' => 'ANG', 'coefficient' => 1, 'couleur' => '#FF6B6B', 'description' => 'Langue anglaise et littérature', 'actif' => true],
            ['nom' => 'Français', 'code' => 'FR', 'coefficient' => 1, 'couleur' => '#4ECDC4', 'description' => 'Langue française et littérature', 'actif' => true],
            ['nom' => 'Physique', 'code' => 'PHY', 'coefficient' => 1, 'couleur' => '#45B7D1', 'description' => 'Sciences physiques', 'actif' => true],
            ['nom' => 'Chimie', 'code' => 'CHI', 'coefficient' => 1, 'couleur' => '#96CEB4', 'description' => 'Sciences chimiques', 'actif' => true],
            ['nom' => 'Mathématique', 'code' => 'MATH', 'coefficient' => 1, 'couleur' => '#FFEAA7', 'description' => 'Mathématiques', 'actif' => true],
            ['nom' => 'Philosophie', 'code' => 'PHILO', 'coefficient' => 1, 'couleur' => '#DDA0DD', 'description' => 'Philosophie et éthique', 'actif' => true],
            ['nom' => 'Biologie', 'code' => 'BIO', 'coefficient' => 1, 'couleur' => '#98D8C8', 'description' => 'Sciences biologiques', 'actif' => true],
            ['nom' => 'Géologie', 'code' => 'GEO', 'coefficient' => 1, 'couleur' => '#F7DC6F', 'description' => 'Sciences de la terre', 'actif' => true],
            ['nom' => 'ECM', 'code' => 'ECM', 'coefficient' => 1, 'couleur' => '#BB8FCE', 'description' => 'Éducation Civique et Morale', 'actif' => true],
            ['nom' => 'Économie', 'code' => 'ECO', 'coefficient' => 1, 'couleur' => '#F8C471', 'description' => 'Sciences économiques', 'actif' => true]
        ];

        foreach ($matieres as $matiere) {
            Matiere::create($matiere);
        }
        
        return redirect()->route('matieres.index')
            ->with('success', 'Toutes les matières ont été supprimées et les matières par défaut ont été créées');
    }
}
