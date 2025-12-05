<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

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

        $allSubmenus = [
            'enseignants' => [
                ['href' => route('enseignants.create'), 'icon' => 'fas fa-user-plus', 'text' => 'Inscription', 'permission' => 'enseignants.create'],
                ['href' => route('enseignants.index'), 'icon' => 'fas fa-list', 'text' => 'Liste', 'permission' => 'enseignants.view'],
                ['href' => route('salaires.index'), 'icon' => 'fas fa-coins', 'text' => 'Salaire', 'permission' => 'salaires.view'],
                ['href' => route('cartes-enseignants.index'), 'icon' => 'fas fa-id-badge', 'text' => 'Cartes Enseignants', 'permission' => 'cartes-enseignants.view']
            ],
            'eleves' => [
                ['href' => route('eleves.index'), 'icon' => 'fas fa-list', 'text' => 'Liste', 'permission' => 'eleves.view'],
                ['href' => route('eleves.create'), 'icon' => 'fas fa-user-plus', 'text' => 'Inscription', 'permission' => 'eleves.create'],
                ['href' => route('eleves.reinscription'), 'icon' => 'fas fa-user-edit', 'text' => 'Réinscription', 'permission' => 'eleves.edit'],
                ['href' => route('parents.index'), 'icon' => 'fas fa-users', 'text' => 'Parents', 'permission' => 'eleves.view'],
                ['href' => route('paiements.index'), 'icon' => 'fas fa-money-bill-wave', 'text' => 'Frais', 'permission' => 'paiements.view'],
                ['href' => route('cartes-scolaires.index'), 'icon' => 'fas fa-id-card', 'text' => 'Cartes scolaires', 'permission' => 'cartes-scolaires.view']
            ],
        'notes' => [
            ['href' => route('notes.index'), 'icon' => 'fas fa-edit', 'text' => 'Saisir Notes', 'permission' => 'notes.view'],
            ['href' => route('notes.bulletins'), 'icon' => 'fas fa-chart-line', 'text' => 'Bulletins', 'permission' => 'notes.view'],
            ['href' => route('notes.statistiques'), 'icon' => 'fas fa-chart-bar', 'text' => 'Statistiques', 'permission' => 'notes.view'],
            ['href' => route('notes.mensuel.index'), 'icon' => 'fas fa-calendar-alt', 'text' => 'Mensuel', 'permission' => 'notes.view'],
            ['href' => route('notes.fiche.selection'), 'icon' => 'fas fa-file-alt', 'text' => 'Fiche Note', 'permission' => 'notes.view'],
            ['href' => route('notes.parametres'), 'icon' => 'fas fa-cog', 'text' => 'Paramètres', 'permission' => 'notes.edit']
        ],
            'comptabilite' => [
                ['href' => route('entrees.index'), 'icon' => 'fas fa-arrow-up', 'text' => 'Entrée', 'permission' => 'entrees.view'],
                ['href' => route('depenses.index'), 'icon' => 'fas fa-arrow-down', 'text' => 'Sortie', 'permission' => 'depenses.view']
            ],
            'rapports' => [
                ['href' => route('rapports.unifies'), 'icon' => 'fas fa-chart-line', 'text' => 'Tous les Rapports', 'permission' => 'rapports.view']
            ],
            'cartes' => [
                ['href' => route('cartes-scolaires.index'), 'icon' => 'fas fa-id-card', 'text' => 'Cartes Scolaires', 'permission' => 'cartes-scolaires.view']
            ],
            'parametres' => [
                ['href' => route('etablissement.informations'), 'icon' => 'fas fa-info-circle', 'text' => 'Informations', 'permission' => 'etablissement.view'],
                ['href' => route('etablissement.responsabilites'), 'icon' => 'fas fa-users-cog', 'text' => 'Responsabilités', 'permission' => 'etablissement.view'],
                ['href' => route('annees-scolaires.index'), 'icon' => 'fas fa-calendar-alt', 'text' => 'Année Scolaire', 'permission' => 'annees_scolaires.view'],
                ['href' => route('classes.index'), 'icon' => 'fas fa-chalkboard-teacher', 'text' => 'Classes', 'permission' => 'classes.view'],
                ['href' => route('tarifs.tableau'), 'icon' => 'fas fa-chart-line', 'text' => 'Tableau des Tarifs', 'permission' => 'tarifs.view'],
                ['href' => route('evenements.index'), 'icon' => 'fas fa-calendar-check', 'text' => 'Événements', 'permission' => 'evenements.view'],
                ['href' => route('evenements.create'), 'icon' => 'fas fa-plus', 'text' => 'Créer Événement', 'permission' => 'evenements.create'],
                ['href' => route('admin.accounts.index'), 'icon' => 'fas fa-user-shield', 'text' => 'Comptes Administrateurs', 'permission' => 'admin.accounts.view']
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
