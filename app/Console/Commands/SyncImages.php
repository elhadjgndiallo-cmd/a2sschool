<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\ImageSyncHelper;

class SyncImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchroniser les images de storage/app/public vers public/storage pour XAMPP';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Synchronisation des images...');
        
        if (ImageSyncHelper::syncImages()) {
            $this->info('✅ Synchronisation terminée avec succès!');
            $this->line('📁 Images disponibles dans: public/storage/');
            $this->line('🌐 Accessibles via: http://localhost/a2sschool/storage/');
        } else {
            $this->error('❌ Erreur lors de la synchronisation');
        }
    }
}
