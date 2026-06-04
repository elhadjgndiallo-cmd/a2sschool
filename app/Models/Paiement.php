<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'frais_scolarite_id',
        'tranche_paiement_id',
        'montant_paye',
        'date_paiement',
        'mode_paiement',
        'reference_paiement',
        'numero_recu',
        'observations',
        'encaisse_par'
    ];

    protected $casts = [
        'montant_paye' => 'decimal:2',
        'date_paiement' => 'date'
    ];

    /**
     * Relation avec les frais de scolarité
     */
    public function fraisScolarite()
    {
        return $this->belongsTo(FraisScolarite::class);
    }

    /**
     * Relation avec l'utilisateur qui a encaissé
     */
    public function encaissePar()
    {
        return $this->belongsTo(Utilisateur::class, 'encaisse_par');
    }

    /**
     * Relation avec la tranche de paiement
     */
    public function tranchePaiement()
    {
        return $this->belongsTo(TranchePaiement::class);
    }

    /**
     * Paiements des élèves d'une année scolaire (jointure, plus efficace que whereHas).
     */
    public function scopeForAnneeScolaire(Builder $query, int $anneeScolaireId): Builder
    {
        return $query
            ->join('frais_scolarite as fs_annee', 'paiements.frais_scolarite_id', '=', 'fs_annee.id')
            ->join('eleves as el_annee', 'fs_annee.eleve_id', '=', 'el_annee.id')
            ->where('el_annee.annee_scolaire_id', $anneeScolaireId)
            ->select('paiements.*');
    }

    /**
     * Relations minimales pour l'affichage comptabilité (entrées, journal).
     */
    public function scopeWithComptabiliteAffichage(Builder $query): Builder
    {
        return $query->with([
            'fraisScolarite:id,eleve_id,type_frais',
            'fraisScolarite.eleve:id,utilisateur_id,classe_id,numero_etudiant',
            'fraisScolarite.eleve.utilisateur:id,nom,prenom',
            'fraisScolarite.eleve.classe:id,nom',
            'encaissePar:id,nom,prenom',
        ]);
    }

    /**
     * Boot method pour générer automatiquement le numéro de reçu
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($paiement) {
            if (!$paiement->numero_recu) {
                $paiement->numero_recu = static::genererNumeroRecu();
            }
        });
    }

    /**
     * Générer un numéro de reçu unique
     */
    public static function genererNumeroRecu()
    {
        $annee = date('Y');
        $prefixe = "REC-{$annee}-";
        
        // Trouver le dernier numéro de reçu pour cette année
        $dernierRecu = static::where('numero_recu', 'like', $prefixe . '%')
            ->orderBy('numero_recu', 'desc')
            ->first();
        
        if ($dernierRecu) {
            // Extraire le numéro du dernier reçu
            $dernierNumero = (int) substr($dernierRecu->numero_recu, strlen($prefixe));
            $nouveauNumero = $dernierNumero + 1;
        } else {
            $nouveauNumero = 1;
        }
        
        return $prefixe . str_pad($nouveauNumero, 6, '0', STR_PAD_LEFT);
    }
}
