<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Eleve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentAbsenceController extends Controller
{
    /**
     * Afficher les absences de tous les enfants du parent
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        if (!$parent) {
            abort(403, 'Profil parent non trouvé');
        }
        
        $enfants = $parent->eleves()->with(['classe'])->get();
        
        if ($enfants->isEmpty()) {
            return view('parent.absences.index', compact('enfants'))
                ->with('message', 'Aucun enfant trouvé pour ce compte parent.');
        }

        $enfantsIds = $enfants->pluck('id');
        
        // Récupérer les absences avec filtres
        $query = Absence::whereIn('eleve_id', $enfantsIds)
            ->with(['eleve.utilisateur', 'eleve.classe', 'matiere'])
            ->orderBy('date_absence', 'desc');

        // Filtres
        if ($request->filled('eleve_id')) {
            $query->where('eleve_id', $request->eleve_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_absence', [$request->date_debut, $request->date_fin]);
        }

        $absences = $query->paginate(20);

        // Statistiques
        $stats = [
            'total_absences' => $absences->total(),
            'absences_justifiees' => $absences->where('statut', 'justifiee')->count(),
            'absences_non_justifiees' => $absences->where('statut', 'non_justifiee')->count(),
            'absences_en_attente' => $absences->where('statut', 'en_attente')->count(),
        ];

        return view('parent.absences.index', compact('enfants', 'absences', 'stats'));
    }

    /**
     * Afficher les absences d'un enfant spécifique
     */
    public function show(Eleve $eleve)
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        // Vérifier que le parent a accès à cet élève
        if (!$parent || !$parent->eleves()->where('eleves.id', $eleve->id)->exists()) {
            abort(403, 'Accès non autorisé.');
        }

        $eleve->load(['classe', 'utilisateur']);
        
        // Récupérer les absences de l'enfant
        $absences = $eleve->absences()
            ->with(['matiere'])
            ->orderBy('date_absence', 'desc')
            ->get();

        // Statistiques de l'enfant
        $stats = [
            'total_absences' => $absences->count(),
            'absences_justifiees' => $absences->where('statut', 'justifiee')->count(),
            'absences_non_justifiees' => $absences->where('statut', 'non_justifiee')->count(),
            'absences_en_attente' => $absences->where('statut', 'en_attente')->count(),
            'taux_absence' => $absences->count() > 0 ? 
                round(($absences->where('statut', 'non_justifiee')->count() / $absences->count()) * 100, 1) : 0,
        ];

        // Absences par mois
        $absencesParMois = $absences->groupBy(function($absence) {
            return $absence->date_absence->format('Y-m');
        });

        return view('parent.absences.show', compact('eleve', 'absences', 'stats', 'absencesParMois'));
    }

    /**
     * Justifier une absence
     */
    public function justifier(Absence $absence, Request $request)
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        // Vérifier que le parent a accès à cet élève
        if (!$parent || !$parent->eleves()->where('eleves.id', $absence->eleve_id)->exists()) {
            abort(403, 'Accès non autorisé.');
        }

        $request->validate([
            'motif_justification' => 'required|string|max:500',
            'piece_jointe' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $absence->statut = 'justifiee';
        $absence->motif_justification = $request->motif_justification;
        $absence->date_justification = now();
        $absence->justifie_par = $user->id;

        // Traitement de la pièce jointe
        if ($request->hasFile('piece_jointe')) {
            $path = $request->file('piece_jointe')->store('justifications', 'public');
            $absence->piece_jointe = $path;
        }

        $absence->save();

        return redirect()->back()->with('success', 'Absence justifiée avec succès.');
    }

    /**
     * Afficher le rapport d'absences d'un enfant
     */
    public function rapport(Eleve $eleve, Request $request)
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        // Vérifier que le parent a accès à cet élève
        if (!$parent || !$parent->eleves()->where('eleves.id', $eleve->id)->exists()) {
            abort(403, 'Accès non autorisé.');
        }

        $eleve->load(['classe', 'utilisateur']);
        
        $periode = $request->get('periode', 'trimestre_1');
        
        // Récupérer les absences de la période
        $absences = $eleve->absences()
            ->where('periode_scolaire', $periode)
            ->with(['matiere'])
            ->orderBy('date_absence', 'desc')
            ->get();

        // Statistiques détaillées
        $stats = [
            'total_absences' => $absences->count(),
            'absences_justifiees' => $absences->where('statut', 'justifiee')->count(),
            'absences_non_justifiees' => $absences->where('statut', 'non_justifiee')->count(),
            'absences_en_attente' => $absences->where('statut', 'en_attente')->count(),
            'taux_absence' => $absences->count() > 0 ? 
                round(($absences->where('statut', 'non_justifiee')->count() / $absences->count()) * 100, 1) : 0,
        ];

        // Absences par matière
        $absencesParMatiere = $absences->groupBy('matiere.nom');

        return view('parent.absences.rapport', compact('eleve', 'absences', 'stats', 'absencesParMatiere', 'periode'));
    }

    /**
     * Exporter les absences d'un enfant
     */
    public function export(Eleve $eleve, Request $request)
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        // Vérifier que le parent a accès à cet élève
        if (!$parent || !$parent->eleves()->where('eleves.id', $eleve->id)->exists()) {
            abort(403, 'Accès non autorisé.');
        }

        $format = $request->get('format', 'pdf');
        
        $absences = $eleve->absences()
            ->with(['matiere'])
            ->orderBy('date_absence', 'desc')
            ->get();

        if ($format === 'excel') {
            return $this->exportExcel($eleve, $absences);
        } else {
            return $this->exportPdf($eleve, $absences);
        }
    }

    private function exportPdf($eleve, $absences)
    {
        // Logique d'export PDF
        // À implémenter selon vos besoins
        return response()->json(['message' => 'Export PDF à implémenter']);
    }

    private function exportExcel($eleve, $absences)
    {
        // Logique d'export Excel
        // À implémenter selon vos besoins
        return response()->json(['message' => 'Export Excel à implémenter']);
    }
}
