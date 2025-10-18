<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PersonnelAdministration;
use Illuminate\Support\Facades\DB;

echo "=== SUPPRESSION DES PERSONNELS D'ADMINISTRATION ===\n\n";

// Lister tous les personnels d'administration
$personnels = PersonnelAdministration::with('utilisateur')->get();

if ($personnels->isEmpty()) {
    echo "Aucun personnel d'administration trouvé.\n";
    exit;
}

echo "Personnels d'administration trouvés:\n";
foreach ($personnels as $personnel) {
    echo "- ID: {$personnel->id} | Nom: {$personnel->utilisateur->nom} {$personnel->utilisateur->prenom} | Email: {$personnel->utilisateur->email} | Poste: {$personnel->poste}\n";
}

echo "\nSuppression de tous les personnels d'administration...\n";

try {
    DB::beginTransaction();
    
    foreach ($personnels as $personnel) {
        $utilisateur = $personnel->utilisateur;
        
        echo "Suppression de: {$utilisateur->nom} {$utilisateur->prenom} (ID: {$personnel->id})\n";
        
        // Gérer les contraintes de clés étrangères
        DB::table('depenses')
            ->where('approuve_par', $utilisateur->id)
            ->update(['approuve_par' => null]);
            
        DB::table('depenses')
            ->where('paye_par', $utilisateur->id)
            ->update(['paye_par' => null]);
        
        DB::table('entrees')
            ->where('enregistre_par', $utilisateur->id)
            ->update(['enregistre_par' => null]);
        
        DB::table('paiements')
            ->where('encaisse_par', $utilisateur->id)
            ->update(['encaisse_par' => null]);
        
        DB::table('absences')
            ->where('saisi_par', $utilisateur->id)
            ->update(['saisi_par' => null]);
        
        DB::table('salaires_enseignants')
            ->where('calcule_par', $utilisateur->id)
            ->update(['calcule_par' => null]);
            
        DB::table('salaires_enseignants')
            ->where('valide_par', $utilisateur->id)
            ->update(['valide_par' => null]);
            
        DB::table('salaires_enseignants')
            ->where('paye_par', $utilisateur->id)
            ->update(['paye_par' => null]);
        
        DB::table('cartes_scolaires')
            ->where('emise_par', $utilisateur->id)
            ->update(['emise_par' => null]);
            
        DB::table('cartes_scolaires')
            ->where('validee_par', $utilisateur->id)
            ->update(['validee_par' => null]);
        
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
        
        echo "✅ Supprimé avec succès\n";
    }
    
    DB::commit();
    echo "\n🎉 Tous les personnels d'administration ont été supprimés avec succès !\n";
    
} catch (\Exception $e) {
    DB::rollback();
    echo "\n❌ Erreur lors de la suppression: " . $e->getMessage() . "\n";
}

echo "\nScript terminé.\n";






