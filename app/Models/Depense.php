<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Depense extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle',
        'montant',
        'date_depense',
        'type_depense',
        'statut',
        'description',
        'beneficiaire',
        'reference_facture',
        'mode_paiement',
        'reference_paiement',
        'approuve_par',
        'paye_par',
        'date_approbation',
        'date_paiement',
        'observations',
        'annee_scolaire_id',
        'salaire_enseignant_id',
        'bon_salaire_enseignant_id',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_depense' => 'date',
        'date_approbation' => 'date',
        'date_paiement' => 'date'
    ];

    /**
     * Relation avec l'utilisateur qui a approuvé
     */
    public function approuvePar()
    {
        return $this->belongsTo(Utilisateur::class, 'approuve_par');
    }

    /**
     * Relation avec l'utilisateur qui a payé
     */
    public function payePar()
    {
        return $this->belongsTo(Utilisateur::class, 'paye_par');
    }

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class, 'annee_scolaire_id');
    }

    public function salaireEnseignant()
    {
        return $this->belongsTo(SalaireEnseignant::class, 'salaire_enseignant_id');
    }

    public function bonSalaireEnseignant()
    {
        return $this->belongsTo(BonSalaireEnseignant::class, 'bon_salaire_enseignant_id');
    }

    public static function hasBonSalaireLinkColumn(): bool
    {
        static $hasColumn = null;

        if ($hasColumn === null) {
            $hasColumn = Schema::hasColumn('depenses', 'bon_salaire_enseignant_id');
        }

        return $hasColumn;
    }

    public static function hasSalaireEnseignantLinkColumn(): bool
    {
        static $hasColumn = null;

        if ($hasColumn === null) {
            $hasColumn = Schema::hasColumn('depenses', 'salaire_enseignant_id');
        }

        return $hasColumn;
    }

    /**
     * Dépenses visibles côté comptabilité (hors doublons salaires module).
     */
    public function scopeExcluantSalairesModule($query)
    {
        $hasSalaireLink = static::hasSalaireEnseignantLinkColumn();
        $hasBonLink = static::hasBonSalaireLinkColumn();

        if (!$hasSalaireLink && !$hasBonLink) {
            return $query->where('type_depense', '!=', 'salaire_enseignant');
        }

        return $query->where(function ($q) use ($hasSalaireLink, $hasBonLink) {
            $q->where('type_depense', '!=', 'salaire_enseignant');

            if ($hasSalaireLink) {
                $q->orWhereNull('salaire_enseignant_id');
            }

            if ($hasBonLink) {
                $q->orWhereNotNull('bon_salaire_enseignant_id');
            }
        });
    }

    /**
     * Scope pour les dépenses en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    /**
     * Scope pour les dépenses approuvées
     */
    public function scopeApprouvees($query)
    {
        return $query->where('statut', 'approuve');
    }

    /**
     * Scope pour les dépenses payées
     */
    public function scopePayees($query)
    {
        return $query->where('statut', 'paye');
    }

    /**
     * Scope pour les dépenses par type
     */
    public function scopeParType($query, $type)
    {
        return $query->where('type_depense', $type);
    }

    /**
     * Scope pour les dépenses par période
     */
    public function scopeParPeriode($query, $dateDebut, $dateFin)
    {
        return $query->whereBetween('date_depense', [$dateDebut, $dateFin]);
    }

    /**
     * Marquer comme approuvé
     */
    public function approuver($utilisateurId, $dateApprobation = null)
    {
        $this->update([
            'statut' => 'approuve',
            'approuve_par' => $utilisateurId,
            'date_approbation' => $dateApprobation ?? now()->toDateString()
        ]);
    }

    /**
     * Marquer comme payé
     */
    public function marquerCommePaye($utilisateurId, $modePaiement, $referencePaiement = null, $datePaiement = null)
    {
        $this->update([
            'statut' => 'paye',
            'paye_par' => $utilisateurId,
            'mode_paiement' => $modePaiement,
            'reference_paiement' => $referencePaiement,
            'date_paiement' => $datePaiement ?? now()->toDateString()
        ]);
    }

    /**
     * Annuler la dépense
     */
    public function annuler()
    {
        $this->update(['statut' => 'annule']);
    }

    /**
     * Accessor pour le libellé du type de dépense
     */
    public function getTypeDepenseLibelleAttribute()
    {
        $types = [
            'salaire_enseignant' => 'Salaire Enseignant',
            'salaire_personnel' => 'Salaire Personnel',
            'achat_materiel' => 'Achat Matériel',
            'maintenance' => 'Maintenance',
            'electricite' => 'Électricité',
            'eau' => 'Eau',
            'nourriture' => 'Nourriture',
            'transport' => 'Transport',
            'communication' => 'Communication',
            'formation' => 'Formation',
            'autre' => 'Autre'
        ];

        return $types[$this->type_depense] ?? $this->type_depense;
    }

    /**
     * Accessor pour le libellé du statut
     */
    public function getStatutLibelleAttribute()
    {
        $statuts = [
            'en_attente' => 'En Attente',
            'approuve' => 'Approuvé',
            'paye' => 'Payé',
            'annule' => 'Annulé'
        ];

        return $statuts[$this->statut] ?? $this->statut;
    }
}
