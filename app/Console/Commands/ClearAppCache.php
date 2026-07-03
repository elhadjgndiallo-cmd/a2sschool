<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class ClearAppCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vider tous les caches de l\'application (config, route, view, cache)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Nettoyage des caches en cours...');
        $this->newLine();
        
        // Cache applicatif
        $this->info('1️⃣  Cache applicatif...');
        Cache::flush();
        $this->line('   ✅ Cache applicatif vidé');
        
        // Config cache
        $this->info('2️⃣  Cache de configuration...');
        Artisan::call('config:clear');
        $this->line('   ✅ Config cache vidé');
        
        // Route cache
        $this->info('3️⃣  Cache des routes...');
        Artisan::call('route:clear');
        $this->line('   ✅ Route cache vidé');
        
        // View cache
        $this->info('4️⃣  Cache des vues...');
        Artisan::call('view:clear');
        $this->line('   ✅ View cache vidé');
        
        // Query cache (si utilisé)
        $this->info('5️⃣  Cache des requêtes...');
        Artisan::call('cache:clear');
        $this->line('   ✅ Query cache vidé');
        
        $this->newLine();
        $this->info('✨ Tous les caches ont été vidés avec succès!');
        
        return Command::SUCCESS;
    }
}
