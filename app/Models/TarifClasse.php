<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarifClasse extends Model
{
    use HasFactory;

    protected $table = 'tarifs_classes';

    protected $fillable = [
        'classe_id',
        'annee_scolaire',
        'frais_inscription',
        'frais_reinscription',
        'frais_scolarite_mensuel',
        'frais_cantine_mensuel',
        'frais_transport_mensuel',
        'frais_uniforme',
        'frais_livres',
        'frais_autres',
        'paiement_par_tranches',
        'nombre_tranches',
        'periode_tranche',
        'actif',
        'description'
    ];

    protected $casts = [
        'frais_inscription' => 'decimal:2',
        'frais_reinscription' => 'decimal:2',
        'frais_scolarite_mensuel' => 'decimal:2',
        'frais_cantine_mensuel' => 'decimal:2',
        'frais_transport_mensuel' => 'decimal:2',
        'frais_uniforme' => 'decimal:2',
        'frais_livres' => 'decimal:2',
        'frais_autres' => 'decimal:2',
        'paiement_par_tranches' => 'boolean',
        'actif' => 'boolean'
    ];

    /**
     * Les attributs à ajouter automatiquement à la sérialisation
     */
    protected $appends = ['total_mensuel', 'total_annuel'];

    /**
     * Validation des attributs
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($tarif) {
            // Valider le nombre de tranches (mois)
            if ($tarif->nombre_tranches < 1 || $tarif->nombre_tranches > 9) {
                throw new \InvalidArgumentException('Le nombre de mois doit être entre 1 et 9.');
            }
        });
    }

    /**
     * Relation avec la classe
     */
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    /**
     * Calculer le total mensuel
     */
    public function getTotalMensuelAttribute()
    {
        return $this->frais_scolarite_mensuel + $this->frais_cantine_mensuel + $this->frais_transport_mensuel;
    }

    /**
     * Calculer le total annuel
     */
    public function getTotalAnnuelAttribute()
    {
        return $this->frais_inscription + 
               ($this->total_mensuel * $this->nombre_tranches) + 
               $this->frais_uniforme + 
               $this->frais_livres + 
               $this->frais_autres;
    }

    /**
     * Calculer le montant par tranche
     */
    public function getMontantTrancheAttribute()
    {
        if ($this->paiement_par_tranches) {
            return $this->total_mensuel;
        }
        return $this->total_annuel;
    }

    /**
     * Scope pour les tarifs actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope pour une année scolaire
     */
    public function scopeAnneeScolaire($query, $annee)
    {
        return $query->where('annee_scolaire', $annee);
    }

    /**
     * Scope pour une classe
     */
    public function scopeClasse($query, $classeId)
    {
        return $query->where('classe_id', $classeId);
    }

    /**
     * Créer automatiquement les frais de scolarité pour un élève
     */
    public function creerFraisScolarite($eleveId, $anneeScolaire = null)
    {
        $anneeScolaire = $anneeScolaire ?? $this->annee_scolaire;
        
        // Créer les frais de scolarité mensuels
        if ($this->frais_scolarite_mensuel > 0) {
            $fraisScolarite = FraisScolarite::create([
                'eleve_id' => $eleveId,
                'classe_id' => $this->classe_id,
                'libelle' => 'Frais de scolarité - ' . $this->classe->nom . ' - ' . $anneeScolaire,
                'montant' => $this->frais_scolarite_mensuel * $this->nombre_tranches,
                'type_frais' => 'scolarite',
                'annee_scolaire' => $anneeScolaire,
                'paiement_par_tranches' => $this->paiement_par_tranches,
                'nombre_tranches' => $this->nombre_tranches,
                'montant_tranche' => $this->frais_scolarite_mensuel,
                'periode_tranche' => $this->periode_tranche,
                'date_debut_tranches' => now()->startOfMonth(),
                'statut' => 'en_attente'
            ]);

            // Créer les tranches de paiement
            if ($this->paiement_par_tranches) {
                $fraisScolarite->creerTranchesPaiement();
            }
        }

        // Créer les frais de cantine
        if ($this->frais_cantine_mensuel > 0) {
            $fraisCantine = FraisScolarite::create([
                'eleve_id' => $eleveId,
                'classe_id' => $this->classe_id,
                'libelle' => 'Frais de cantine - ' . $this->classe->nom . ' - ' . $anneeScolaire,
                'montant' => $this->frais_cantine_mensuel * $this->nombre_tranches,
                'type_frais' => 'cantine',
                'annee_scolaire' => $anneeScolaire,
                'paiement_par_tranches' => $this->paiement_par_tranches,
                'nombre_tranches' => $this->nombre_tranches,
                'montant_tranche' => $this->frais_cantine_mensuel,
                'periode_tranche' => $this->periode_tranche,
                'date_debut_tranches' => now()->startOfMonth(),
                'statut' => 'en_attente'
            ]);

            if ($this->paiement_par_tranches) {
                $fraisCantine->creerTranchesPaiement();
            }
        }

        // Créer les frais de transport
        if ($this->frais_transport_mensuel > 0) {
            $fraisTransport = FraisScolarite::create([
                'eleve_id' => $eleveId,
                'classe_id' => $this->classe_id,
                'libelle' => 'Frais de transport - ' . $this->classe->nom . ' - ' . $anneeScolaire,
                'montant' => $this->frais_transport_mensuel * $this->nombre_tranches,
                'type_frais' => 'transport',
                'annee_scolaire' => $anneeScolaire,
                'paiement_par_tranches' => $this->paiement_par_tranches,
                'nombre_tranches' => $this->nombre_tranches,
                'montant_tranche' => $this->frais_transport_mensuel,
                'periode_tranche' => $this->periode_tranche,
                'date_debut_tranches' => now()->startOfMonth(),
                'statut' => 'en_attente'
            ]);

            if ($this->paiement_par_tranches) {
                $fraisTransport->creerTranchesPaiement();
            }
        }

        // Créer les frais uniques (inscription, uniforme, livres, autres)
        $fraisUniques = [
            'inscription' => $this->frais_inscription,
            'uniforme' => $this->frais_uniforme,
            'livres' => $this->frais_livres,
            'autres' => $this->frais_autres
        ];

        foreach ($fraisUniques as $type => $montant) {
            if ($montant > 0) {
                FraisScolarite::create([
                    'eleve_id' => $eleveId,
                    'classe_id' => $this->classe_id,
                    'libelle' => 'Frais ' . ucfirst($type) . ' - ' . $this->classe->nom . ' - ' . $anneeScolaire,
                    'montant' => $montant,
                    'type_frais' => $type,
                    'annee_scolaire' => $anneeScolaire,
                    'paiement_par_tranches' => false,
                    'statut' => 'en_attente'
                ]);
            }
        }
    }
}
