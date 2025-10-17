<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PersonnelAdministration;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;

echo "=== LISTE DES PERSONNELS D'ADMINISTRATION ===\n\n";

$personnels = PersonnelAdministration::with('utilisateur')->get();

if ($personnels->isEmpty()) {
    echo "Aucun personnel d'administration trouvé.\n";
    exit;
}

foreach ($personnels as $personnel) {
    echo "ID: {$personnel->id}\n";
    echo "Nom: {$personnel->utilisateur->nom} {$personnel->utilisateur->prenom}\n";
    echo "Email: {$personnel->utilisateur->email}\n";
    echo "Poste: {$personnel->poste}\n";
    echo "Département: {$personnel->departement}\n";
    echo "Statut: {$personnel->statut}\n";
    echo "Permissions: " . json_encode($personnel->permissions) . "\n";
    echo "---\n";
}

echo "\nVoulez-vous supprimer un personnel d'administration ? (y/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$response = trim($line);
fclose($handle);

if (strtolower($response) === 'y' || strtolower($response) === 'yes') {
    echo "Entrez l'ID du personnel à supprimer: ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    $id = trim($line);
    fclose($handle);
    
    $personnel = PersonnelAdministration::find($id);
    
    if (!$personnel) {
        echo "Personnel d'administration avec l'ID {$id} non trouvé.\n";
        exit;
    }
    
    echo "\nPersonnel trouvé: {$personnel->utilisateur->nom} {$personnel->utilisateur->prenom}\n";
    echo "Êtes-vous sûr de vouloir supprimer ce compte ? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    $confirm = trim($line);
    fclose($handle);
    
    if (strtolower($confirm) === 'y' || strtolower($confirm) === 'yes') {
        try {
            DB::beginTransaction();
            
            // Gérer les contraintes de clés étrangères avant suppression
            $utilisateur = $personnel->utilisateur;
            
            // Mettre à jour les dépenses qui référencent cet utilisateur
            DB::table('depenses')
                ->where('approuve_par', $utilisateur->id)
                ->update(['approuve_par' => null]);
                
            DB::table('depenses')
                ->where('paye_par', $utilisateur->id)
                ->update(['paye_par' => null]);
            
            // Mettre à jour les entrées qui référencent cet utilisateur
            DB::table('entrees')
                ->where('enregistre_par', $utilisateur->id)
                ->update(['enregistre_par' => null]);
            
            // Mettre à jour les paiements qui référencent cet utilisateur
            DB::table('paiements')
                ->where('encaisse_par', $utilisateur->id)
                ->update(['encaisse_par' => null]);
            
            // Mettre à jour les absences qui référencent cet utilisateur
            DB::table('absences')
                ->where('saisi_par', $utilisateur->id)
                ->update(['saisi_par' => null]);
            
            // Mettre à jour les salaires qui référencent cet utilisateur
            DB::table('salaires_enseignants')
                ->where('calcule_par', $utilisateur->id)
                ->update(['calcule_par' => null]);
                
            DB::table('salaires_enseignants')
                ->where('valide_par', $utilisateur->id)
                ->update(['valide_par' => null]);
                
            DB::table('salaires_enseignants')
                ->where('paye_par', $utilisateur->id)
                ->update(['paye_par' => null]);
            
            // Mettre à jour les cartes scolaires qui référencent cet utilisateur
            DB::table('cartes_scolaires')
                ->where('emise_par', $utilisateur->id)
                ->update(['emise_par' => null]);
                
            DB::table('cartes_scolaires')
                ->where('validee_par', $utilisateur->id)
                ->update(['validee_par' => null]);
            
            // Supprimer les messages envoyés et reçus par cet utilisateur
            DB::table('messages')
                ->where('expediteur_id', $utilisateur->id)
                ->orWhere('destinataire_id', $utilisateur->id)
                ->delete();
            
            // Supprimer la photo de profil si elle existe
            if ($utilisateur->photo_profil) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($utilisateur->photo_profil);
            }
            
            // Supprimer le personnel d'administration (cascade vers utilisateur)
            $personnel->delete();
            
            DB::commit();
            
            echo "✅ Personnel d'administration supprimé avec succès !\n";
            echo "Utilisateur: {$utilisateur->nom} {$utilisateur->prenom}\n";
            echo "Email: {$utilisateur->email}\n";
            
        } catch (\Exception $e) {
            DB::rollback();
            echo "❌ Erreur lors de la suppression: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Suppression annulée.\n";
    }
} else {
    echo "Suppression annulée.\n";
}

echo "\nScript terminé.\n";





