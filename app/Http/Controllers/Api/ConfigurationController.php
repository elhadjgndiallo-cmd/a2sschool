<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ConfigurationController extends Controller
{
    /**
     * Récupérer toutes les configurations du système
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Récupérer les configurations depuis le cache ou le stockage
        $configurations = $this->getConfigurations();
        
        return response()->json([
            'status' => 'success',
            'data' => $configurations,
            'message' => 'Configurations récupérées avec succès'
        ]);
    }

    /**
     * Mettre à jour une configuration spécifique
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cle' => 'required|string',
            'valeur' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation des données'
            ], 422);
        }

        try {
            // Récupérer les configurations existantes
            $configurations = $this->getConfigurations();
            
            // Mettre à jour la configuration spécifiée
            $configurations[$request->cle] = $request->valeur;
            
            // Sauvegarder les configurations
            $this->saveConfigurations($configurations);
            
            return response()->json([
                'status' => 'success',
                'data' => $configurations,
                'message' => 'Configuration mise à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour de la configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour plusieurs configurations à la fois
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'configurations' => 'required|array',
            'configurations.*.cle' => 'required|string',
            'configurations.*.valeur' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation des données'
            ], 422);
        }

        try {
            // Récupérer les configurations existantes
            $configurations = $this->getConfigurations();
            
            // Mettre à jour les configurations spécifiées
            foreach ($request->configurations as $config) {
                $configurations[$config['cle']] = $config['valeur'];
            }
            
            // Sauvegarder les configurations
            $this->saveConfigurations($configurations);
            
            return response()->json([
                'status' => 'success',
                'data' => $configurations,
                'message' => 'Configurations mises à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour des configurations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour le logo de l'école
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateLogo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation des données'
            ], 422);
        }

        try {
            // Récupérer les configurations existantes
            $configurations = $this->getConfigurations();
            
            // Supprimer l'ancien logo s'il existe
            if (isset($configurations['logo_path']) && Storage::disk('public')->exists($configurations['logo_path'])) {
                Storage::disk('public')->delete($configurations['logo_path']);
            }
            
            // Stocker le nouveau logo
            $logoPath = $request->file('logo')->store('logos', 'public');
            
            // Mettre à jour la configuration du logo
            $configurations['logo_path'] = $logoPath;
            $configurations['logo_url'] = asset('storage/' . $logoPath);
            
            // Sauvegarder les configurations
            $this->saveConfigurations($configurations);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'logo_path' => $configurations['logo_path'],
                    'logo_url' => $configurations['logo_url']
                ],
                'message' => 'Logo mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du logo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Réinitialiser les configurations par défaut
     *
     * @return \Illuminate\Http\Response
     */
    public function resetDefaults()
    {
        try {
            // Définir les configurations par défaut
            $defaultConfigurations = $this->getDefaultConfigurations();
            
            // Sauvegarder les configurations par défaut
            $this->saveConfigurations($defaultConfigurations);
            
            return response()->json([
                'status' => 'success',
                'data' => $defaultConfigurations,
                'message' => 'Configurations réinitialisées avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la réinitialisation des configurations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les configurations depuis le cache ou le stockage
     *
     * @return array
     */
    private function getConfigurations()
    {
        // Essayer de récupérer les configurations depuis le cache
        if (Cache::has('system_configurations')) {
            return Cache::get('system_configurations');
        }
        
        // Si pas dans le cache, essayer de récupérer depuis le stockage
        if (Storage::disk('local')->exists('configurations.json')) {
            $configurations = json_decode(Storage::disk('local')->get('configurations.json'), true);
            
            // Mettre en cache pour les prochaines requêtes
            Cache::put('system_configurations', $configurations, now()->addDay());
            
            return $configurations;
        }
        
        // Si aucune configuration n'existe, retourner les configurations par défaut
        $defaultConfigurations = $this->getDefaultConfigurations();
        
        // Sauvegarder les configurations par défaut
        $this->saveConfigurations($defaultConfigurations);
        
        return $defaultConfigurations;
    }

    /**
     * Sauvegarder les configurations dans le stockage et le cache
     *
     * @param  array  $configurations
     * @return void
     */
    private function saveConfigurations($configurations)
    {
        // Sauvegarder dans le stockage
        Storage::disk('local')->put('configurations.json', json_encode($configurations, JSON_PRETTY_PRINT));
        
        // Mettre à jour le cache
        Cache::put('system_configurations', $configurations, now()->addDay());
    }

    /**
     * Obtenir les configurations par défaut du système
     *
     * @return array
     */
    private function getDefaultConfigurations()
    {
        return [
            'nom_ecole' => 'Groupe Scolaire Haïti Futur Développement',
            'slogan' => 'Éduquer pour l\'avenir',
            'adresse' => 'Port-au-Prince, Haïti',
            'telephone' => '+509 0000 0000',
            'email' => 'contact@gshfd.edu.ht',
            'site_web' => 'www.gshfd.edu.ht',
            'annee_scolaire' => date('Y') . '-' . (date('Y') + 1),
            'date_debut_annee' => date('Y') . '-09-01',
            'date_fin_annee' => (date('Y') + 1) . '-06-30',
            'devise' => 'HTG',
            'format_date' => 'd/m/Y',
            'format_heure' => 'H:i',
            'fuseau_horaire' => 'America/Port-au-Prince',
            'langue' => 'fr',
            'theme' => 'default',
            'couleur_primaire' => '#3490dc',
            'couleur_secondaire' => '#38c172',
            'logo_path' => null,
            'logo_url' => null,
            'sms_notification' => false,
            'email_notification' => true,
            'systeme_notation' => [
                'excellent' => 90,
                'tres_bien' => 80,
                'bien' => 70,
                'assez_bien' => 60,
                'passable' => 50,
                'insuffisant' => 0
            ],
            'jours_ecole' => ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'],
            'heures_cours' => [
                'debut' => '08:00',
                'fin' => '16:00'
            ],
            'duree_periode' => 50, // en minutes
            'pause_entre_periodes' => 10, // en minutes
            'nombre_periodes_jour' => 8,
            'seuil_absence_alerte' => 3, // nombre d'absences avant alerte
            'pourcentage_presence_minimum' => 80, // pourcentage minimum de présence requis
            'delai_paiement' => 10, // jours de délai pour les paiements
            'frais_retard_paiement' => 5, // pourcentage de frais pour retard de paiement
            'maintenance_mode' => false,
            'version' => '1.0.0',
        ];
    }
}