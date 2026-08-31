@php
    $carte = $carte ?? $cartes_scolaire;
    $eleve = $carte->eleve;
    $user = $eleve->utilisateur ?? null;

    $resolvePublicFile = function (?string $path) {
        if (!$path) {
            return null;
        }
        $rel = ltrim($path, '/');
        if (str_starts_with($rel, 'storage/')) {
            $rel = substr($rel, strlen('storage/'));
        }
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($rel) || file_exists(public_path('storage/' . $rel))) {
            return asset('storage/' . $rel);
        }
        return null;
    };

    $photoUrl = $resolvePublicFile($carte->photo_path ?: ($user->photo_profil ?? null));
    $school = \App\Helpers\SchoolHelper::getSchoolInfo();
    $logoUrl = $resolvePublicFile($school?->logo);
    $cachetUrl = $resolvePublicFile($school?->cachet);
    $emblemUrl = $cachetUrl ?: $logoUrl;

    $anneeScolaire = \App\Helpers\SchoolHelper::getActiveSchoolYear();
    $anneeLibelle = $anneeScolaire?->nom;
    if (!$anneeLibelle) {
        $y1 = $carte->date_emission ? $carte->date_emission->format('Y') : now()->year;
        $y2 = $carte->date_expiration ? $carte->date_expiration->format('Y') : ($y1 + 1);
        $anneeLibelle = $y1 . '-' . $y2;
    }

    $schoolNom = $school?->nom ?? 'ÉCOLE';
    $filigrane = strtoupper(trim((string) ($school?->prefixe_matricule ?? '')));
    if ($filigrane === '') {
        if (preg_match('/^([A-Za-z]{2,4})\b/u', $schoolNom, $m)) {
            $filigrane = strtoupper($m[1]);
        } else {
            $skip = ['DE', 'DU', 'LA', 'LE', 'LES', 'ET', 'DES', 'L'];
            $letters = '';
            foreach (preg_split('/\s+/', strtoupper($schoolNom)) as $word) {
                $word = trim($word, " \t\n\r\0\x0B.'-");
                if ($word === '' || in_array($word, $skip, true)) {
                    continue;
                }
                $letters .= mb_substr($word, 0, 1);
                if (mb_strlen($letters) >= 3) {
                    break;
                }
            }
            $filigrane = $letters !== '' ? $letters : 'CS';
        }
    }

    $lieuNaissance = strtoupper(trim($user->lieu_naissance ?? 'CONAKRY'));
    $dateNaissance = $user && $user->date_naissance ? $user->date_naissance->format('d-m-Y') : 'Non définie';
    $sexe = ($user->sexe ?? 'M') === 'F' ? 'F' : 'M';
    $classeNom = $eleve->classe->nom ?? 'Non assigné';
    $matricule = $eleve->numero_etudiant ?? $carte->numero_carte;
    $contact = $user->telephone ?? '**********';
@endphp
<div class="carte-id">
    <div class="carte-id-filigrane" aria-hidden="true">
        <span class="carte-id-filigrane-cs">{{ $filigrane }}</span>
        @if($emblemUrl)
            <img class="carte-id-filigrane-emblem" src="{{ $emblemUrl }}" alt="">
        @endif
    </div>

    <div class="carte-id-header">
        <div class="carte-id-logo">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo">
            @else
                🇬🇳
            @endif
        </div>
        <div class="carte-id-header-text">
            <p class="carte-id-country">RÉPUBLIQUE DE GUINÉE</p>
            <p class="carte-id-school">{{ $schoolNom }}</p>
            <p class="carte-id-title">CARTE D'IDENTITÉ SCOLAIRE</p>
        </div>
        <div class="carte-id-logo">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo">
            @else
                📚
            @endif
        </div>
    </div>

    <div class="carte-id-body">
        <div class="carte-id-left">
            <div class="carte-id-photo">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="Photo de l'élève">
                @else
                    <span>PHOTO</span>
                @endif
            </div>
            <div class="carte-id-photo-caption">Photo de l'élève</div>
        </div>

        <div class="carte-id-center">
            <div><strong>Année scolaire :</strong> {{ $anneeLibelle }}</div>
            <div><strong>Nom :</strong> {{ strtoupper($user->nom ?? '') }}</div>
            <div><strong>Prénom :</strong> {{ strtoupper($user->prenom ?? '') }}</div>
            <div><strong>Né(e) le :</strong> {{ $dateNaissance }}</div>
            <div><strong>À :</strong> {{ $lieuNaissance }}</div>
            <div class="carte-id-row">
                <span><strong>Sexe :</strong> {{ $sexe }}</span>
                <span><strong>Classe :</strong> {{ $classeNom }}</span>
            </div>
            <div class="carte-id-row">
                <span><strong>Contact :</strong> {{ $contact }}</span>
                <span><strong>MAT. :</strong> {{ $matricule }}</span>
            </div>
        </div>

        <div class="carte-id-qr-wrap">
            <div class="carte-id-qr">
                {!! $carte->qr_code !!}
            </div>
            <div class="carte-id-qr-caption">Scanner pour vérification</div>
        </div>
    </div>

    <div class="carte-id-footer">
        <span class="carte-id-footer-num">{{ $carte->numero_carte }}</span>
        <span class="carte-id-footer-right">
            <span class="carte-id-status is-{{ $carte->statut }}">{{ $carte->statut_libelle }}</span>
            Émise le {{ $carte->date_emission ? $carte->date_emission->format('d/m/Y') : '—' }}
        </span>
    </div>
</div>
