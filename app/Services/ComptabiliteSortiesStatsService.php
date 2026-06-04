<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\Depense;
use App\Models\SalaireEnseignant;
use Illuminate\Http\Request;

class ComptabiliteSortiesStatsService
{
    /**
     * Total des sorties : dépenses (hors doublons salaires) + salaires enseignants payés.
     */
    public function calculateStats(?Request $request = null, ?AnneeScolaire $anneeScolaire = null): array
    {
        $request = $request ?? new Request();

        $query = Depense::query();

        if ($anneeScolaire) {
            $query->whereBetween('date_depense', [
                $anneeScolaire->date_debut->format('Y-m-d'),
                $anneeScolaire->date_fin->format('Y-m-d'),
            ]);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_depense', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_depense', '<=', $request->date_fin);
        }

        if ($request->filled('type_depense')) {
            $query->where('type_depense', $request->type_depense);
        }

        if (!$request->filled('type_depense') || $request->type_depense !== 'salaire_enseignant') {
            $query->where('type_depense', '!=', 'salaire_enseignant');
        }

        $totalDepenses = (float) $query->sum('montant');
        $nombreDepenses = $query->count();

        $totalSalaires = 0.0;
        $nombreSalaires = 0;

        if (!$request->filled('type_depense') || $request->type_depense === 'salaire_enseignant') {
            $salairesQuery = SalaireEnseignant::where('statut', 'payé')
                ->whereNotNull('date_paiement');

            if ($anneeScolaire) {
                $salairesQuery->whereBetween('date_paiement', [
                    $anneeScolaire->date_debut->format('Y-m-d'),
                    $anneeScolaire->date_fin->format('Y-m-d'),
                ]);
            }

            if ($request->filled('date_debut')) {
                $salairesQuery->whereDate('date_paiement', '>=', $request->date_debut);
            }

            if ($request->filled('date_fin')) {
                $salairesQuery->whereDate('date_paiement', '<=', $request->date_fin);
            }

            $totalSalaires = (float) $salairesQuery->sum('salaire_net');
            $nombreSalaires = $salairesQuery->count();
        }

        $total = $totalDepenses + $totalSalaires;
        $nombre = $nombreDepenses + $nombreSalaires;

        return [
            'total' => $total,
            'nombre' => $nombre,
            'moyenne' => $nombre > 0 ? $total / $nombre : 0,
            'total_depenses' => $totalDepenses,
            'total_salaires' => $totalSalaires,
        ];
    }
}
