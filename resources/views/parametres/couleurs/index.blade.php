@extends('layouts.app')

@section('title', 'Personnalisation des couleurs')

@php
    $all = array_merge(
        $couleurs['general'] ?? [],
        $couleurs['bulletin'] ?? [],
        $couleurs['resultat'] ?? [],
        $couleurs['document'] ?? []
    );
    $val = fn (string $cle, string $def) => $all[$cle] ?? $def;

    $groupes = [
        [
            'titre' => 'Couleurs générales',
            'icon' => 'fas fa-cog',
            'items' => [
                ['cle' => 'header_bg', 'label' => "Couleur d'en-tête", 'defaut' => '#34495e'],
                ['cle' => 'header_text', 'label' => "Texte d'en-tête", 'defaut' => '#ffffff'],
                ['cle' => 'primary_color', 'label' => 'Couleur principale', 'defaut' => '#007bff'],
                ['cle' => 'secondary_color', 'label' => 'Couleur secondaire', 'defaut' => '#6c757d'],
                ['cle' => 'success_color', 'label' => 'Couleur de succès', 'defaut' => '#28a745'],
                ['cle' => 'danger_color', 'label' => 'Couleur de danger', 'defaut' => '#dc3545'],
            ],
        ],
        [
            'titre' => 'Bulletins',
            'icon' => 'fas fa-file-alt',
            'items' => [
                ['cle' => 'bulletin_header_bg', 'label' => 'En-tête du bulletin', 'defaut' => '#34495e'],
                ['cle' => 'bulletin_table_header_bg', 'label' => 'En-tête des tableaux', 'defaut' => '#34495e'],
                ['cle' => 'bulletin_table_border', 'label' => 'Bordures des tableaux', 'defaut' => '#2c3e50'],
                ['cle' => 'bulletin_success_text', 'label' => 'Texte de succès', 'defaut' => '#28a745'],
                ['cle' => 'bulletin_danger_text', 'label' => 'Texte de danger', 'defaut' => '#dc3545'],
            ],
        ],
        [
            'titre' => 'Résultats',
            'icon' => 'fas fa-chart-bar',
            'items' => [
                ['cle' => 'resultat_header_bg', 'label' => 'En-tête des résultats', 'defaut' => '#34495e'],
                ['cle' => 'resultat_moyenne_text', 'label' => 'Texte des moyennes', 'defaut' => '#28a745'],
                ['cle' => 'resultat_rang_text', 'label' => 'Texte des rangs', 'defaut' => '#007bff'],
            ],
        ],
        [
            'titre' => 'Documents',
            'icon' => 'fas fa-file',
            'items' => [
                ['cle' => 'document_header_bg', 'label' => 'En-tête des documents', 'defaut' => '#34495e'],
                ['cle' => 'document_title_bg', 'label' => 'Titres des documents', 'defaut' => '#6c757d'],
                ['cle' => 'document_border', 'label' => 'Bordures des documents', 'defaut' => '#2c3e50'],
            ],
        ],
    ];
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-palette me-2"></i>
        Personnalisation des couleurs
    </h1>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ $errors->first() }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('parametres.couleurs.update') }}" method="POST" id="form-couleurs">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 1rem;">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Aperçu</h5>
                </div>
                <div class="card-body p-0">
                    <div id="preview-header" class="px-3 py-3" style="background: {{ $val('header_bg', '#34495e') }}; color: {{ $val('header_text', '#ffffff') }};">
                        <div class="fw-bold">En-tête de l’application</div>
                        <div class="small opacity-75">Nom de l’école</div>
                    </div>
                    <div class="p-3">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span id="preview-primary" class="badge" style="background: {{ $val('primary_color', '#007bff') }};">Principal</span>
                            <span id="preview-secondary" class="badge" style="background: {{ $val('secondary_color', '#6c757d') }};">Secondaire</span>
                            <span id="preview-success" class="badge" style="background: {{ $val('success_color', '#28a745') }};">Succès</span>
                            <span id="preview-danger" class="badge" style="background: {{ $val('danger_color', '#dc3545') }};">Danger</span>
                        </div>
                        <div id="preview-bulletin" class="border rounded overflow-hidden mb-2" style="border-color: {{ $val('bulletin_table_border', '#2c3e50') }} !important;">
                            <div id="preview-bulletin-head" class="px-2 py-1 small text-white" style="background: {{ $val('bulletin_header_bg', '#34495e') }};">Bulletin</div>
                            <div class="px-2 py-2 small">
                                <span id="preview-bulletin-ok" style="color: {{ $val('bulletin_success_text', '#28a745') }};">Réussi</span>
                                &nbsp;·&nbsp;
                                <span id="preview-bulletin-ko" style="color: {{ $val('bulletin_danger_text', '#dc3545') }};">Échec</span>
                            </div>
                        </div>
                        <div class="small text-muted">Les changements s’affichent ici avant l’enregistrement.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            @foreach($groupes as $groupe)
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="{{ $groupe['icon'] }} me-2 text-primary"></i>
                        {{ $groupe['titre'] }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($groupe['items'] as $item)
                        @php $hex = strtoupper($val($item['cle'], $item['defaut'])); @endphp
                        <div class="col-md-6">
                            <label for="{{ $item['cle'] }}" class="form-label">{{ $item['label'] }}</label>
                            <div class="input-group color-field">
                                <input type="color"
                                       class="form-control form-control-color color-picker"
                                       id="{{ $item['cle'] }}"
                                       name="couleurs[{{ $item['cle'] }}]"
                                       value="{{ $hex }}"
                                       data-preview="{{ $item['cle'] }}">
                                <input type="text"
                                       class="form-control color-hex"
                                       value="{{ $hex }}"
                                       maxlength="7"
                                       spellcheck="false"
                                       aria-label="Code hexadécimal {{ $item['label'] }}">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach

            <div class="d-flex justify-content-between flex-wrap gap-2">
                <button type="submit" form="form-reset-couleurs" class="btn btn-outline-warning"
                        onclick="return confirm('Réinitialiser toutes les couleurs aux valeurs par défaut ?')">
                    <i class="fas fa-undo me-1"></i>
                    Réinitialiser
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>
                    Enregistrer
                </button>
            </div>
        </div>
    </div>
</form>

<form id="form-reset-couleurs" action="{{ route('parametres.couleurs.reset') }}" method="POST" class="d-none">
    @csrf
</form>
@endsection

@push('styles')
<style>
    .color-field .form-control-color {
        width: 52px;
        min-width: 52px;
        height: 38px;
        padding: 4px;
        cursor: pointer;
    }
    .color-field .color-hex {
        font-family: Consolas, Monaco, monospace;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const preview = {
        header_bg: (v) => { const el = document.getElementById('preview-header'); if (el) el.style.background = v; },
        header_text: (v) => { const el = document.getElementById('preview-header'); if (el) el.style.color = v; },
        primary_color: (v) => { const el = document.getElementById('preview-primary'); if (el) el.style.background = v; },
        secondary_color: (v) => { const el = document.getElementById('preview-secondary'); if (el) el.style.background = v; },
        success_color: (v) => { const el = document.getElementById('preview-success'); if (el) el.style.background = v; },
        danger_color: (v) => { const el = document.getElementById('preview-danger'); if (el) el.style.background = v; },
        bulletin_header_bg: (v) => { const el = document.getElementById('preview-bulletin-head'); if (el) el.style.background = v; },
        bulletin_table_border: (v) => { const el = document.getElementById('preview-bulletin'); if (el) el.style.borderColor = v; },
        bulletin_success_text: (v) => { const el = document.getElementById('preview-bulletin-ok'); if (el) el.style.color = v; },
        bulletin_danger_text: (v) => { const el = document.getElementById('preview-bulletin-ko'); if (el) el.style.color = v; },
    };

    function applyPreview(key, value) {
        if (preview[key]) preview[key](value);
    }

    document.querySelectorAll('.color-field').forEach(function (field) {
        const picker = field.querySelector('.color-picker');
        const hex = field.querySelector('.color-hex');
        if (!picker || !hex) return;

        picker.addEventListener('input', function () {
            hex.value = this.value.toUpperCase();
            applyPreview(this.dataset.preview, this.value);
        });

        hex.addEventListener('input', function () {
            let value = this.value.trim();
            if (value && value.charAt(0) !== '#') value = '#' + value;
            if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                picker.value = value;
                this.value = value.toUpperCase();
                applyPreview(picker.dataset.preview, value);
            }
        });
    });
});
</script>
@endpush
