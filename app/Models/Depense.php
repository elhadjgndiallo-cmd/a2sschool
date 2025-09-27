<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'observations'
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
