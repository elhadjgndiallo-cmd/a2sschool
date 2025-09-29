<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Eleve;
use App\Models\Note;
use App\Models\Absence;
use App\Models\EmploiTemps;

class StudentController extends Controller
{
    /**
     * Afficher l'emploi du temps de l'élève
     */
    public function emploiTemps()
    {
        $user = Auth::user();
        $eleve = $user->eleve;
        
        if (!$eleve) {
            abort(403, 'Profil élève non trouvé');
        }

        if (!$eleve->classe) {
            return view('student.emploi-temps', compact('eleve'))
                ->with('error', 'Vous n\'êtes pas assigné à une classe.');
        }

        // Récupérer l'emploi du temps de la classe de l'élève
        $emploisTemps = $eleve->classe->emploisTemps()
            ->with(['matiere', 'enseignant.utilisateur'])
            ->actif()
            ->orderBy('jour_semaine')
            ->orderBy('heure_debut')
            ->get();

        return view('student.emploi-temps', compact('eleve', 'emploisTemps'));
    }

    /**
     * Afficher les notes de l'élève
     */
    public function notes()
    {
        $user = Auth::user();
        $eleve = $user->eleve;
        
        if (!$eleve) {
            abort(403, 'Profil élève non trouvé');
        }

        // Récupérer toutes les notes de l'élève
        $notes = $eleve->notes()
            ->with(['matiere', 'enseignant.utilisateur'])
            ->orderBy('date_evaluation', 'desc')
            ->paginate(20);

        // Statistiques des notes
        $statistiques = [
            'total_notes' => $eleve->notes()->count(),
            'moyenne_generale' => $eleve->notes()->whereNotNull('note_finale')->avg('note_finale'),
            'meilleure_note' => $eleve->notes()->whereNotNull('note_finale')->max('note_finale'),
            'derniere_note' => $eleve->notes()->latest('date_evaluation')->first()
        ];

        return view('student.notes', compact('eleve', 'notes', 'statistiques'));
    }

    /**
     * Afficher les absences de l'élève
     */
    public function absences()
    {
        $user = Auth::user();
        $eleve = $user->eleve;
        
        if (!$eleve) {
            abort(403, 'Profil élève non trouvé');
        }

        // Récupérer toutes les absences de l'élève
        $absences = $eleve->absences()
            ->with(['matiere', 'saisiPar'])
            ->orderBy('date_absence', 'desc')
            ->paginate(20);

        // Statistiques des absences
        $statistiques = [
            'total_absences' => $eleve->absences()->count(),
            'absences_justifiees' => $eleve->absences()->where('statut', 'justifiee')->count(),
            'absences_non_justifiees' => $eleve->absences()->where('statut', 'non_justifiee')->count(),
            'absences_ce_mois' => $eleve->absences()
                ->whereMonth('date_absence', now()->month)
                ->whereYear('date_absence', now()->year)
                ->count()
        ];

        return view('student.absences', compact('eleve', 'absences', 'statistiques'));
    }

    /**
     * Afficher le bulletin de l'élève
     */
    public function bulletin()
    {
        $user = Auth::user();
        $eleve = $user->eleve;
        
        if (!$eleve) {
            abort(403, 'Profil élève non trouvé');
        }

        // Récupérer les notes par matière
        $notesParMatiere = $eleve->notes()
            ->with(['matiere', 'enseignant.utilisateur'])
            ->get()
            ->groupBy('matiere.nom');

        // Calculer les moyennes par matière
        $moyennesParMatiere = [];
        foreach ($notesParMatiere as $matiere => $notes) {
            $moyennesParMatiere[$matiere] = [
                'moyenne' => $notes->avg('note'),
                'total_notes' => $notes->count(),
                'notes' => $notes
            ];
        }

        return view('student.bulletin', compact('eleve', 'moyennesParMatiere'));
    }
}




