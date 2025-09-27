<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Eleve;
use App\Models\Enseignant;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Note;
use App\Models\Absence;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistiqueController extends Controller
{
    /**
     * Récupérer les statistiques générales du système
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Statistiques générales
        $totalEleves = Eleve::count();
        $totalEnseignants = Enseignant::count();
        $totalClasses = Classe::count();
        $totalMatieres = Matiere::count();
        
        // Statistiques des élèves par classe
        $elevesParClasse = Classe::withCount('eleves')
            ->orderBy('nom')
            ->get()
            ->map(function ($classe) {
                return [
                    'classe' => $classe->nom,
                    'niveau' => $classe->niveau,
                    'total_eleves' => $classe->eleves_count
                ];
            });
        
        // Statistiques des notes
        $moyenneGenerale = Note::avg('valeur');
        $notesParMatiere = Note::select('matieres.nom as matiere', DB::raw('AVG(notes.valeur) as moyenne'))
            ->join('matieres', 'notes.matiere_id', '=', 'matieres.id')
            ->groupBy('matieres.id', 'matieres.nom')
            ->orderBy('moyenne', 'desc')
            ->get();
        
        // Statistiques des absences
        $totalAbsences = Absence::count();
        $absencesJustifiees = Absence::where('justifiee', true)->count();
        $absencesNonJustifiees = Absence::where('justifiee', false)->count();
        $absencesParClasse = Absence::select('classes.nom as classe', DB::raw('COUNT(*) as total'))
            ->join('eleves', 'absences.eleve_id', '=', 'eleves.id')
            ->join('classes', 'eleves.classe_id', '=', 'classes.id')
            ->groupBy('classes.id', 'classes.nom')
            ->orderBy('total', 'desc')
            ->get();
        
        // Statistiques des paiements
        $totalPaiements = Paiement::sum('montant');
        $paiementsParMois = Paiement::select(DB::raw('MONTH(date_paiement) as mois'), DB::raw('YEAR(date_paiement) as annee'), DB::raw('SUM(montant) as total'))
            ->groupBy('mois', 'annee')
            ->orderBy('annee')
            ->orderBy('mois')
            ->get()
            ->map(function ($item) {
                return [
                    'mois' => $item->mois,
                    'annee' => $item->annee,
                    'nom_mois' => date('F', mktime(0, 0, 0, $item->mois, 1)),
                    'total' => $item->total
                ];
            });
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'general' => [
                    'total_eleves' => $totalEleves,
                    'total_enseignants' => $totalEnseignants,
                    'total_classes' => $totalClasses,
                    'total_matieres' => $totalMatieres,
                ],
                'eleves' => [
                    'eleves_par_classe' => $elevesParClasse,
                ],
                'notes' => [
                    'moyenne_generale' => $moyenneGenerale,
                    'notes_par_matiere' => $notesParMatiere,
                ],
                'absences' => [
                    'total_absences' => $totalAbsences,
                    'absences_justifiees' => $absencesJustifiees,
                    'absences_non_justifiees' => $absencesNonJustifiees,
                    'absences_par_classe' => $absencesParClasse,
                ],
                'paiements' => [
                    'total_paiements' => $totalPaiements,
                    'paiements_par_mois' => $paiementsParMois,
                ],
            ],
            'message' => 'Statistiques récupérées avec succès'
        ]);
    }

    /**
     * Récupérer les statistiques des élèves
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function eleves(Request $request)
    {
        // Total des élèves
        $totalEleves = Eleve::count();
        
        // Élèves par genre
        $elevesParGenre = Eleve::select('utilisateurs.genre', DB::raw('COUNT(*) as total'))
            ->join('utilisateurs', 'eleves.utilisateur_id', '=', 'utilisateurs.id')
            ->groupBy('utilisateurs.genre')
            ->get()
            ->map(function ($item) {
                return [
                    'genre' => $item->genre,
                    'total' => $item->total
                ];
            });
        
        // Élèves par classe
        $elevesParClasse = Classe::withCount('eleves')
            ->orderBy('nom')
            ->get()
            ->map(function ($classe) {
                return [
                    'classe' => $classe->nom,
                    'niveau' => $classe->niveau,
                    'total_eleves' => $classe->eleves_count
                ];
            });
        
        // Élèves par âge
        $elevesParAge = Eleve::select(DB::raw('TIMESTAMPDIFF(YEAR, utilisateurs.date_naissance, CURDATE()) as age'), DB::raw('COUNT(*) as total'))
            ->join('utilisateurs', 'eleves.utilisateur_id', '=', 'utilisateurs.id')
            ->whereNotNull('utilisateurs.date_naissance')
            ->groupBy('age')
            ->orderBy('age')
            ->get();
        
        // Nouveaux élèves par mois
        $nouveauxElevesParMois = Eleve::select(DB::raw('MONTH(created_at) as mois'), DB::raw('YEAR(created_at) as annee'), DB::raw('COUNT(*) as total'))
            ->whereYear('created_at', date('Y'))
            ->groupBy('mois', 'annee')
            ->orderBy('mois')
            ->get()
            ->map(function ($item) {
                return [
                    'mois' => $item->mois,
                    'annee' => $item->annee,
                    'nom_mois' => date('F', mktime(0, 0, 0, $item->mois, 1)),
                    'total' => $item->total
                ];
            });
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'total_eleves' => $totalEleves,
                'eleves_par_genre' => $elevesParGenre,
                'eleves_par_classe' => $elevesParClasse,
                'eleves_par_age' => $elevesParAge,
                'nouveaux_eleves_par_mois' => $nouveauxElevesParMois,
            ],
            'message' => 'Statistiques des élèves récupérées avec succès'
        ]);
    }

    /**
     * Récupérer les statistiques des enseignants
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function enseignants(Request $request)
    {
        // Total des enseignants
        $totalEnseignants = Enseignant::count();
        
        // Enseignants par genre
        $enseignantsParGenre = Enseignant::select('utilisateurs.genre', DB::raw('COUNT(*) as total'))
            ->join('utilisateurs', 'enseignants.utilisateur_id', '=', 'utilisateurs.id')
            ->groupBy('utilisateurs.genre')
            ->get()
            ->map(function ($item) {
                return [
                    'genre' => $item->genre,
                    'total' => $item->total
                ];
            });
        
        // Enseignants par spécialité
        $enseignantsParSpecialite = Enseignant::select('specialite', DB::raw('COUNT(*) as total'))
            ->groupBy('specialite')
            ->orderBy('total', 'desc')
            ->get();
        
        // Enseignants par matière
        $enseignantsParMatiere = Matiere::withCount('enseignants')
            ->orderBy('nom')
            ->get()
            ->map(function ($matiere) {
                return [
                    'matiere' => $matiere->nom,
                    'total_enseignants' => $matiere->enseignants_count
                ];
            });
        
        // Nouveaux enseignants par mois
        $nouveauxEnseignantsParMois = Enseignant::select(DB::raw('MONTH(created_at) as mois'), DB::raw('YEAR(created_at) as annee'), DB::raw('COUNT(*) as total'))
            ->whereYear('created_at', date('Y'))
            ->groupBy('mois', 'annee')
            ->orderBy('mois')
            ->get()
            ->map(function ($item) {
                return [
                    'mois' => $item->mois,
                    'annee' => $item->annee,
                    'nom_mois' => date('F', mktime(0, 0, 0, $item->mois, 1)),
                    'total' => $item->total
                ];
            });
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'total_enseignants' => $totalEnseignants,
                'enseignants_par_genre' => $enseignantsParGenre,
                'enseignants_par_specialite' => $enseignantsParSpecialite,
                'enseignants_par_matiere' => $enseignantsParMatiere,
                'nouveaux_enseignants_par_mois' => $nouveauxEnseignantsParMois,
            ],
            'message' => 'Statistiques des enseignants récupérées avec succès'
        ]);
    }

    /**
     * Récupérer les statistiques des notes
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function notes(Request $request)
    {
        // Moyenne générale
        $moyenneGenerale = Note::avg('valeur');
        
        // Notes par matière
        $notesParMatiere = Note::select('matieres.nom as matiere', DB::raw('AVG(notes.valeur) as moyenne'))
            ->join('matieres', 'notes.matiere_id', '=', 'matieres.id')
            ->groupBy('matieres.id', 'matieres.nom')
            ->orderBy('moyenne', 'desc')
            ->get();
        
        // Notes par classe
        $notesParClasse = Note::select('classes.nom as classe', DB::raw('AVG(notes.valeur) as moyenne'))
            ->join('eleves', 'notes.eleve_id', '=', 'eleves.id')
            ->join('classes', 'eleves.classe_id', '=', 'classes.id')
            ->groupBy('classes.id', 'classes.nom')
            ->orderBy('moyenne', 'desc')
            ->get();
        
        // Distribution des notes
        $distributionNotes = [
            '0-4' => Note::whereBetween('valeur', [0, 4])->count(),
            '4-8' => Note::whereBetween('valeur', [4, 8])->count(),
            '8-12' => Note::whereBetween('valeur', [8, 12])->count(),
            '12-16' => Note::whereBetween('valeur', [12, 16])->count(),
            '16-20' => Note::whereBetween('valeur', [16, 20])->count(),
        ];
        
        // Évolution des moyennes par trimestre
        $evolutionMoyennes = Note::select('trimestre', DB::raw('AVG(valeur) as moyenne'))
            ->groupBy('trimestre')
            ->orderBy('trimestre')
            ->get();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'moyenne_generale' => $moyenneGenerale,
                'notes_par_matiere' => $notesParMatiere,
                'notes_par_classe' => $notesParClasse,
                'distribution_notes' => $distributionNotes,
                'evolution_moyennes' => $evolutionMoyennes,
            ],
            'message' => 'Statistiques des notes récupérées avec succès'
        ]);
    }

    /**
     * Récupérer les statistiques des absences
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function absences(Request $request)
    {
        // Total des absences
        $totalAbsences = Absence::count();
        $absencesJustifiees = Absence::where('justifiee', true)->count();
        $absencesNonJustifiees = Absence::where('justifiee', false)->count();
        
        // Absences par classe
        $absencesParClasse = Absence::select('classes.nom as classe', DB::raw('COUNT(*) as total'))
            ->join('eleves', 'absences.eleve_id', '=', 'eleves.id')
            ->join('classes', 'eleves.classe_id', '=', 'classes.id')
            ->groupBy('classes.id', 'classes.nom')
            ->orderBy('total', 'desc')
            ->get();
        
        // Absences par matière
        $absencesParMatiere = Absence::select('matieres.nom as matiere', DB::raw('COUNT(*) as total'))
            ->join('matieres', 'absences.matiere_id', '=', 'matieres.id')
            ->groupBy('matieres.id', 'matieres.nom')
            ->orderBy('total', 'desc')
            ->get();
        
        // Absences par jour de la semaine
        $absencesParJour = Absence::select(DB::raw('DAYOFWEEK(date_absence) as jour'), DB::raw('COUNT(*) as total'))
            ->groupBy('jour')
            ->orderBy('jour')
            ->get()
            ->map(function ($item) {
                $joursSemaine = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
                return [
                    'jour' => $item->jour,
                    'nom_jour' => $joursSemaine[$item->jour - 1],
                    'total' => $item->total
                ];
            });
        
        // Évolution des absences par mois
        $evolutionAbsences = Absence::select(DB::raw('MONTH(date_absence) as mois'), DB::raw('YEAR(date_absence) as annee'), DB::raw('COUNT(*) as total'))
            ->whereYear('date_absence', date('Y'))
            ->groupBy('mois', 'annee')
            ->orderBy('mois')
            ->get()
            ->map(function ($item) {
                return [
                    'mois' => $item->mois,
                    'annee' => $item->annee,
                    'nom_mois' => date('F', mktime(0, 0, 0, $item->mois, 1)),
                    'total' => $item->total
                ];
            });
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'total_absences' => $totalAbsences,
                'absences_justifiees' => $absencesJustifiees,
                'absences_non_justifiees' => $absencesNonJustifiees,
                'absences_par_classe' => $absencesParClasse,
                'absences_par_matiere' => $absencesParMatiere,
                'absences_par_jour' => $absencesParJour,
                'evolution_absences' => $evolutionAbsences,
            ],
            'message' => 'Statistiques des absences récupérées avec succès'
        ]);
    }

    /**
     * Récupérer les statistiques des paiements
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function paiements(Request $request)
    {
        // Total des paiements
        $totalPaiements = Paiement::sum('montant');
        $nombrePaiements = Paiement::count();
        
        // Paiements par statut
        $paiementsParStatut = Paiement::select('statut', DB::raw('COUNT(*) as total'), DB::raw('SUM(montant) as montant_total'))
            ->groupBy('statut')
            ->get()
            ->map(function ($item) {
                return [
                    'statut' => $item->statut,
                    'total' => $item->total,
                    'montant_total' => $item->montant_total
                ];
            });
        
        // Paiements par type
        $paiementsParType = Paiement::select('type_paiement', DB::raw('COUNT(*) as total'), DB::raw('SUM(montant) as montant_total'))
            ->groupBy('type_paiement')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type_paiement,
                    'total' => $item->total,
                    'montant_total' => $item->montant_total
                ];
            });
        
        // Paiements par méthode
        $paiementsParMethode = Paiement::select('methode_paiement', DB::raw('COUNT(*) as total'), DB::raw('SUM(montant) as montant_total'))
            ->groupBy('methode_paiement')
            ->get()
            ->map(function ($item) {
                return [
                    'methode' => $item->methode_paiement,
                    'total' => $item->total,
                    'montant_total' => $item->montant_total
                ];
            });
        
        // Paiements par classe
        $paiementsParClasse = Paiement::select('classes.nom as classe', DB::raw('COUNT(*) as total'), DB::raw('SUM(paiements.montant) as montant_total'))
            ->join('eleves', 'paiements.eleve_id', '=', 'eleves.id')
            ->join('classes', 'eleves.classe_id', '=', 'classes.id')
            ->groupBy('classes.id', 'classes.nom')
            ->orderBy('montant_total', 'desc')
            ->get();
        
        // Évolution des paiements par mois
        $evolutionPaiements = Paiement::select(DB::raw('MONTH(date_paiement) as mois'), DB::raw('YEAR(date_paiement) as annee'), DB::raw('SUM(montant) as montant_total'))
            ->groupBy('mois', 'annee')
            ->orderBy('annee')
            ->orderBy('mois')
            ->get()
            ->map(function ($item) {
                return [
                    'mois' => $item->mois,
                    'annee' => $item->annee,
                    'nom_mois' => date('F', mktime(0, 0, 0, $item->mois, 1)),
                    'montant_total' => $item->montant_total
                ];
            });
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'total_paiements' => $totalPaiements,
                'nombre_paiements' => $nombrePaiements,
                'paiements_par_statut' => $paiementsParStatut,
                'paiements_par_type' => $paiementsParType,
                'paiements_par_methode' => $paiementsParMethode,
                'paiements_par_classe' => $paiementsParClasse,
                'evolution_paiements' => $evolutionPaiements,
            ],
            'message' => 'Statistiques des paiements récupérées avec succès'
        ]);
    }
}