<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Matiere;

/**
 * Service d'optimisation des requêtes fréquentes
 * Cache les données qui ne changent pas souvent pour améliorer les performances
 */
class QueryOptimizationService
{
    /**
     * Durée du cache en secondes (10 minutes par défaut)
     */
    const CACHE_DURATION = 600;
    
    /**
     * Durée du cache courte (2 minutes)
     */
    const SHORT_CACHE_DURATION = 120;
    
    /**
     * Durée du cache longue (30 minutes)
     */
    const LONG_CACHE_DURATION = 1800;

    /**
     * Récupérer l'année scolaire active (avec cache)
     */
    public function getAnneeScolaireActive()
    {
        return Cache::remember('annee_scolaire_active', self::LONG_CACHE_DURATION, function () {
            return AnneeScolaire::where('active', true)->first();
        });
    }

    /**
     * Récupérer toutes les classes actives (avec cache)
     */
    public function getClassesActives()
    {
        return Cache::remember('classes_actives', self::CACHE_DURATION, function () {
            return Classe::where('actif', true)
                ->orderBy('nom', 'asc')
                ->get();
        });
    }

    /**
     * Récupérer toutes les matières actives (avec cache)
     */
    public function getMatieresActives()
    {
        return Cache::remember('matieres_actives', self::CACHE_DURATION, function () {
            return Matiere::where('actif', true)
                ->orderBy('nom', 'asc')
                ->get();
        });
    }

    /**
     * Récupérer les statistiques d'une classe (avec cache)
     */
    public function getClasseStats($classeId, $anneeScolaireId)
    {
        $cacheKey = "classe_stats_{$classeId}_{$anneeScolaireId}";
        
        return Cache::remember($cacheKey, self::SHORT_CACHE_DURATION, function () use ($classeId, $anneeScolaireId) {
            $classe = Classe::findOrFail($classeId);
            
            $stats = [
                'total_eleves' => $classe->eleves()
                    ->where('annee_scolaire_id', $anneeScolaireId)
                    ->where('actif', true)
                    ->count(),
                'total_garcons' => $classe->eleves()
                    ->where('annee_scolaire_id', $anneeScolaireId)
                    ->where('actif', true)
                    ->whereHas('utilisateur', function($q) {
                        $q->where('sexe', 'M');
                    })
                    ->count(),
                'total_filles' => $classe->eleves()
                    ->where('annee_scolaire_id', $anneeScolaireId)
                    ->where('actif', true)
                    ->whereHas('utilisateur', function($q) {
                        $q->where('sexe', 'F');
                    })
                    ->count(),
            ];
            
            return $stats;
        });
    }

    /**
     * Invalider le cache de l'année scolaire active
     */
    public function clearAnneeScolaireCache()
    {
        Cache::forget('annee_scolaire_active');
    }

    /**
     * Invalider le cache des classes
     */
    public function clearClassesCache()
    {
        Cache::forget('classes_actives');
    }

    /**
     * Invalider le cache des matières
     */
    public function clearMatieresCache()
    {
        Cache::forget('matieres_actives');
    }

    /**
     * Invalider le cache des statistiques d'une classe
     */
    public function clearClasseStatsCache($classeId, $anneeScolaireId)
    {
        Cache::forget("classe_stats_{$classeId}_{$anneeScolaireId}");
    }

    /**
     * Invalider tout le cache
     */
    public function clearAllCache()
    {
        Cache::flush();
    }

    /**
     * Pré-charger les données fréquemment utilisées
     */
    public function warmupCache()
    {
        // Charger l'année scolaire active
        $this->getAnneeScolaireActive();
        
        // Charger les classes actives
        $this->getClassesActives();
        
        // Charger les matières actives
        $this->getMatieresActives();
    }
}
