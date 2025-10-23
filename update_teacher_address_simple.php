<?php
/**
 * Script simple pour modifier l'adresse d'un enseignant
 * Utilisation: php update_teacher_address_simple.php
 */

// Configuration de base
require_once 'vendor/autoload.php';

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Utilisateur;
use App\Models\Enseignant;

echo "=== Script de modification d'adresse enseignant ===\n\n";

try {
    // Afficher la liste des enseignants
    echo "Liste des enseignants disponibles:\n";
    echo "================================\n";
    
    $enseignants = Enseignant::with('utilisateur')->get();
    
    if ($enseignants->isEmpty()) {
        echo "Aucun enseignant trouvé dans la base de données.\n";
        exit;
    }
    
    foreach ($enseignants as $index => $enseignant) {
        echo ($index + 1) . ". ";
        echo "ID: " . $enseignant->id . " | ";
        echo "Nom: " . $enseignant->utilisateur->prenom . " " . $enseignant->utilisateur->nom . " | ";
        echo "Email: " . $enseignant->utilisateur->email . " | ";
        echo "Adresse actuelle: " . ($enseignant->utilisateur->adresse ?? 'Non définie') . "\n";
    }
    
    echo "\n";
    echo "Choisissez une option:\n";
    echo "1. Modifier par ID d'enseignant\n";
    echo "2. Modifier par email\n";
    echo "3. Quitter\n";
    echo "Votre choix (1-3): ";
    
    $choix = trim(fgets(STDIN));
    
    switch ($choix) {
        case '1':
            echo "Entrez l'ID de l'enseignant: ";
            $id = trim(fgets(STDIN));
            $enseignant = Enseignant::with('utilisateur')->find($id);
            break;
            
        case '2':
            echo "Entrez l'email de l'enseignant: ";
            $email = trim(fgets(STDIN));
            $enseignant = Enseignant::whereHas('utilisateur', function($query) use ($email) {
                $query->where('email', $email);
            })->with('utilisateur')->first();
            break;
            
        case '3':
            echo "Au revoir!\n";
            exit;
            
        default:
            echo "Choix invalide.\n";
            exit;
    }
    
    if (!$enseignant) {
        echo "❌ Enseignant non trouvé.\n";
        exit;
    }
    
    echo "\nEnseignant sélectionné:\n";
    echo "========================\n";
    echo "ID: " . $enseignant->id . "\n";
    echo "Nom: " . $enseignant->utilisateur->prenom . " " . $enseignant->utilisateur->nom . "\n";
    echo "Email: " . $enseignant->utilisateur->email . "\n";
    echo "Adresse actuelle: " . ($enseignant->utilisateur->adresse ?? 'Non définie') . "\n";
    
    echo "\nEntrez la nouvelle adresse: ";
    $nouvelleAdresse = trim(fgets(STDIN));
    
    if (empty($nouvelleAdresse)) {
        echo "❌ L'adresse ne peut pas être vide.\n";
        exit;
    }
    
    // Sauvegarder l'ancienne adresse
    $ancienneAdresse = $enseignant->utilisateur->adresse;
    
    // Mettre à jour l'adresse
    $enseignant->utilisateur->adresse = $nouvelleAdresse;
    $enseignant->utilisateur->save();
    
    echo "\n✅ Adresse mise à jour avec succès!\n";
    echo "====================================\n";
    echo "Enseignant: " . $enseignant->utilisateur->prenom . " " . $enseignant->utilisateur->nom . "\n";
    echo "Ancienne adresse: " . ($ancienneAdresse ?? 'Non définie') . "\n";
    echo "Nouvelle adresse: " . $nouvelleAdresse . "\n";
    echo "Date de modification: " . now()->format('d/m/Y H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . "\n";
    echo "Ligne: " . $e->getLine() . "\n";
}


