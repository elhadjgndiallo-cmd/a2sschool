<?php

namespace App\Http\Controllers;

use App\Models\AbsenceEnseignant;
use App\Models\AnneeScolaire;
use App\Models\EmploiTemps;
use App\Models\Enseignant;
use App\Services\HeuresEnseignantService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AbsenceEnseignantController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('absences-enseignants.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à voir les absences des enseignants.');
        }

        $aujourdhui = now()->toDateString();
        $enseignants = Enseignant::listeDeroulante();
        $debutMois = now()->copy()->startOfMonth()->toDateString();
        $finMois = now()->copy()->endOfMonth()->toDateString();
        $moisLabel = ucfirst(now()->locale('fr')->translatedFormat('F Y'));

        $absencesDuMois = AbsenceEnseignant::with('classe')
            ->duMois($debutMois, $finMois)
            ->orderBy('date_debut')
            ->orderBy('heure_debut')
            ->get()
            ->groupBy(fn ($absence) => (int) $absence->enseignant_id);

        $absencesDuJour = $absencesDuMois->map(function ($absences) use ($aujourdhui) {
            return $absences->filter(function ($absence) use ($aujourdhui) {
                $debut = $absence->date_debut?->toDateString();
                $fin = $absence->date_fin?->toDateString();

                return $debut && $fin && $debut <= $aujourdhui && $fin >= $aujourdhui;
            })->values();
        });

        $jour = $this->jourSemaineAujourdhui();

        $coursDuJour = [];
        if ($jour && $enseignants->isNotEmpty()) {
            $coursDuJour = EmploiTemps::with(['classe', 'matiere'])
                ->actif()
                ->pourAnneeActive()
                ->jour($jour)
                ->whereIn('enseignant_id', $enseignants->pluck('id'))
                ->orderBy('heure_debut')
                ->get()
                ->groupBy('enseignant_id')
                ->map(function ($cours) {
                    return $cours->map(function ($c) {
                        return [
                            'classe_id' => $c->classe_id,
                            'classe' => $c->classe->nom ?? '',
                            'matiere' => $c->matiere->nom ?? '',
                            'heure_debut' => $this->formatHeureCourt($c->heure_debut),
                            'heure_fin' => $this->formatHeureCourt($c->heure_fin),
                        ];
                    })->values();
                })
                ->toArray();
        }

        $heuresPrevuesMois = app(HeuresEnseignantService::class)->heuresPrevuesPourCollection(
            $enseignants,
            Carbon::parse($debutMois)->startOfDay(),
            Carbon::parse($finMois)->startOfDay()
        );

        return view('absences-enseignants.index', compact(
            'enseignants',
            'absencesDuJour',
            'absencesDuMois',
            'heuresPrevuesMois',
            'aujourdhui',
            'moisLabel',
            'coursDuJour'
        ));
    }

    public function statistiques(Request $request)
    {
        if (!auth()->user()->hasPermission('absences-enseignants.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à voir les absences des enseignants.');
        }

        $annee = AnneeScolaire::anneeActive();
        $vue = $request->query('vue', 'mois') === 'annee' ? 'annee' : 'mois';
        $moisOptions = $this->moisDisponibles($annee);

        if ($vue === 'annee') {
            if (!$annee?->date_debut || !$annee?->date_fin) {
                return redirect()->route('absences-enseignants.statistiques')
                    ->with('error', 'Aucune année scolaire active n\'est définie.');
            }

            [$debut, $fin] = $this->periodeAnneeScolaire($annee);
            $mois = null;
            $periodeLabel = 'Année scolaire ' . $annee->nom;
            $moisPrecedent = null;
            $moisSuivant = null;
        } else {
            $mois = $request->query('mois', now()->format('Y-m'));
            if (!preg_match('/^\d{4}-\d{2}$/', $mois)) {
                $mois = now()->format('Y-m');
            }

            $debut = Carbon::createFromFormat('Y-m-d', $mois . '-01')->startOfMonth();
            $fin = $debut->copy()->endOfMonth();
            $periodeLabel = ucfirst($debut->locale('fr')->translatedFormat('F Y'));
            $moisPrecedent = $debut->copy()->subMonth()->format('Y-m');
            $moisSuivant = $debut->copy()->addMonth()->format('Y-m');
        }

        $absences = AbsenceEnseignant::query()
            ->duMois($debut->toDateString(), $fin->toDateString())
            ->get()
            ->groupBy(fn ($absence) => (int) $absence->enseignant_id);

        $lignes = Enseignant::listeDeroulante()->map(function ($enseignant) use ($absences) {
            $user = $enseignant->utilisateur;
            $liste = $absences->get((int) $enseignant->id)
                ?? $absences->get((string) $enseignant->id)
                ?? collect();

            return [
                'prenom' => $user->prenom ?? '',
                'nom' => $user->nom ?? '',
                'specialite' => $enseignant->specialite ?: '—',
                'total_minutes' => $liste->sum(fn ($absence) => $absence->dureeMinutes()),
            ];
        })->sortBy(fn ($ligne) => strtolower($ligne['nom'] . ' ' . $ligne['prenom']))->values();

        $totalMinutes = $lignes->sum('total_minutes');

        return view('absences-enseignants.statistiques', compact(
            'lignes',
            'vue',
            'mois',
            'moisOptions',
            'periodeLabel',
            'moisPrecedent',
            'moisSuivant',
            'totalMinutes',
            'annee'
        ));
    }

    public function saisir()
    {
        return redirect()->route('absences-enseignants.index');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('absences-enseignants.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à enregistrer une absence enseignant.');
        }

        $request->validate([
            'enseignant_id' => 'required|exists:enseignants,id',
            'absence' => 'accepted',
            'classe_id' => 'required|exists:classes,id',
            'heure_debut' => 'required',
            'heure_fin' => 'required|after:heure_debut',
            'motif' => 'nullable|string|max:1000',
        ], [
            'absence.accepted' => 'Cochez « Absence » pour enregistrer.',
            'classe_id.required' => 'Choisissez la classe.',
            'heure_debut.required' => 'Indiquez l\'heure de début.',
            'heure_fin.required' => 'Indiquez l\'heure de fin.',
            'heure_fin.after' => 'L\'heure de fin doit être après l\'heure de début.',
        ]);

        $date = now()->toDateString();
        $jour = $this->jourSemaineAujourdhui();

        $dansEdt = $jour && EmploiTemps::actif()
            ->pourAnneeActive()
            ->jour($jour)
            ->where('enseignant_id', $request->enseignant_id)
            ->where('classe_id', $request->classe_id)
            ->exists();

        if (!$dansEdt) {
            return redirect()->route('absences-enseignants.index')
                ->with('error', 'Cette classe n\'est pas dans l\'emploi du temps de l\'enseignant aujourd\'hui.');
        }

        $heureDebut = substr((string) $request->heure_debut, 0, 5);
        $heureFin = substr((string) $request->heure_fin, 0, 5);

        $existe = AbsenceEnseignant::quiChevaucheCreneau(
            (int) $request->enseignant_id,
            $date,
            $heureDebut,
            $heureFin
        )->exists();

        if ($existe) {
            return redirect()->route('absences-enseignants.index')
                ->with('error', 'Un créneau d\'absence se chevauche déjà pour cet enseignant.');
        }

        AbsenceEnseignant::create([
            'enseignant_id' => $request->enseignant_id,
            'classe_id' => $request->classe_id,
            'date_debut' => $date,
            'date_fin' => $date,
            'heure_debut' => $heureDebut,
            'heure_fin' => $heureFin,
            'type' => 'absence',
            'motif' => $request->motif,
            'origine' => 'admin',
            'statut' => 'non_justifiee',
            'saisi_par' => auth()->id(),
        ]);

        return redirect()->route('absences-enseignants.index')
            ->with('success', 'Absence enregistrée pour ce créneau.');
    }

    public function storeJournee(Request $request)
    {
        if (!auth()->user()->hasPermission('absences-enseignants.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à enregistrer une absence enseignant.');
        }

        $request->validate([
            'enseignant_id' => 'required|exists:enseignants,id',
            'motif' => 'nullable|string|max:1000',
        ]);

        $cours = $this->coursAujourdhui((int) $request->enseignant_id);

        if ($cours->isEmpty()) {
            return redirect()->route('absences-enseignants.index')
                ->with('error', 'Aucun cours prévu aujourd\'hui pour cet enseignant.');
        }

        $date = now()->toDateString();
        $crees = 0;

        foreach ($cours as $creneau) {
            $heureDebut = $this->formatHeureCourt($creneau->heure_debut);
            $heureFin = $this->formatHeureCourt($creneau->heure_fin);

            if (!$heureDebut || !$heureFin) {
                continue;
            }

            $existe = AbsenceEnseignant::quiChevaucheCreneau(
                (int) $request->enseignant_id,
                $date,
                $heureDebut,
                $heureFin
            )->exists();

            if ($existe) {
                continue;
            }

            AbsenceEnseignant::create([
                'enseignant_id' => $request->enseignant_id,
                'classe_id' => $creneau->classe_id,
                'date_debut' => $date,
                'date_fin' => $date,
                'heure_debut' => $heureDebut,
                'heure_fin' => $heureFin,
                'type' => 'absence',
                'motif' => $request->motif,
                'origine' => 'admin',
                'statut' => 'non_justifiee',
                'saisi_par' => auth()->id(),
            ]);

            $crees++;
        }

        if ($crees === 0) {
            return redirect()->route('absences-enseignants.index')
                ->with('error', 'Tous les créneaux du jour sont déjà marqués absents.');
        }

        return redirect()->route('absences-enseignants.index')
            ->with('success', 'Absence enregistrée pour toute la journée (' . $crees . ' créneau' . ($crees > 1 ? 'x' : '') . ').');
    }

    public function show(AbsenceEnseignant $absenceEnseignant)
    {
        if (!auth()->user()->hasPermission('absences-enseignants.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à voir cette absence.');
        }

        $absenceEnseignant->load(['enseignant.utilisateur', 'classe', 'saisiPar', 'traitePar']);

        return view('absences-enseignants.show', compact('absenceEnseignant'));
    }

    public function ficheEnseignant(Enseignant $enseignant)
    {
        if (!auth()->user()->hasPermission('absences-enseignants.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à voir les absences des enseignants.');
        }

        $enseignant->load('utilisateur');

        $absences = AbsenceEnseignant::with(['saisiPar', 'traitePar', 'classe'])
            ->where('enseignant_id', $enseignant->id)
            ->orderByDesc('date_debut')
            ->paginate(20);

        return view('absences-enseignants.fiche', compact('enseignant', 'absences'));
    }

    public function justifier(Request $request, AbsenceEnseignant $absenceEnseignant)
    {
        if (!auth()->user()->hasPermission('absences-enseignants.edit')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à justifier cette absence.');
        }

        if (!$absenceEnseignant->peutEtreJustifiee()) {
            return redirect()->back()->with('error', 'Cette absence ne peut plus être justifiée.');
        }

        $request->validate([
            'motif' => 'required|string|max:1000',
            'motif_categorie' => 'nullable|in:maladie,personnel,mission,autre',
            'document_justificatif' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $data = [
            'statut' => 'justifiee',
            'motif' => $request->motif,
            'motif_categorie' => $request->motif_categorie ?: $absenceEnseignant->motif_categorie,
            'traite_par' => auth()->id(),
            'traite_at' => now(),
        ];

        if ($request->hasFile('document_justificatif')) {
            $this->supprimerDocument($absenceEnseignant);
            $data['document_justificatif'] = $request->file('document_justificatif')
                ->store('justificatifs-enseignants', 'public');
        }

        $absenceEnseignant->update($data);

        return redirect()->back()->with('success', 'Absence justifiée avec succès.');
    }

    public function valider(Request $request, AbsenceEnseignant $absenceEnseignant)
    {
        if (!auth()->user()->hasPermission('absences-enseignants.edit')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à valider cette déclaration.');
        }

        if (!$absenceEnseignant->peutEtreTraitee()) {
            return redirect()->back()->with('error', 'Cette déclaration ne peut plus être validée.');
        }

        $request->validate([
            'commentaire_admin' => 'nullable|string|max:1000',
        ]);

        $absenceEnseignant->update([
            'statut' => 'justifiee',
            'commentaire_admin' => $request->commentaire_admin,
            'traite_par' => auth()->id(),
            'traite_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Déclaration validée.');
    }

    public function refuser(Request $request, AbsenceEnseignant $absenceEnseignant)
    {
        if (!auth()->user()->hasPermission('absences-enseignants.edit')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à refuser cette déclaration.');
        }

        if (!$absenceEnseignant->peutEtreTraitee()) {
            return redirect()->back()->with('error', 'Cette déclaration ne peut plus être refusée.');
        }

        $request->validate([
            'commentaire_admin' => 'required|string|max:1000',
        ]);

        $absenceEnseignant->update([
            'statut' => 'refusee',
            'commentaire_admin' => $request->commentaire_admin,
            'traite_par' => auth()->id(),
            'traite_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Déclaration refusée.');
    }

    public function destroy(AbsenceEnseignant $absenceEnseignant)
    {
        if (!auth()->user()->hasPermission('absences-enseignants.delete')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à supprimer cette absence.');
        }

        $this->supprimerDocument($absenceEnseignant);
        $absenceEnseignant->delete();

        return redirect()->route('absences-enseignants.index')
            ->with('success', 'Absence supprimée.');
    }

    public function mesAbsences()
    {
        $enseignant = $this->enseignantConnecte();

        $absences = AbsenceEnseignant::with(['saisiPar', 'traitePar', 'classe'])
            ->where('enseignant_id', $enseignant->id)
            ->orderByDesc('date_debut')
            ->paginate(15);

        $compteurs = [
            'en_attente' => AbsenceEnseignant::where('enseignant_id', $enseignant->id)->where('statut', 'en_attente')->count(),
            'justifiees' => AbsenceEnseignant::where('enseignant_id', $enseignant->id)->where('statut', 'justifiee')->count(),
            'non_justifiees' => AbsenceEnseignant::where('enseignant_id', $enseignant->id)->where('statut', 'non_justifiee')->count(),
        ];

        return view('teacher.mes-absences', compact('absences', 'compteurs'));
    }

    public function createDeclaration()
    {
        $this->enseignantConnecte();

        return view('teacher.declarer-absence');
    }

    public function storeDeclaration(Request $request)
    {
        $enseignant = $this->enseignantConnecte();

        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'heure_debut' => 'nullable',
            'heure_fin' => 'nullable',
            'type' => 'required|in:absence,retard,conge',
            'motif_categorie' => 'required|in:maladie,personnel,mission,autre',
            'motif' => 'required|string|max:1000',
            'document_justificatif' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $chevauche = AbsenceEnseignant::quiChevauche(
            $enseignant->id,
            $request->date_debut,
            $request->date_fin
        )->exists();

        if ($chevauche) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une absence existe déjà sur cette période.');
        }

        $documentPath = null;
        if ($request->hasFile('document_justificatif')) {
            $documentPath = $request->file('document_justificatif')
                ->store('justificatifs-enseignants', 'public');
        }

        AbsenceEnseignant::create([
            'enseignant_id' => $enseignant->id,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'heure_debut' => $request->filled('heure_debut') ? $request->heure_debut : null,
            'heure_fin' => $request->filled('heure_fin') ? $request->heure_fin : null,
            'type' => $request->type,
            'motif_categorie' => $request->motif_categorie,
            'motif' => $request->motif,
            'document_justificatif' => $documentPath,
            'origine' => 'enseignant',
            'statut' => 'en_attente',
            'saisi_par' => auth()->id(),
        ]);

        return redirect()->route('teacher.mes-absences')
            ->with('success', 'Votre déclaration a été envoyée. Elle sera examinée par l\'administration.');
    }

    public function annulerDeclaration(AbsenceEnseignant $absenceEnseignant)
    {
        $enseignant = $this->enseignantConnecte();

        if ((int) $absenceEnseignant->enseignant_id !== (int) $enseignant->id) {
            abort(403, 'Cette absence ne vous appartient pas.');
        }

        if (!$absenceEnseignant->peutEtreAnnulee()) {
            return redirect()->back()->with('error', 'Seule une déclaration encore en attente peut être annulée.');
        }

        $this->supprimerDocument($absenceEnseignant);
        $absenceEnseignant->delete();

        return redirect()->route('teacher.mes-absences')
            ->with('success', 'Déclaration annulée.');
    }

    private function periodeAnneeScolaire(AnneeScolaire $annee): array
    {
        $officiel = $annee->date_debut->copy()->startOfDay();
        $septembre = Carbon::create($officiel->year, 9, 1)->startOfDay();
        $debut = $septembre->lt($officiel) ? $septembre : $officiel;
        $fin = $annee->date_fin->copy()->startOfDay();

        return [$debut, $fin];
    }

    private function moisDisponibles(?AnneeScolaire $annee): array
    {
        if ($annee?->date_debut && $annee?->date_fin) {
            [$debut, $fin] = $this->periodeAnneeScolaire($annee);
            $debut = $debut->copy()->startOfMonth();
            $fin = $fin->copy()->startOfMonth();
        } else {
            $fin = now()->copy()->startOfMonth();
            $debut = $fin->copy()->subMonths(11);
        }

        if ($debut->gt($fin)) {
            [$debut, $fin] = [$fin, $debut];
        }

        $mois = [];
        $cursor = $debut->copy();
        $limite = 24;

        while ($cursor->lte($fin) && $limite-- > 0) {
            $mois[] = [
                'valeur' => $cursor->format('Y-m'),
                'label' => ucfirst($cursor->locale('fr')->translatedFormat('F Y')),
            ];
            $cursor->addMonth();
        }

        return $mois;
    }

    private function jourSemaineAujourdhui(): ?string
    {
        return [
            1 => 'lundi',
            2 => 'mardi',
            3 => 'mercredi',
            4 => 'jeudi',
            5 => 'vendredi',
            6 => 'samedi',
        ][now()->dayOfWeekIso] ?? null;
    }

    private function coursAujourdhui(int $enseignantId)
    {
        $jour = $this->jourSemaineAujourdhui();

        if (!$jour) {
            return collect();
        }

        return EmploiTemps::actif()
            ->pourAnneeActive()
            ->jour($jour)
            ->where('enseignant_id', $enseignantId)
            ->orderBy('heure_debut')
            ->get()
            ->unique(function ($cours) {
                return implode('|', [
                    (int) $cours->classe_id,
                    $this->formatHeureCourt($cours->heure_debut),
                    $this->formatHeureCourt($cours->heure_fin),
                ]);
            })
            ->values();
    }

    private function formatHeureCourt($value): string
    {
        if (!$value) {
            return '';
        }

        if (is_object($value) && method_exists($value, 'format')) {
            return $value->format('H:i');
        }

        $texte = (string) $value;
        if (preg_match('/(\d{2}:\d{2})/', $texte, $matches)) {
            return $matches[1];
        }

        return substr($texte, 0, 5);
    }

    private function enseignantConnecte(): Enseignant
    {
        $enseignant = auth()->user()?->enseignant;

        if (!$enseignant) {
            abort(403, 'Profil enseignant non trouvé.');
        }

        return $enseignant;
    }

    private function supprimerDocument(AbsenceEnseignant $absence): void
    {
        if ($absence->document_justificatif) {
            Storage::disk('public')->delete($absence->document_justificatif);
        }
    }
}
