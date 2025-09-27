<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Eleve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentNoteController extends Controller
{
    /**
     * Afficher les notes de tous les enfants du parent
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
            return view('parent.notes.index', compact('enfants'))
                ->with('message', 'Aucun enfant trouvé pour ce compte parent.');
        }

        $enfantsIds = $enfants->pluck('id');
        
        // Récupérer les notes avec filtres
        $query = Note::whereIn('eleve_id', $enfantsIds)
            ->with(['eleve.utilisateur', 'eleve.classe', 'matiere', 'enseignant.utilisateur'])
            ->orderBy('date_evaluation', 'desc');

        // Filtres
        if ($request->filled('eleve_id')) {
            $query->where('eleve_id', $request->eleve_id);
        }

        if ($request->filled('matiere_id')) {
            $query->where('matiere_id', $request->matiere_id);
        }

        if ($request->filled('type_evaluation')) {
            $query->where('type_evaluation', $request->type_evaluation);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_evaluation', [$request->date_debut, $request->date_fin]);
        }

        $notes = $query->paginate(20);

        // Statistiques
        $stats = [
            'total_notes' => $notes->total(),
            'moyenne_generale' => $notes->avg('note_sur'),
            'notes_sup_10' => $notes->where('note_sur', '>=', 10)->count(),
            'notes_inf_10' => $notes->where('note_sur', '<', 10)->count(),
        ];

        // Données pour les filtres
        $matieres = \App\Models\Matiere::whereHas('notes', function($q) use ($enfantsIds) {
            $q->whereIn('eleve_id', $enfantsIds);
        })->orderBy('nom')->get();

        return view('parent.notes.index', compact('enfants', 'notes', 'stats', 'matieres'));
    }

    /**
     * Afficher les notes d'un enfant spécifique
     */
    public function show(Eleve $eleve)
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        // Vérifier que le parent a accès à cet élève
        if (!$parent || !$parent->eleves()->where('id', $eleve->id)->exists()) {
            abort(403, 'Accès non autorisé.');
        }

        $eleve->load(['classe', 'utilisateur']);
        
        // Récupérer les notes de l'enfant
        $notes = $eleve->notes()
            ->with(['matiere', 'enseignant.utilisateur'])
            ->orderBy('date_evaluation', 'desc')
            ->get();

        // Statistiques de l'enfant
        $stats = [
            'total_notes' => $notes->count(),
            'moyenne_generale' => $notes->avg('note_sur'),
            'notes_sup_10' => $notes->where('note_sur', '>=', 10)->count(),
            'notes_inf_10' => $notes->where('note_sur', '<', 10)->count(),
            'meilleure_note' => $notes->max('note_sur'),
            'moins_bonne_note' => $notes->min('note_sur'),
        ];

        // Notes par matière
        $notesParMatiere = $notes->groupBy('matiere.nom');

        return view('parent.notes.show', compact('eleve', 'notes', 'stats', 'notesParMatiere'));
    }

    /**
     * Afficher le bulletin d'un enfant
     */
    public function bulletin(Eleve $eleve, Request $request)
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        // Vérifier que le parent a accès à cet élève
        if (!$parent || !$parent->eleves()->where('id', $eleve->id)->exists()) {
            abort(403, 'Accès non autorisé.');
        }

        $eleve->load(['classe', 'utilisateur']);
        
        $periode = $request->get('periode', 'trimestre_1');
        
        // Récupérer les notes de la période
        $notes = $eleve->notes()
            ->where('periode_scolaire', $periode)
            ->with(['matiere', 'enseignant.utilisateur'])
            ->get();

        // Calculer les moyennes par matière
        $moyennesParMatiere = $notes->groupBy('matiere.nom')->map(function($notesMatiere) {
            return [
                'moyenne' => $notesMatiere->avg('note_sur'),
                'coefficient' => $notesMatiere->first()->matiere->coefficient ?? 1,
                'notes' => $notesMatiere->count(),
            ];
        });

        // Moyenne générale pondérée
        $moyenneGenerale = $moyennesParMatiere->sum(function($matiere) {
            return $matiere['moyenne'] * $matiere['coefficient'];
        }) / $moyennesParMatiere->sum('coefficient');

        return view('parent.notes.bulletin', compact('eleve', 'notes', 'moyennesParMatiere', 'moyenneGenerale', 'periode'));
    }

    /**
     * Exporter les notes d'un enfant
     */
    public function export(Eleve $eleve, Request $request)
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        // Vérifier que le parent a accès à cet élève
        if (!$parent || !$parent->eleves()->where('id', $eleve->id)->exists()) {
            abort(403, 'Accès non autorisé.');
        }

        $format = $request->get('format', 'pdf');
        
        $notes = $eleve->notes()
            ->with(['matiere', 'enseignant.utilisateur'])
            ->orderBy('date_evaluation', 'desc')
            ->get();

        if ($format === 'excel') {
            return $this->exportExcel($eleve, $notes);
        } else {
            return $this->exportPdf($eleve, $notes);
        }
    }

    private function exportPdf($eleve, $notes)
    {
        // Logique d'export PDF
        // À implémenter selon vos besoins
        return response()->json(['message' => 'Export PDF à implémenter']);
    }

    private function exportExcel($eleve, $notes)
    {
        // Logique d'export Excel
        // À implémenter selon vos besoins
        return response()->json(['message' => 'Export Excel à implémenter']);
    }
}
