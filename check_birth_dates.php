<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking student birth dates:\n";
echo "============================\n\n";

$eleves = \App\Models\Eleve::with('utilisateur')->take(5)->get();

foreach ($eleves as $eleve) {
    echo "Student: " . $eleve->nom_complet . "\n";
    echo "Birth date: " . ($eleve->utilisateur->date_naissance ?? 'NULL') . "\n";
    echo "Birth date formatted: " . ($eleve->utilisateur->date_naissance ? \Carbon\Carbon::parse($eleve->utilisateur->date_naissance)->format('d/m/Y') : 'Non renseignée') . "\n";
    echo "---\n";
}

echo "\nChecking if date_naissance field exists in utilisateurs table:\n";
$user = \App\Models\Utilisateur::first();
if ($user) {
    echo "User found: " . $user->nom_complet . "\n";
    echo "Date naissance raw: " . ($user->date_naissance ?? 'NULL') . "\n";
    echo "Date naissance formatted: " . ($user->date_naissance ? \Carbon\Carbon::parse($user->date_naissance)->format('d/m/Y') : 'Non renseignée') . "\n";
}

