<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\ParentModel;
use App\Models\Utilisateur;
use App\Support\SimpleXlsx;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class EleveImportService
{
    public const HEADERS = [
        'prenom',
        'nom',
        'sexe',
        'type_inscription',
        'numero_etudiant',
        'date_naissance',
        'lieu_naissance',
        'telephone',
        'adresse',
        'situation_matrimoniale',
        'ecole_origine',
        'exempte_frais',
        'paiement_annuel',
        'gratuit_inscription',
        'gratuit_reinscription',
        'nom_parent',
        'prenom_parent',
        'telephone_parent',
    ];

    public const REQUIRED_HEADERS = [
        'prenom',
        'nom',
        'sexe',
        'type_inscription',
    ];

    public function __construct(
        private EleveInscriptionService $inscriptionService
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function preview(UploadedFile $file, int $classeId): array
    {
        $classe = $this->resolveClasse($classeId);
        $parsed = $this->parseFile($file);
        $this->assertRequiredHeaders($parsed['headers']);

        $anneeActive = AnneeScolaire::where('active', true)->first();
        $matriculesDansFichier = [];
        $results = [];
        $lineNumber = 2;

        foreach ($parsed['rows'] as $rawRow) {
            $result = $this->validateRow($rawRow, $lineNumber, $anneeActive, $matriculesDansFichier);
            $results[] = $result;
            $lineNumber++;
        }

        $validCount = collect($results)->where('status', 'ok')->count();
        $this->appendCapacityWarning($results, $classe, $validCount);

        return $results;
    }

    /**
     * @param  array<int, array<string, mixed>>  $previewRows
     * @return array{imported: int, matricules: array<int, string>}
     */
    public function importRows(array $previewRows, int $classeId): array
    {
        $errorRows = collect($previewRows)->filter(fn ($row) => ($row['status'] ?? '') === 'error' && ($row['line'] ?? 0) > 0);
        if ($errorRows->isNotEmpty()) {
            throw new RuntimeException('Import impossible : corrigez les erreurs signalées dans la prévisualisation.');
        }

        $classe = $this->resolveClasse($classeId);
        $validRows = collect($previewRows)->filter(
            fn ($row) => in_array($row['status'] ?? '', ['ok', 'warning'], true)
                && ($row['line'] ?? 0) > 0
                && !empty($row['data'])
        );
        if ($validRows->isEmpty()) {
            throw new RuntimeException('Aucune ligne valide à importer.');
        }

        $imported = [];
        $matriculesReserves = [];

        foreach ($validRows as $row) {
            $data = $row['data'];
            if (empty($data['numero_etudiant'])) {
                do {
                    $data['numero_etudiant'] = $this->inscriptionService->generateNewMatricule();
                } while (in_array($data['numero_etudiant'], $matriculesReserves, true));
            }
            $matriculesReserves[] = $data['numero_etudiant'];

            $parentData = $this->resolveParentData($data['parent'] ?? null);
            unset($data['parent']);

            $eleve = $this->inscriptionService->inscrire($data, $classe->id, $parentData);
            $imported[] = $eleve;
        }

        $classe->updateEffectifActuel();

        return [
            'imported' => count($imported),
            'matricules' => array_map(fn (Eleve $e) => $e->numero_etudiant, $imported),
        ];
    }

    public function generateTemplatePath(): string
    {
        $directory = storage_path('app/templates');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory . DIRECTORY_SEPARATOR . 'modele_import_eleves.xlsx';

        $exampleRow = [
            'Aminata',
            'DIALLO',
            'F',
            '1',
            '',
            '15/03/2015',
            'Conakry',
            '620000001',
            'Matam',
            'celibataire',
            '',
            'non',
            'non',
            'non',
            'non',
            'DIALLO',
            'Mamadou',
            '620000002',
        ];

        $helpRows = [
            ['Colonne', 'Valeurs autorisées / remarques'],
            ['prenom, nom', 'Obligatoires'],
            ['sexe', 'F = Féminin, H = Homme'],
            ['type_inscription', '1 = Inscription, 2 = Réinscription'],
            ['numero_etudiant', 'Optionnel — généré automatiquement si vide'],
            ['date_inscription', 'Automatique : date du jour (pas de colonne)'],
            ['statut', 'Automatique : actif (pas de colonne)'],
            ['date_naissance', 'Optionnel — JJ/MM/AAAA ou AAAA-MM-JJ'],
            ['situation_matrimoniale', 'celibataire, marie, divorce, veuf (optionnel)'],
            ['exempte_frais, paiement_annuel, gratuit_inscription, gratuit_reinscription', 'oui / non'],
            ['nom_parent, prenom_parent', 'Optionnels — si renseignés (les deux), le parent est créé ou lié'],
            ['telephone_parent', 'Optionnel — si déjà utilisé par un parent, ce parent est réutilisé'],
            ['classe', 'Choisie dans l\'interface avant l\'import — pas de colonne dans le fichier'],
        ];

        SimpleXlsx::write($path, self::HEADERS, [$exampleRow], $helpRows);

        return $path;
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<string, string|null>>}
     */
    private function parseFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'csv') {
            return $this->parseCsv($file->getRealPath());
        }

        if (in_array($extension, ['xlsx', 'xls'], true)) {
            if ($extension === 'xls') {
                throw new RuntimeException('Le format .xls n\'est pas supporté. Enregistrez le fichier au format .xlsx ou .csv.');
            }

            if (class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                return $this->parseWithPhpSpreadsheet($file->getRealPath());
            }

            return SimpleXlsx::read($file->getRealPath());
        }

        throw new RuntimeException('Format de fichier non supporté. Utilisez .xlsx ou .csv.');
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<string, string|null>>}
     */
    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('Impossible de lire le fichier CSV.');
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            throw new RuntimeException('Le fichier CSV est vide.');
        }

        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);

        $headers = [];
        $rows = [];
        $lineNumber = 0;

        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            $lineNumber++;
            if ($lineNumber === 1) {
                $headers = array_map(fn ($h) => $this->normalizeHeader((string) $h), $line);
                continue;
            }

            if ($this->rowIsEmpty($line)) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }
                $assoc[$header] = isset($line[$index]) ? trim((string) $line[$index]) : null;
            }
            $rows[] = $assoc;
        }

        fclose($handle);

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<string, string|null>>}
     */
    private function parseWithPhpSpreadsheet(string $path): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $matrix = $sheet->toArray(null, true, true, false);

        if (empty($matrix)) {
            return ['headers' => [], 'rows' => []];
        }

        $headers = array_map(fn ($h) => $this->normalizeHeader((string) $h), array_shift($matrix));
        $rows = [];

        foreach ($matrix as $line) {
            if ($this->rowIsEmpty($line)) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }
                $assoc[$header] = isset($line[$index]) ? trim((string) $line[$index]) : null;
            }
            $rows[] = $assoc;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * @param  array<int, string>  $headers
     */
    private function assertRequiredHeaders(array $headers): void
    {
        $headers = $this->aliasHeaders($headers);
        $missing = array_diff(self::REQUIRED_HEADERS, $headers);
        if (!empty($missing)) {
            throw new RuntimeException(
                'Colonnes obligatoires manquantes : ' . implode(', ', $missing)
            );
        }
    }

    /**
     * @param  array<string, string|null>  $rawRow
     * @param  array<int, string>  $matriculesDansFichier
     * @return array<string, mixed>
     */
    private function validateRow(
        array $rawRow,
        int $lineNumber,
        ?AnneeScolaire $anneeActive,
        array &$matriculesDansFichier
    ): array {
        $rawRow = $this->normalizeRowKeys($rawRow);
        $messages = [];
        $warnings = [];

        $prenom = trim((string) ($rawRow['prenom'] ?? ''));
        $nom = trim((string) ($rawRow['nom'] ?? ''));
        $sexe = $this->normalizeSexe($rawRow['sexe'] ?? null);
        $dateInscription = Carbon::today()->format('Y-m-d');
        $typeInscription = $this->normalizeTypeInscription($rawRow['type_inscription'] ?? null);
        $statut = 'inscrit';
        $numeroEtudiant = trim((string) ($rawRow['numero_etudiant'] ?? ''));

        if ($prenom === '') {
            $messages[] = 'Le prénom est obligatoire.';
        }
        if ($nom === '') {
            $messages[] = 'Le nom est obligatoire.';
        }
        if ($sexe === null) {
            $messages[] = 'Le sexe est invalide (H ou F attendu).';
        }
        if ($typeInscription === null) {
            $messages[] = 'Le type d\'inscription est invalide (1 = Inscription, 2 = Réinscription).';
        }

        $dateNaissance = null;
        if (!empty($rawRow['date_naissance'])) {
            $dateNaissance = $this->parseDate($rawRow['date_naissance']);
            if ($dateNaissance === null) {
                $messages[] = 'La date de naissance est invalide.';
            } elseif (Carbon::parse($dateNaissance)->isFuture()) {
                $messages[] = 'La date de naissance doit être antérieure à aujourd\'hui.';
            }
        }

        $situation = null;
        if (!empty($rawRow['situation_matrimoniale'])) {
            $situation = $this->normalizeEnum(
                $rawRow['situation_matrimoniale'],
                ['celibataire', 'marie', 'divorce', 'veuf']
            );
            if ($situation === null) {
                $messages[] = 'La situation matrimoniale est invalide.';
            }
        }

        if ($numeroEtudiant !== '') {
            if (in_array($numeroEtudiant, $matriculesDansFichier, true)) {
                $messages[] = 'Matricule dupliqué dans le fichier.';
            } else {
                $matriculesDansFichier[] = $numeroEtudiant;
            }

            if ($anneeActive) {
                $exists = Eleve::where('numero_etudiant', $numeroEtudiant)
                    ->where('annee_scolaire_id', $anneeActive->id)
                    ->exists();
                if ($exists) {
                    $messages[] = 'Ce matricule existe déjà pour l\'année scolaire active.';
                }
            }
        }

        $telephone = trim((string) ($rawRow['telephone'] ?? ''));
        if (strlen($telephone) > 20) {
            $messages[] = 'Le téléphone ne doit pas dépasser 20 caractères.';
        }

        $nomParent = trim((string) ($rawRow['nom_parent'] ?? ''));
        $prenomParent = trim((string) ($rawRow['prenom_parent'] ?? ''));
        $telephoneParent = trim((string) ($rawRow['telephone_parent'] ?? ''));

        if (strlen($telephoneParent) > 20) {
            $messages[] = 'Le téléphone parent ne doit pas dépasser 20 caractères.';
        }

        $hasNomParent = $nomParent !== '';
        $hasPrenomParent = $prenomParent !== '';
        $hasTelephoneParent = $telephoneParent !== '';
        $parentData = null;
        $parentDisplay = '—';

        if ($hasNomParent || $hasPrenomParent || $hasTelephoneParent) {
            if (!$hasNomParent || !$hasPrenomParent) {
                $messages[] = 'Pour créer un parent, renseignez nom_parent et prenom_parent (téléphone_parent optionnel).';
            } else {
                $parentData = [
                    'nom' => $nomParent,
                    'prenom' => $prenomParent,
                    'telephone' => $hasTelephoneParent ? $telephoneParent : null,
                ];
                $parentDisplay = trim($prenomParent . ' ' . $nomParent);
                if ($hasTelephoneParent) {
                    $parentDisplay .= ' (' . $telephoneParent . ')';
                }
            }
        }

        $data = [
            'numero_etudiant' => $numeroEtudiant,
            'prenom' => $prenom,
            'nom' => $nom,
            'sexe' => $sexe,
            'date_inscription' => $dateInscription,
            'type_inscription' => $typeInscription,
            'statut' => $statut,
            'date_naissance' => $dateNaissance,
            'lieu_naissance' => $this->nullableString($rawRow['lieu_naissance'] ?? null),
            'telephone' => $telephone !== '' ? $telephone : null,
            'adresse' => $this->nullableString($rawRow['adresse'] ?? null),
            'situation_matrimoniale' => $situation,
            'ecole_origine' => $this->nullableString($rawRow['ecole_origine'] ?? null),
            'exempte_frais' => $this->parseBoolean($rawRow['exempte_frais'] ?? null),
            'paiement_annuel' => $this->parseBoolean($rawRow['paiement_annuel'] ?? null),
            'gratuit_inscription' => $this->parseBoolean($rawRow['gratuit_inscription'] ?? null),
            'gratuit_reinscription' => $this->parseBoolean($rawRow['gratuit_reinscription'] ?? null),
            'parent' => $parentData,
        ];

        $status = !empty($messages) ? 'error' : (!empty($warnings) ? 'warning' : 'ok');

        return [
            'line' => $lineNumber,
            'status' => $status,
            'messages' => array_merge($messages, $warnings),
            'data' => $data,
            'display' => [
                'prenom' => $prenom,
                'nom' => $nom,
                'numero_etudiant' => $numeroEtudiant !== '' ? $numeroEtudiant : '(auto)',
                'sexe' => $sexe === 'M' ? 'H' : ($sexe ?? '—'),
                'date_inscription' => Carbon::parse($dateInscription)->format('d/m/Y') . ' (auto)',
                'type_inscription' => $this->typeInscriptionLibelle($typeInscription),
                'statut' => 'Actif (auto)',
                'parent' => $parentDisplay,
            ],
        ];
    }

    /**
     * @param  array{nom: string, prenom: string, telephone: ?string}|null  $parent
     * @return array<string, mixed>|null
     */
    private function resolveParentData(?array $parent): ?array
    {
        if ($parent === null) {
            return null;
        }

        $telephone = $parent['telephone'] ?? null;

        if ($telephone) {
            $utilisateur = Utilisateur::where('role', 'parent')
                ->where('telephone', $telephone)
                ->first();

            if ($utilisateur) {
                $parentModel = ParentModel::where('utilisateur_id', $utilisateur->id)->first();
                if ($parentModel) {
                    return [
                        'parent_type' => 'existing',
                        'parent_id' => $parentModel->id,
                        'lien_parente' => 'tuteur',
                        'responsable_legal' => true,
                        'contact_urgence' => true,
                        'autorise_sortie' => true,
                    ];
                }
            }
        }

        return [
            'parent_type' => 'new',
            'parent_nom' => $parent['nom'],
            'parent_prenom' => $parent['prenom'],
            'parent_telephone' => $telephone,
            'lien_parente' => 'tuteur',
            'responsable_legal' => true,
            'contact_urgence' => true,
            'autorise_sortie' => true,
        ];
    }

    private function normalizeRowKeys(array $row): array
    {
        if (isset($row['sex']) && !isset($row['sexe'])) {
            $row['sexe'] = $row['sex'];
        }

        return $row;
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<int, string>
     */
    private function aliasHeaders(array $headers): array
    {
        return array_map(function (string $header) {
            if ($header === 'sex') {
                return 'sexe';
            }

            return $header;
        }, $headers);
    }

    private function normalizeTypeInscription(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        return match ($value) {
            '1' => 'nouvelle',
            '2' => 'reinscription',
            default => $this->normalizeEnum($value, ['nouvelle', 'reinscription']),
        };
    }

    private function typeInscriptionLibelle(?string $type): string
    {
        return match ($type) {
            'nouvelle' => '1 — Inscription',
            'reinscription' => '2 — Réinscription',
            default => '—',
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     */
    private function appendCapacityWarning(array &$results, Classe $classe, int $validCount): void
    {
        if ($validCount <= 0 || !$classe->effectif_max) {
            return;
        }

        $anneeActive = AnneeScolaire::where('active', true)->first();
        $effectifActuel = $anneeActive
            ? $classe->eleves()->where('annee_scolaire_id', $anneeActive->id)->count()
            : $classe->effectif_actuel;

        if ($effectifActuel + $validCount > $classe->effectif_max) {
            $results[] = [
                'line' => 0,
                'status' => 'warning',
                'messages' => [
                    'Capacité de la classe dépassée : '
                    . ($effectifActuel + $validCount) . ' / ' . $classe->effectif_max . ' élèves.',
                ],
                'data' => null,
                'display' => ['info' => 'Capacité classe'],
            ];
        }
    }

    private function resolveClasse(int $classeId): Classe
    {
        $classe = Classe::where('id', $classeId)->where('actif', true)->first();
        if (!$classe) {
            throw new RuntimeException('Classe introuvable ou inactive.');
        }

        if (!AnneeScolaire::where('active', true)->exists()) {
            throw new RuntimeException('Aucune année scolaire active.');
        }

        return $classe;
    }

    private function normalizeHeader(string $header): string
    {
        $header = trim(mb_strtolower($header));
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
        $header = str_replace([' ', '-'], '_', $header);

        if ($header === 'sex') {
            return 'sexe';
        }

        return $header;
    }

    private function normalizeSexe(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = mb_strtolower(trim($value));

        return match ($value) {
            'm', 'masculin', 'homme', 'h' => 'M',
            'f', 'feminin', 'féminin', 'femme' => 'F',
            default => null,
        };
    }

    /**
     * @param  array<int, string>  $allowed
     */
    private function normalizeEnum(?string $value, array $allowed): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = mb_strtolower(trim(str_replace([' ', '-'], '_', $value)));

        return in_array($normalized, $allowed, true) ? $normalized : null;
    }

    private function parseDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Throwable) {
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseBoolean(?string $value): bool
    {
        if ($value === null || trim($value) === '') {
            return false;
        }

        $value = mb_strtolower(trim($value));

        return in_array($value, ['1', 'oui', 'yes', 'true', 'vrai', 'o', 'y'], true);
    }

    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    /**
     * @param  array<int, mixed>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
