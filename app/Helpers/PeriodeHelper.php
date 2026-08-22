<?php

namespace App\Helpers;

use App\Models\Classe;

class PeriodeHelper
{
    /**
     * Libellé d'affichage d'une période.
     * Primaire : Trimestre N | Collège/Lycée : Semestre N | Sans classe : Trimestre/Semestre N
     */
    public static function libelle(string $periode, ?Classe $classe = null): string
    {
        $numero = self::numero($periode);
        if ($numero === null) {
            return $periode;
        }

        if ($classe === null) {
            return 'Trimestre/Semestre ' . $numero;
        }

        $type = $classe->isPrimaire() ? 'Trimestre' : 'Semestre';

        return $type . ' ' . $numero;
    }

    /**
     * Type de période : Trimestre ou Semestre.
     */
    public static function type(?Classe $classe = null): string
    {
        if ($classe === null) {
            return 'Trimestre/Semestre';
        }

        return $classe->isPrimaire() ? 'Trimestre' : 'Semestre';
    }

    /**
     * Codes de périodes disponibles pour une classe.
     *
     * @return array<int, string>
     */
    public static function periodesDisponibles(?Classe $classe = null): array
    {
        if ($classe === null || $classe->isPrimaire()) {
            return ['trimestre1', 'trimestre2', 'trimestre3'];
        }

        return ['trimestre1', 'trimestre2'];
    }

    /**
     * Options pour un select [code => libellé].
     *
     * @return array<string, string>
     */
    public static function options(?Classe $classe = null): array
    {
        $options = [];
        foreach (self::periodesDisponibles($classe) as $code) {
            $options[$code] = self::libelle($code, $classe);
        }

        return $options;
    }

    public static function numero(string $periode): ?int
    {
        return match ($periode) {
            'trimestre1', 'semestre1' => 1,
            'trimestre2', 'semestre2' => 2,
            'trimestre3' => 3,
            default => null,
        };
    }
}
