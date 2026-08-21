<?php

namespace App\Services;

use App\Http\Controllers\PaiementController;
use App\Models\AnneeScolaire;
use App\Models\Eleve;
use App\Models\ParentModel;
use App\Models\Utilisateur;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EleveInscriptionService
{
    public function __construct(
        private ImageService $imageService
    ) {}

    /**
     * Inscrire un élève (formulaire manuel ou import Excel).
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>|null  $parentData
     */
    public function inscrire(array $data, int $classeId, ?array $parentData = null, ?UploadedFile $photo = null): Eleve
    {
        return DB::transaction(function () use ($data, $classeId, $parentData, $photo) {
            $utilisateur = Utilisateur::create([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $this->generateStudentEmail($data['prenom'], $data['nom']),
                'password' => Hash::make('student123'),
                'telephone' => $data['telephone'] ?? null,
                'adresse' => $data['adresse'] ?? null,
                'sexe' => $data['sexe'],
                'date_naissance' => $data['date_naissance'] ?? null,
                'lieu_naissance' => $data['lieu_naissance'] ?? null,
                'role' => 'student',
                'actif' => true,
            ]);

            if ($photo) {
                $photoPath = $this->imageService->resizeAndSaveImage(
                    $photo,
                    'profile_images',
                    300,
                    300
                );
                $utilisateur->update(['photo_profil' => $photoPath]);
            }

            $anneeScolaireActive = AnneeScolaire::where('active', true)->first();

            $numeroEtudiant = !empty($data['numero_etudiant'])
                ? trim((string) $data['numero_etudiant'])
                : $this->generateNewMatricule();

            $eleve = Eleve::create([
                'utilisateur_id' => $utilisateur->id,
                'classe_id' => $classeId,
                'numero_etudiant' => $numeroEtudiant,
                'date_inscription' => $data['date_inscription'],
                'type_inscription' => $data['type_inscription'],
                'ecole_origine' => $data['ecole_origine'] ?? null,
                'situation_matrimoniale' => $data['situation_matrimoniale'] ?? null,
                'statut' => $data['statut'],
                'annee_scolaire_id' => $anneeScolaireActive?->id,
                'exempte_frais' => (bool) ($data['exempte_frais'] ?? false),
                'paiement_annuel' => (bool) ($data['paiement_annuel'] ?? false),
                'actif' => true,
            ]);

            if ($parentData && !empty($parentData['parent_type'])) {
                $this->attacherParent($eleve, $parentData);
            }

            $paiementController = app(PaiementController::class);
            $paiementController->creerFraisAutomatiques(
                $eleve,
                (bool) ($data['gratuit_inscription'] ?? false),
                (bool) ($data['gratuit_reinscription'] ?? false)
            );

            return $eleve->fresh(['utilisateur', 'classe']);
        });
    }

    /**
     * @param  array<string, mixed>  $parentData
     */
    private function attacherParent(Eleve $eleve, array $parentData): void
    {
        $parentId = null;

        if ($parentData['parent_type'] === 'existing') {
            $parentId = $parentData['parent_id'] ?? null;
        } elseif ($parentData['parent_type'] === 'new') {
            $parentUtilisateur = Utilisateur::create([
                'nom' => $parentData['parent_nom'],
                'prenom' => $parentData['parent_prenom'],
                'email' => $parentData['parent_email'] ?? $this->generateParentEmail(
                    $parentData['parent_prenom'],
                    $parentData['parent_nom']
                ),
                'password' => Hash::make('parent123'),
                'telephone' => $parentData['parent_telephone'] ?? null,
                'adresse' => $parentData['parent_adresse'] ?? null,
                'role' => 'parent',
                'actif' => true,
            ]);

            $parent = ParentModel::create([
                'utilisateur_id' => $parentUtilisateur->id,
            ]);

            $parentId = $parent->id;
        }

        if ($parentId) {
            $eleve->parents()->attach($parentId, [
                'lien_parente' => $parentData['lien_parente'] ?? 'tuteur',
                'autre_lien_parente' => $parentData['autre_lien_parente'] ?? null,
                'responsable_legal' => (bool) ($parentData['responsable_legal'] ?? false),
                'contact_urgence' => (bool) ($parentData['contact_urgence'] ?? false),
                'autorise_sortie' => (bool) ($parentData['autorise_sortie'] ?? false),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function generateNewMatricule(): string
    {
        $school = \App\Helpers\SchoolHelper::getSchoolInfo();

        if (!$school) {
            $prefixe = date('Y');
            $suffixe = '';
        } else {
            $prefixe = $school->prefixe_matricule ?: date('Y');
            $suffixe = $school->suffixe_matricule ?: '';
        }

        $pattern = $prefixe . '%';
        $lastMatricule = Eleve::where('numero_etudiant', 'LIKE', $pattern)
            ->where('numero_etudiant', 'NOT LIKE', '%STD%')
            ->orderByDesc('numero_etudiant')
            ->first();

        if ($lastMatricule) {
            $matriculeWithoutSuffix = $suffixe
                ? str_replace($suffixe, '', $lastMatricule->numero_etudiant)
                : $lastMatricule->numero_etudiant;
            $nextNumber = (int) str_replace($prefixe, '', $matriculeWithoutSuffix) + 1;
        } else {
            $nextNumber = 1;
        }

        $attempts = 0;
        do {
            $numero = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            $matricule = $prefixe . $numero . $suffixe;

            if (!Eleve::where('numero_etudiant', $matricule)->exists()) {
                return $matricule;
            }

            $nextNumber++;
            $attempts++;
        } while ($attempts <= 1000);

        return $prefixe . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT) . uniqid();
    }

    public function generateStudentEmail(string $prenom, string $nom): string
    {
        $base = strtolower($prenom . '.' . $nom);
        $base = preg_replace('/[^a-z0-9.]/', '', $base);
        $counter = 1;

        do {
            $email = $base . ($counter > 1 ? $counter : '') . '@gmail.com';
            $counter++;
        } while (Utilisateur::where('email', $email)->exists());

        return $email;
    }

    public function generateParentEmail(string $prenom, string $nom): string
    {
        $base = strtolower($prenom . '.' . $nom);
        $base = preg_replace('/[^a-z0-9.]/', '', $base);
        $counter = 1;

        do {
            $email = $base . ($counter > 1 ? $counter : '') . '@gmail.com';
            $counter++;
        } while (Utilisateur::where('email', $email)->exists());

        return $email;
    }
}
