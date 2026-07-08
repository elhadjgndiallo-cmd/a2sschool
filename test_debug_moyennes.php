<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "═══════════════════════════════════════════════════════════\n";
echo "  DEBUG MOYENNES ANNUELLES PAR MATIÈRE\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Récupérer une classe
$classe = \App\Models\Classe::first();
echo "Classe testée : " . $classe->nom . "\n\n";

// Récupérer un élève de cette classe QUI A DES NOTES
$anneeScolaire = \App\Models\AnneeScolaire::where('active', true)->first();
$eleve = $classe->eleves()
    ->where('annee_scolaire_id', $anneeScolaire->id)
    ->whereHas('notes')  // Élève avec des notes
    ->with('utilisateur')
    ->first();

if (!$eleve) {
    echo "Aucun élève trouvé\n";
    exit(1);
}

echo "Élève testé : " . $eleve->utilisateur->prenom . " " . $eleve->utilisateur->nom . "\n";
echo "ID élève : " . $eleve->id . "\n\n";

// Récupérer les matières
$matieres = $classe->emploisTemps()
    ->with('matiere')
    ->get()
    ->pluck('matiere')
    ->unique('id')
    ->sortBy('nom');

echo "Matières de la classe (" . $matieres->count() . ") :\n";
foreach ($matieres as $matiere) {
    echo "  - ID: " . $matiere->id . " | Nom: " . $matiere->nom . "\n";
}

echo "\n";
echo "─────────────────────────────────────────────────────────\n";
echo "TEST: Récupération notes par période\n";
echo "─────────────────────────────────────────────────────────\n\n";

$periodes = ['trimestre1', 'trimestre2', 'trimestre3'];
$notesParPeriode = [];

foreach ($periodes as $periode) {
    $notes = $eleve->notes()
        ->where('periode', $periode)
        ->with('matiere')
        ->get();
    $notesParPeriode[$periode] = $notes;
    echo "$periode : " . $notes->count() . " notes\n";
}

echo "\n";
echo "─────────────────────────────────────────────────────────\n";
echo "TEST: Calcul moyennes annuelles par matière\n";
echo "─────────────────────────────────────────────────────────\n\n";

$moyennesAnnuellesParMatiere = \App\Models\Note::construireMoyennesAnnuellesParMatiere($notesParPeriode, $periodes);

echo "Résultat de construireMoyennesAnnuellesParMatiere :\n";
echo "Type: " . gettype($moyennesAnnuellesParMatiere) . "\n";
echo "Nombre d'éléments: " . count($moyennesAnnuellesParMatiere) . "\n\n";

if (is_array($moyennesAnnuellesParMatiere)) {
    echo "Contenu du tableau :\n";
    foreach ($moyennesAnnuellesParMatiere as $matiereId => $moyenne) {
        $matiere = \App\Models\Matiere::find($matiereId);
        $nom = $matiere ? $matiere->nom : "Matière inconnue";
        echo "  Matiere ID $matiereId ($nom) => Moyenne: " . number_format($moyenne, 2) . "\n";
    }
}

echo "\n";
echo "─────────────────────────────────────────────────────────\n";
echo "TEST: Mapping avec les matières de la classe\n";
echo "─────────────────────────────────────────────────────────\n\n";

foreach ($matieres as $matiere) {
    $moyenneAnnuelle = $moyennesAnnuellesParMatiere[$matiere->id] ?? null;
    echo "Matière: " . $matiere->nom . " (ID: " . $matiere->id . ")\n";
    echo "  Moyenne annuelle: " . ($moyenneAnnuelle !== null ? number_format($moyenneAnnuelle, 2) : 'NULL') . "\n\n";
}

echo "═══════════════════════════════════════════════════════════\n";
