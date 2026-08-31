@php
    $carte = $carte ?? $cartes_personnel_administration;
    $personnel = $carte->personnelAdministration;
    $user = $personnel->utilisateur ?? null;
    $school = \App\Helpers\SchoolHelper::getSchoolInfo();
    $anneeScolaire = \App\Helpers\SchoolHelper::getActiveSchoolYear();

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
    $logoUrl = $resolvePublicFile($school?->logo);

    $anneeLibelle = $anneeScolaire?->nom;
    if (!$anneeLibelle) {
        $y1 = $carte->date_emission ? $carte->date_emission->format('Y') : now()->year;
        $y2 = $carte->date_expiration ? $carte->date_expiration->format('Y') : ($y1 + 1);
        $anneeLibelle = $y1 . ' - ' . $y2;
    }

    $prenom = trim($user->prenom ?? '');
    $nom = trim($user->nom ?? '');
    $nomComplet = trim($prenom . ' ' . $nom) ?: ($user->name ?? 'Personnel');
    $initiales = strtoupper(mb_substr($prenom !== '' ? $prenom : $nom, 0, 1) . mb_substr($nom, 0, 1));

    $idPersonnel = $carte->numero_carte;
    $poste = $personnel->poste ?: 'Administration';
    $email = $user->email ?? 'Non renseigné';
    $telephone = $user->telephone ?? 'Non renseigné';
    $adressePersonnel = trim((string) ($user->adresse ?? '')) ?: 'Non renseignée';
    $adresseEcole = trim((string) ($school?->adresse ?? '')) ?: 'Adresse non renseignée';
    $brandName = $school?->nom ?: 'A2School';
    $brandSub = $school?->slogan ?: 'Système de gestion scolaire';
    $signature = $school?->dg ?: $nomComplet;
@endphp
<div class="carte-ens">
    <div class="carte-ens-top">
        <div class="carte-ens-side">
            <div class="carte-ens-brand">
                <div class="carte-ens-brand-mark">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo">
                    @else
                        <i class="fas fa-graduation-cap"></i>
                    @endif
                </div>
                <div class="carte-ens-brand-text">
                    <p class="carte-ens-brand-name">{{ $brandName }}</p>
                    <p class="carte-ens-brand-sub">{{ $brandSub }}</p>
                </div>
            </div>

            <div class="carte-ens-photo">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="Photo de {{ $nomComplet }}">
                @else
                    {{ $initiales }}
                @endif
            </div>

            <div class="carte-ens-role">
                <i class="fas fa-user-tie"></i>
                Administration
            </div>
        </div>

        <div class="carte-ens-main">
            <div class="carte-ens-head">
                <div class="carte-ens-identity">
                    <p class="carte-ens-year-label">Année scolaire</p>
                    <p class="carte-ens-year">{{ $anneeLibelle }}</p>
                    <p class="carte-ens-name"><span>Prénom :</span> {{ $prenom !== '' ? $prenom : '—' }}</p>
                    <p class="carte-ens-name"><span>Nom :</span> {{ $nom !== '' ? $nom : '—' }}</p>
                    <p class="carte-ens-job">{{ $poste }}</p>
                </div>
                <div class="carte-ens-qr">
                    <div class="carte-ens-qr-box">
                        @if($carte->qr_code)
                            {!! $carte->qr_code !!}
                        @endif
                    </div>
                    <p class="carte-ens-qr-caption">Scanner pour vérifier</p>
                </div>
            </div>

            <div class="carte-ens-rows">
                <div class="carte-ens-row">
                    <i class="fas fa-id-card"></i>
                    <span>ID Admin : <strong>{{ $idPersonnel }}</strong></span>
                </div>
                <div class="carte-ens-row">
                    <i class="fas fa-briefcase"></i>
                    <span>Poste : <strong>{{ $poste }}</strong></span>
                </div>
                <div class="carte-ens-row carte-ens-row-wrap">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Adresse : <strong>{{ $adressePersonnel }}</strong></span>
                </div>
                <div class="carte-ens-row">
                    <i class="fas fa-envelope"></i>
                    <span>Email : <strong>{{ $email }}</strong></span>
                </div>
                <div class="carte-ens-row">
                    <i class="fas fa-phone"></i>
                    <span>Téléphone : <strong>{{ $telephone }}</strong></span>
                </div>
            </div>

            <div class="carte-ens-bottom">
                <div class="carte-ens-notice">
                    <i class="fas fa-shield-alt"></i>
                    <span>Cette carte est strictement personnelle et doit être présentée sur demande.</span>
                </div>
                <div class="carte-ens-sign">
                    <p class="carte-ens-sign-name">{{ $signature }}</p>
                    <hr class="carte-ens-sign-line">
                    <p class="carte-ens-sign-label">Signature Autorisée</p>
                </div>
            </div>
        </div>
    </div>

    <div class="carte-ens-footer">
        <span>
            <i class="fas fa-map-marker-alt"></i>
            {{ $adresseEcole }}
        </span>
        <span>
            <i class="fas fa-calendar-alt"></i>
            Valide jusqu’au {{ $carte->date_expiration ? $carte->date_expiration->format('d/m/Y') : '—' }}
        </span>
    </div>
</div>
