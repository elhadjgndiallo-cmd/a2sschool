<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EvenementPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'evenements.view' => 'Voir les événements',
            'evenements.create' => 'Créer des événements',
            'evenements.edit' => 'Modifier les événements',
            'evenements.delete' => 'Supprimer les événements',
            'evenements.manage_all' => 'Gérer tous les événements (admin)',
        ];

        foreach ($permissions as $key => $description) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $key],
                [
                    'name' => $key,
                    'description' => $description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Assigner les permissions aux rôles
        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles()
    {
        // Permissions pour les administrateurs (toutes les permissions)
        $adminPermissions = [
            'evenements.view',
            'evenements.create', 
            'evenements.edit',
            'evenements.delete',
            'evenements.manage_all'
        ];

        foreach ($adminPermissions as $permission) {
            DB::table('role_permissions')->updateOrInsert(
                [
                    'role' => 'admin',
                    'permission' => $permission
                ],
                [
                    'role' => 'admin',
                    'permission' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Permissions pour le personnel d'administration (gestion complète)
        $personnelAdminPermissions = [
            'evenements.view',
            'evenements.create',
            'evenements.edit', 
            'evenements.delete'
        ];

        foreach ($personnelAdminPermissions as $permission) {
            DB::table('role_permissions')->updateOrInsert(
                [
                    'role' => 'personnel_admin',
                    'permission' => $permission
                ],
                [
                    'role' => 'personnel_admin',
                    'permission' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Permissions pour les enseignants (création et modification de leurs événements)
        $teacherPermissions = [
            'evenements.view',
            'evenements.create'
        ];

        foreach ($teacherPermissions as $permission) {
            DB::table('role_permissions')->updateOrInsert(
                [
                    'role' => 'teacher',
                    'permission' => $permission
                ],
                [
                    'role' => 'teacher',
                    'permission' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Permissions pour les parents (consultation seulement)
        $parentPermissions = [
            'evenements.view'
        ];

        foreach ($parentPermissions as $permission) {
            DB::table('role_permissions')->updateOrInsert(
                [
                    'role' => 'parent',
                    'permission' => $permission
                ],
                [
                    'role' => 'parent',
                    'permission' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Permissions pour les élèves (consultation seulement)
        $studentPermissions = [
            'evenements.view'
        ];

        foreach ($studentPermissions as $permission) {
            DB::table('role_permissions')->updateOrInsert(
                [
                    'role' => 'student',
                    'permission' => $permission
                ],
                [
                    'role' => 'student',
                    'permission' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
