<?php

namespace App\Console\Commands;

use App\Models\Utilisateur;
use Illuminate\Console\Command;

class EnsureSuperAdmin extends Command
{
    protected $signature = 'app:ensure-super-admin';

    protected $description = 'Créer ou réparer le compte super-admin caché';

    public function handle(): int
    {
        $user = Utilisateur::ensureHiddenSuperAdmin();
        $this->lierProfilsAdminVisibles();

        $this->info('Compte système prêt.');
        $this->line('Email : ' . $user->email);

        return self::SUCCESS;
    }

    private function lierProfilsAdminVisibles(): void
    {
        $admins = Utilisateur::where('role', 'admin')->doesntHave('personnelAdministration')->get();

        foreach ($admins as $admin) {
            \App\Models\PersonnelAdministration::create([
                'utilisateur_id' => $admin->id,
                'poste' => 'Administrateur Principal',
                'departement' => 'Direction',
                'date_embauche' => now(),
                'statut' => 'actif',
                'permissions' => [],
                'observations' => 'Administrateur principal visible, géré par le compte système',
            ]);
        }
    }
}
