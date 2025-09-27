<?php

namespace App\Helpers;

use App\Models\Etablissement;
use App\Models\AnneeScolaire;
use Illuminate\Support\Facades\Cache;

class SchoolHelper
{
    /**
     * Obtenir les informations de l'établissement
     */
    public static function getSchoolInfo()
    {
        return Cache::remember('school_info', 3600, function () {
            return Etablissement::principal();
        });
    }

    /**
     * Obtenir l'année scolaire active
     */
    public static function getActiveSchoolYear()
    {
        return Cache::remember('active_school_year', 3600, function () {
            return AnneeScolaire::anneeActive();
        });
    }

    /**
     * Obtenir toutes les informations pour les documents
     */
    public static function getDocumentInfo()
    {
        $school = self::getSchoolInfo();
        $year = self::getActiveSchoolYear();

        return [
            'school' => $school,
            'year' => $year,
            'logo_url' => $school && $school->logo ? asset('storage/' . $school->logo) : null,
            'cachet_url' => $school && $school->cachet ? asset('storage/' . $school->cachet) : null,
            'school_name' => $school ? $school->nom : 'École',
            'school_address' => $school ? $school->adresse : '',
            'school_phone' => $school ? $school->telephone : '',
            'school_email' => $school ? $school->email : '',
            'school_slogan' => $school ? $school->slogan : '',
            'school_description' => $school ? $school->description : '',
            'dg' => $school ? $school->dg : '',
            'dg_name' => $school ? $school->dg : '',
            'directeur_primaire' => $school ? $school->directeur_primaire : '',
            'prefixe_matricule' => $school ? $school->prefixe_matricule : '',
            'suffixe_matricule' => $school ? $school->suffixe_matricule : '',
            'statut_etablissement' => $school ? $school->statut_etablissement : '',
            'year_name' => $year ? $year->nom : '',
            'year_period' => $year ? $year->date_debut->format('d/m/Y') . ' - ' . $year->date_fin->format('d/m/Y') : '',
        ];
    }

    /**
     * Vider le cache des informations de l'école
     */
    public static function clearCache()
    {
        Cache::forget('school_info');
        Cache::forget('active_school_year');
    }

    /**
     * Générer le prochain matricule
     */
    public static function generateMatricule()
    {
        $school = self::getSchoolInfo();
        
        if (!$school) {
            return date('Y') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        }
        
        $prefixe = $school->prefixe_matricule ?: date('Y');
        $suffixe = $school->suffixe_matricule ?: ''; // Pas de suffixe par défaut
        
        // Compter les élèves existants pour générer un numéro séquentiel
        $count = \App\Models\Eleve::count() + 1;
        
        // Générer un matricule unique
        do {
            $numero = str_pad($count, 3, '0', STR_PAD_LEFT);
            $matricule = $prefixe . $numero . $suffixe;
            $count++;
        } while (\App\Models\Eleve::where('numero_etudiant', $matricule)->exists());
        
        return $matricule;
    }

    /**
     * Obtenir l'en-tête pour les documents
     */
    public static function getDocumentHeader()
    {
        $info = self::getDocumentInfo();
        
        return [
            'title' => $info['school_name'],
            'subtitle' => $info['school_slogan'],
            'address' => $info['school_address'],
            'contact' => trim(($info['school_phone'] ? 'Tél: ' . $info['school_phone'] : '') . 
                         ($info['school_phone'] && $info['school_email'] ? ' | ' : '') . 
                         ($info['school_email'] ? 'Email: ' . $info['school_email'] : '')),
            'logo' => $info['logo_url'],
            'cachet' => $info['cachet_url'],
            'year' => $info['year_name'],
            'period' => $info['year_period'],
        ];
    }
}
