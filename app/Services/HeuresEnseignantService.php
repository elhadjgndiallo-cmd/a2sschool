<?php

namespace App\Services;

use App\Models\AbsenceEnseignant;
use App\Models\EmploiTemps;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HeuresEnseignantService
{
    public function recapitulatif(int $enseignantId, Carbon $debut, Carbon $fin): array
    {
        $prevues = $this->heuresPrevuesParEnseignant([$enseignantId], $debut, $fin)[$enseignantId] ?? 0;
        $absences = $this->heuresAbsencesParEnseignant([$enseignantId], $debut, $fin)[$enseignantId] ?? 0;
        $restantes = max(0, $prevues - $absences);

        return [
            'minutes_prevues' => $prevues,
            'minutes_absences' => $absences,
            'minutes_restantes' => $restantes,
            'heures_prevues' => AbsenceEnseignant::formatDureeMinutes($prevues),
            'heures_absences' => AbsenceEnseignant::formatDureeMinutes($absences),
            'heures_restantes' => AbsenceEnseignant::formatDureeMinutes($restantes),
            'nombre_heures' => (int) round($restantes / 60),
        ];
    }

    public function heuresPrevuesParEnseignant(array $enseignantIds, Carbon $debut, Carbon $fin): array
    {
        $totaux = [];
        foreach ($enseignantIds as $id) {
            $totaux[(int) $id] = 0;
        }

        if (empty($enseignantIds)) {
            return $totaux;
        }

        $jours = [
            1 => 'lundi',
            2 => 'mardi',
            3 => 'mercredi',
            4 => 'jeudi',
            5 => 'vendredi',
            6 => 'samedi',
        ];

        $occurrences = [
            'lundi' => 0,
            'mardi' => 0,
            'mercredi' => 0,
            'jeudi' => 0,
            'vendredi' => 0,
            'samedi' => 0,
        ];

        for ($jour = $debut->copy()->startOfDay(); $jour->lte($fin->copy()->startOfDay()); $jour->addDay()) {
            $nomJour = $jours[$jour->dayOfWeekIso] ?? null;
            if ($nomJour) {
                $occurrences[$nomJour]++;
            }
        }

        $emplois = EmploiTemps::actif()
            ->pourAnneeActive()
            ->whereIn('enseignant_id', $enseignantIds)
            ->get()
            ->unique(function ($cours) {
                return implode('|', [
                    (int) $cours->enseignant_id,
                    strtolower((string) $cours->jour_semaine),
                    $this->formatHeureCourt($cours->heure_debut),
                    $this->formatHeureCourt($cours->heure_fin),
                    (int) $cours->classe_id,
                ]);
            });

        foreach ($emplois as $cours) {
            $enseignantId = (int) $cours->enseignant_id;
            if (!array_key_exists($enseignantId, $totaux)) {
                $totaux[$enseignantId] = 0;
            }

            $nomJour = strtolower((string) $cours->jour_semaine);
            $fois = $occurrences[$nomJour] ?? 0;
            $totaux[$enseignantId] += $cours->dureeMinutes() * $fois;
        }

        return $totaux;
    }

    public function heuresAbsencesParEnseignant(array $enseignantIds, Carbon $debut, Carbon $fin): array
    {
        $totaux = [];
        foreach ($enseignantIds as $id) {
            $totaux[(int) $id] = 0;
        }

        if (empty($enseignantIds)) {
            return $totaux;
        }

        AbsenceEnseignant::query()
            ->duMois($debut->toDateString(), $fin->toDateString())
            ->whereIn('enseignant_id', $enseignantIds)
            ->get()
            ->each(function ($absence) use (&$totaux) {
                $enseignantId = (int) $absence->enseignant_id;
                if (!array_key_exists($enseignantId, $totaux)) {
                    $totaux[$enseignantId] = 0;
                }
                $totaux[$enseignantId] += $absence->dureeMinutes();
            });

        return $totaux;
    }

    public function heuresPrevuesPourCollection(Collection $enseignants, Carbon $debut, Carbon $fin): array
    {
        return $this->heuresPrevuesParEnseignant($enseignants->pluck('id')->all(), $debut, $fin);
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
}
