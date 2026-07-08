<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$note = \App\Models\Note::first();

if ($note) {
    echo "Période: " . $note->periode . PHP_EOL;
    echo "Élève ID: " . $note->eleve_id . PHP_EOL;
    echo "Matière ID: " . $note->matiere_id . PHP_EOL;
} else {
    echo "Aucune note trouvée" . PHP_EOL;
}

// Compter les notes par période
echo "\nNotes par période:" . PHP_EOL;
$periodes = \App\Models\Note::select('periode', \DB::raw('count(*) as total'))
    ->groupBy('periode')
    ->get();

foreach ($periodes as $p) {
    echo "  " . $p->periode . ": " . $p->total . PHP_EOL;
}
