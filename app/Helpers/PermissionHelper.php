<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class PermissionHelper
{
    /**
     * Vérifier si l'utilisateur connecté a une permission
     */
    public static function hasPermission($permission)
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        return $user->hasPermission($permission);
    }

    /**
     * Vérifier si l'utilisateur connecté est admin
     */
    public static function isAdmin()
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        return $user->isAdmin();
    }

    /**
     * Vérifier si l'utilisateur connecté peut accéder à l'administration
     */
    public static function canAccessAdmin()
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        return $user->canAccessAdmin();
    }

    /**
     * Obtenir les sous-menus filtrés par permissions
     */
    public static function getFilteredSubmenus($menuType)
    {
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }

        // Fonction helper pour obtenir une route de manière sécurisée
        $getRoute = function($routeName, $default = '#') {
            try {
                return Route::has($routeName) ? route($routeName) : $default;
            } catch (\Exception $e) {
                return $default;
            }
        };

        $allSubmenus = [
            'enseignants' => [
                ['href' => $getRoute('enseignants.create'), 'icon' => 'fas fa-user-plus', 'text' => 'Inscription', 'permission' => 'enseignants.create'],
                ['href' => $getRoute('enseignants.index'), 'icon' => 'fas fa-list', 'text' => 'Liste', 'permission' => 'enseignants.view'],
                ['href' => $getRoute('salaires.index'), 'icon' => 'fas fa-coins', 'text' => 'Salaire', 'permission' => 'salaires.view'],
                ['href' => $getRoute('cartes-enseignants.index'), 'icon' => 'fas fa-id-badge', 'text' => 'Cartes Enseignants', 'permission' => 'cartes-enseignants.view']
            ],
            'eleves' => [
                ['href' => $getRoute('eleves.index'), 'icon' => 'fas fa-list', 'text' => 'Liste', 'permission' => 'eleves.view'],
                ['href' => $getRoute('eleves.create'), 'icon' => 'fas fa-user-plus', 'text' => 'Inscription', 'permission' => 'eleves.create'],
                ['href' => $getRoute('eleves.reinscription'), 'icon' => 'fas fa-user-edit', 'text' => 'Réinscription', 'permission' => 'eleves.edit'],
                ['href' => $getRoute('parents.index'), 'icon' => 'fas fa-users', 'text' => 'Parents', 'permission' => 'eleves.view'],
                ['href' => $getRoute('paiements.index'), 'icon' => 'fas fa-money-bill-wave', 'text' => 'Frais', 'permission' => 'paiements.view'],
                ['href' => $getRoute('cartes-scolaires.index'), 'icon' => 'fas fa-id-card', 'text' => 'Cartes scolaires', 'permission' => 'cartes-scolaires.view']
            ],
        'notes' => [
            ['href' => $getRoute('notes.index'), 'icon' => 'fas fa-edit', 'text' => 'Saisir Notes', 'permission' => 'notes.view'],
            ['href' => $getRoute('notes.bulletins'), 'icon' => 'fas fa-chart-line', 'text' => 'Bulletins', 'permission' => 'notes.view'],
            ['href' => $getRoute('notes.statistiques'), 'icon' => 'fas fa-chart-bar', 'text' => 'Statistiques', 'permission' => 'notes.view'],
            ['href' => $getRoute('notes.mensuel.index'), 'icon' => 'fas fa-calendar-alt', 'text' => 'Mensuel', 'permission' => 'notes.view'],
            ['href' => $getRoute('notes.fiche.selection'), 'icon' => 'fas fa-file-alt', 'text' => 'Fiche Note', 'permission' => 'notes.view'],
            ['href' => $getRoute('notes.parametres'), 'icon' => 'fas fa-cog', 'text' => 'Paramètres', 'permission' => 'notes.edit']
        ],
            'comptabilite' => [
                ['href' => $getRoute('entrees.index'), 'icon' => 'fas fa-arrow-up', 'text' => 'Entrée', 'permission' => 'entrees.view'],
                ['href' => $getRoute('depenses.index'), 'icon' => 'fas fa-arrow-down', 'text' => 'Sortie', 'permission' => 'depenses.view']
            ],
            'rapports' => [
                ['href' => $getRoute('rapports.unifies'), 'icon' => 'fas fa-chart-line', 'text' => 'Tous les Rapports', 'permission' => 'rapports.view']
            ],
            'cartes' => [
                ['href' => $getRoute('cartes-scolaires.index'), 'icon' => 'fas fa-id-card', 'text' => 'Cartes Scolaires', 'permission' => 'cartes-scolaires.view']
            ],
            'parametres' => [
                ['href' => $getRoute('etablissement.informations'), 'icon' => 'fas fa-info-circle', 'text' => 'Informations', 'permission' => 'etablissement.view'],
                ['href' => $getRoute('etablissement.responsabilites'), 'icon' => 'fas fa-users-cog', 'text' => 'Responsabilités', 'permission' => 'etablissement.view'],
                ['href' => $getRoute('annees-scolaires.index'), 'icon' => 'fas fa-calendar-alt', 'text' => 'Année Scolaire', 'permission' => 'annees_scolaires.view'],
                ['href' => $getRoute('classes.index'), 'icon' => 'fas fa-chalkboard-teacher', 'text' => 'Classes', 'permission' => 'classes.view'],
                ['href' => $getRoute('tarifs.tableau'), 'icon' => 'fas fa-chart-line', 'text' => 'Tableau des Tarifs', 'permission' => 'tarifs.view'],
                ['href' => $getRoute('evenements.index'), 'icon' => 'fas fa-calendar-check', 'text' => 'Événements', 'permission' => 'evenements.view'],
                ['href' => $getRoute('evenements.create'), 'icon' => 'fas fa-plus', 'text' => 'Créer Événement', 'permission' => 'evenements.create'],
                ['href' => $getRoute('admin.accounts.index'), 'icon' => 'fas fa-user-shield', 'text' => 'Comptes Administrateurs', 'permission' => 'admin.accounts.view']
            ]
        ];

        if (!isset($allSubmenus[$menuType])) {
            return [];
        }

        $filteredSubmenus = [];
        
        foreach ($allSubmenus[$menuType] as $submenu) {
            // Si pas de permission requise ou si l'utilisateur a la permission ou est admin
            if (!$submenu['permission'] || $user->hasPermission($submenu['permission']) || $user->isAdmin()) {
                $filteredSubmenus[] = $submenu;
            }
        }

        return $filteredSubmenus;
    }
}
