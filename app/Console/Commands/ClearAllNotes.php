<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Note;

class ClearAllNotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notes:clear-all {--force : Force la suppression sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Supprime toutes les notes saisies pour toutes les classes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $totalNotes = Note::count();
        
        if ($totalNotes === 0) {
            $this->info('Aucune note trouvée dans la base de données.');
            return 0;
        }

        $this->warn("⚠️  ATTENTION : Cette action va supprimer TOUTES les notes saisies !");
        $this->line("📊 Nombre total de notes à supprimer : {$totalNotes}");
        
        if (!$this->option('force')) {
            if (!$this->confirm('Êtes-vous sûr de vouloir continuer ? Cette action est irréversible !')) {
                $this->info('Opération annulée.');
                return 0;
            }
        }

        $this->info('🗑️  Suppression des notes en cours...');
        
        try {
            $deletedCount = Note::count();
            Note::truncate();
            
            $this->info("✅ Suppression terminée avec succès !");
            $this->line("📊 {$deletedCount} notes supprimées.");
            $this->line("🎯 Toutes les classes sont maintenant vides de notes.");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la suppression : " . $e->getMessage());
            return 1;
        }
    }
}
