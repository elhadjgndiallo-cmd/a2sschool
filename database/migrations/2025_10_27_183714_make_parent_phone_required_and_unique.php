<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // D'abord, nettoyer les téléphones vides ou en double pour les parents
        $this->cleanupParentPhones();
        
        // Modifier la colonne telephone dans utilisateurs pour les parents
        Schema::table('utilisateurs', function (Blueprint $table) {
            // Ajouter un index unique sur telephone pour les utilisateurs avec role 'parent'
            $table->unique(['telephone', 'role'], 'unique_parent_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropUnique('unique_parent_phone');
        });
    }
    
    /**
     * Nettoyer les téléphones des parents
     */
    private function cleanupParentPhones()
    {
        // Récupérer tous les parents sans téléphone
        $parentsWithoutPhone = DB::table('utilisateurs')
            ->where('role', 'parent')
            ->where(function($query) {
                $query->whereNull('telephone')
                      ->orWhere('telephone', '')
                      ->orWhere('telephone', ' ');
            })
            ->get();
        
        // Générer des téléphones temporaires pour les parents sans téléphone
        foreach ($parentsWithoutPhone as $parent) {
            $tempPhone = 'TEMP_' . $parent->id . '_' . time();
            DB::table('utilisateurs')
                ->where('id', $parent->id)
                ->update(['telephone' => $tempPhone]);
        }
        
        // Identifier et corriger les doublons
        $duplicatePhones = DB::table('utilisateurs')
            ->where('role', 'parent')
            ->whereNotNull('telephone')
            ->where('telephone', '!=', '')
            ->select('telephone')
            ->groupBy('telephone')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('telephone');
        
        foreach ($duplicatePhones as $phone) {
            $usersWithSamePhone = DB::table('utilisateurs')
                ->where('role', 'parent')
                ->where('telephone', $phone)
                ->orderBy('id')
                ->get();
            
            // Garder le premier, modifier les autres
            for ($i = 1; $i < count($usersWithSamePhone); $i++) {
                $newPhone = $phone . '_' . $usersWithSamePhone[$i]->id;
                DB::table('utilisateurs')
                    ->where('id', $usersWithSamePhone[$i]->id)
                    ->update(['telephone' => $newPhone]);
            }
        }
    }
};