<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Utilisateur;
use App\Models\Eleve;
use App\Models\Enseignant;
use App\Models\ParentModel;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Paiement;
use App\Models\Absence;
use App\Models\Note;
use App\Models\Evenement;

class DashboardController extends Controller
{
    /**
     * Afficher la page d'accueil selon le rôle de l'utilisateur
     */
    public function index()
    {
        $user = Auth::user();
        
        // Rediriger vers le dashboard approprié selon le rôle
        switch ($user->role) {
            case 'admin':
            case 'personnel_admin':
                return $this->adminDashboard();
                
            case 'teacher':
                return $this->teacherDashboard();
                
            case 'student':
                return $this->studentDashboard();
                
            case 'parent':
                return $this->parentDashboard();
                
            default:
                // Par défaut, afficher le dashboard admin
                return $this->adminDashboard();
        }
    }

    /**
     * Dashboard Administrateur
     */
    public function adminDashboard()
    {
        $user = Auth::user();
        
        // Statistiques générales
        $stats = [
            'eleves' => Eleve::count(),
            'enseignants' => Enseignant::count(),
            'parents' => ParentModel::count(),
            'classes' => Classe::count(),
            'matieres' => Matiere::count(),
            'paiements_total' => Paiement::sum('montant_paye'),
            'absences_total' => Absence::count(),
            'notes_total' => Note::count(),
        ];
        
        // Derniers paiements
        $derniersPaiements = Paiement::with(['fraisScolarite.eleve.utilisateur', 'encaissePar'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Dernières absences
        $dernieresAbsences = Absence::with(['eleve.utilisateur', 'matiere'])
            ->orderBy('date_absence', 'desc')
            ->limit(10)
            ->get();

        // Dernières notes
        $dernieresNotes = Note::with(['eleve.utilisateur', 'matiere', 'enseignant.utilisateur'])
            ->orderBy('date_evaluation', 'desc')
            ->limit(10)
            ->get();

        // Statistiques par mois (pour les graphiques)
        $paiementsParMois = Paiement::selectRaw('MONTH(created_at) as mois, SUM(montant_paye) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        $absencesParMois = Absence::selectRaw('MONTH(date_absence) as mois, COUNT(*) as total')
            ->whereYear('date_absence', now()->year)
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        return view('admin.dashboard', compact(
            'stats', 
            'derniersPaiements', 
            'dernieresAbsences', 
            'dernieresNotes',
            'paiementsParMois',
            'absencesParMois',
            'user'
        ));
    }

    /**
     * Dashboard Enseignant
     */
    private function teacherDashboard()
    {
        $user = Auth::user();
        $enseignant = $user->enseignant;
        
        if (!$enseignant) {
            abort(403, 'Profil enseignant non trouvé');
        }

        $emploisDuJour = $enseignant->emploisTemps()
            ->where('jour_semaine', strtolower(now()->locale('fr')->dayName))
            ->actif()
            ->orderBy('heure_debut')
            ->get();

        // Récupérer les événements pour l'enseignant
        $classeIds = $enseignant->emploisTemps()->pluck('classe_id')->unique()->filter();
        $evenements = Evenement::where(function($q) use ($classeIds) {
            // Événements publics
            $q->where('public', true);
            
            // Événements spécifiques aux classes où l'enseignant enseigne
            if ($classeIds->isNotEmpty()) {
                $q->orWhereIn('classe_id', $classeIds);
            }
        })
        ->where('date_debut', '>=', now()->startOfDay())
        ->orderBy('date_debut', 'asc')
        ->limit(5)
        ->get();

        return view('dashboards.teacher', compact('enseignant', 'emploisDuJour', 'evenements'));
    }

    /**
     * Dashboard Élève
     */
    private function studentDashboard()
    {
        $user = Auth::user();
        $eleve = $user->eleve;
        
        if (!$eleve) {
            abort(403, 'Profil élève non trouvé');
        }

        $emploisDuJour = [];
        if ($eleve->classe) {
            $emploisDuJour = $eleve->classe->emploisTemps()
                ->where('jour_semaine', strtolower(now()->locale('fr')->dayName))
                ->actif()
                ->orderBy('heure_debut')
                ->get();
        }

        $dernieresNotes = $eleve->notes()
            ->with(['matiere', 'enseignant.utilisateur'])
            ->orderBy('date_evaluation', 'desc')
            ->limit(5)
            ->get();

        // Récupérer les événements pour l'élève
        $evenements = Evenement::where(function($q) use ($eleve) {
            // Événements publics
            $q->where('public', true);
            
            // Événements spécifiques à la classe de l'élève
            if ($eleve->classe_id) {
                $q->orWhere('classe_id', $eleve->classe_id);
            }
        })
        ->where('date_debut', '>=', now()->startOfDay())
        ->orderBy('date_debut', 'asc')
        ->limit(5)
        ->get();

        return view('dashboards.student', compact('eleve', 'emploisDuJour', 'dernieresNotes', 'evenements'));
    }

    /**
     * Dashboard Parent
     */
    public function parentDashboard()
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        if (!$parent) {
            abort(403, 'Profil parent non trouvé');
        }

        $enfants = $parent->eleves()->with(['classe', 'notes', 'absences'])->get();

        // Statistiques pour les parents
        $stats = [
            'total_enfants' => $enfants->count(),
            'total_notes' => $enfants->sum(function($enfant) {
                return $enfant->notes->count();
            }),
            'total_absences' => $enfants->sum(function($enfant) {
                return $enfant->absences->count();
            }),
            'moyenne_generale' => $enfants->flatMap->notes->whereNotNull('note_finale')->avg('note_finale'),
        ];

        // Dernières activités (notes et absences)
        $dernieresActivites = collect();
        
        foreach ($enfants as $enfant) {
            // Ajouter les dernières notes
            foreach ($enfant->notes->take(3) as $note) {
                $noteFinale = $note->note_finale ?? 0;
                $appreciation = $enfant->classe->getAppreciation($noteFinale);
                
                $dernieresActivites->push([
                    'type' => 'note',
                    'date' => $note->date_evaluation,
                    'enfant' => $enfant,
                    'contenu' => $note,
                    'icone' => 'fas fa-chart-line',
                    'couleur' => $appreciation['color']
                ]);
            }
            
            // Ajouter les dernières absences
            foreach ($enfant->absences->take(3) as $absence) {
                $dernieresActivites->push([
                    'type' => 'absence',
                    'date' => $absence->date_absence,
                    'enfant' => $enfant,
                    'contenu' => $absence,
                    'icone' => 'fas fa-exclamation-triangle',
                    'couleur' => $absence->statut == 'justifiee' ? 'success' : 'warning'
                ]);
            }
        }

        // Trier par date décroissante et prendre les 10 plus récentes
        $dernieresActivites = $dernieresActivites->sortByDesc('date')->take(10);

        // Récupérer les notifications réelles du parent
        $notifications = \App\Models\Notification::where('utilisateur_id', $parent->utilisateur_id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Récupérer les événements pour les parents (publics et pour les classes de leurs enfants)
        $classeIds = $enfants->pluck('classe_id')->filter()->unique();
        $evenements = Evenement::where(function($q) use ($classeIds) {
            // Événements publics
            $q->where('public', true);
            
            // Événements spécifiques aux classes des enfants
            if ($classeIds->isNotEmpty()) {
                $q->orWhereIn('classe_id', $classeIds);
            }
        })
        ->where('date_debut', '>=', now()->startOfDay())
        ->orderBy('date_debut', 'asc')
        ->limit(5)
        ->get();

        return view('dashboards.parent', compact('parent', 'enfants', 'stats', 'dernieresActivites', 'notifications', 'evenements'));
    }
}
