<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Emploi du temps — Primaire / préscolaire
    |--------------------------------------------------------------------------
    | Durées autorisées : 30, 45 ou 60 minutes (jusqu'à 1 h).
    | La colonne Heure affiche début–fin selon le créneau créé.
    */

    'primaire' => [
        'durees_autorisees' => [30, 45, 60],
        'duree_defaut' => 45,
        'max_creneaux_par_jour' => 12,
        'journee' => [
            'debut' => '08:00',
            'fin' => '15:00',
        ],
        'recre' => [
            'debut' => '10:00',
            'fin' => '10:15',
            'label' => 'RÉCRÉATION',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Emploi du temps — Collège / lycée (secondaire)
    |--------------------------------------------------------------------------
    */

    'secondaire' => [
        'duree_defaut_minutes' => 120,
        'heures_debut' => [
            '08:00',
            '10:00',
            '10:10',
            '12:10',
            '14:00',
            '14:30',
            '16:00',
            '16:30',
        ],
    ],

];
