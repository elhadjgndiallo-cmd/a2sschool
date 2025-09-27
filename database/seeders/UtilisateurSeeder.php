<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Utilisateur;
use App\Models\Eleve;
use App\Models\Enseignant;
use App\Models\ParentModel;

class UtilisateurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Administrateur
        $admin = Utilisateur::create([
            'name' => 'Administrateur Principal',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('Elhadj3248'),
            'role' => 'admin',
            'telephone' => '0123456789',
            'adresse' => '123 Rue de l\'École, Ville',
            'actif' => true,
        ]);

        // Enseignants
        $enseignant1 = Utilisateur::create([
            'name' => 'Camara Enseignant',
            'email' => 'camara@gmail.com',
            'password' => Hash::make('Elhadj3248'),
            'role' => 'teacher',
            'telephone' => '0123456790',
            'adresse' => '456 Avenue des Professeurs, Ville',
            'date_naissance' => '1985-03-15',
            'sexe' => 'M',
            'actif' => true,
        ]);

        Enseignant::create([
            'utilisateur_id' => $enseignant1->id,
            'numero_employe' => 'ENS001',
            'date_embauche' => '2020-09-01',
            'specialite' => 'Mathématiques',
            'statut' => 'titulaire',
            'salaire' => 3500.00,
            'qualifications' => 'Master en Mathématiques, CAPES',
        ]);

        $enseignant2 = Utilisateur::create([
            'name' => 'Elhadj Mamadou',
            'email' => 'elhadjmamadou@gmail.com',
            'password' => Hash::make('Elhadj3248'),
            'role' => 'teacher',
            'telephone' => '0123456791',
            'adresse' => '789 Rue des Enseignants, Ville',
            'date_naissance' => '1980-07-22',
            'sexe' => 'M',
            'actif' => true,
        ]);

        Enseignant::create([
            'utilisateur_id' => $enseignant2->id,
            'numero_employe' => 'ENS002',
            'date_embauche' => '2018-09-01',
            'specialite' => 'Français',
            'statut' => 'titulaire',
            'salaire' => 3400.00,
            'qualifications' => 'Master en Lettres Modernes, CAPES',
        ]);

        // Parent
        $parent1 = Utilisateur::create([
            'name' => 'Elhadj Parent',
            'email' => 'elhadj@gmail.com',
            'password' => Hash::make('Elhadj3248'),
            'role' => 'parent',
            'telephone' => '0123456792',
            'adresse' => '321 Rue des Familles, Ville',
            'date_naissance' => '1975-11-10',
            'sexe' => 'M',
            'actif' => true,
        ]);

        ParentModel::create([
            'utilisateur_id' => $parent1->id,
            'profession' => 'Ingénieur',
            'employeur' => 'TechCorp',
            'telephone_travail' => '0123456793',
            'lien_parente' => 'pere',
            'contact_urgence' => true,
        ]);

        // Élèves
        $eleve1 = Utilisateur::create([
            'name' => 'Lucas Elève',
            'email' => 'lucas@gmail.com',
            'password' => Hash::make('Elhadj3248'),
            'role' => 'student',
            'telephone' => '0123456796',
            'adresse' => '321 Rue des Familles, Ville',
            'date_naissance' => '2010-09-12',
            'sexe' => 'M',
            'actif' => true,
        ]);

        $eleve2 = Utilisateur::create([
            'name' => 'Emma Élève',
            'email' => 'emma@gmail.com',
            'password' => Hash::make('Elhadj3248'),
            'role' => 'student',
            'telephone' => '0123456797',
            'adresse' => '654 Boulevard des Étudiants, Ville',
            'date_naissance' => '2011-02-28',
            'sexe' => 'F',
            'actif' => true,
        ]);
    }
}
