@extends('layouts.app')

@section('title', 'Importer des élèves')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2><i class="fas fa-file-import me-2"></i>Importer des élèves</h2>
            <p class="text-muted mb-0">
                Année scolaire active :
                <strong>{{ $anneeScolaire->nom ?? '—' }}</strong>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('eleves.import.modele') }}" class="btn btn-outline-success me-1">
                <i class="fas fa-download me-1"></i> Modèle Excel
            </a>
            <a href="{{ route('eleves.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="alert alert-info">
        <strong>Important :</strong> sélectionnez d'abord la classe cible. Le fichier ne contient pas de colonne « classe ».
        Chaque élève importé recevra automatiquement les frais d'inscription et de scolarité selon le tarif de la classe.
    </div>

    @if(empty($preview))
        <div class="card mb-4">
            <div class="card-header"><strong>1. Choisir la classe et le fichier</strong></div>
            <div class="card-body">
                <form action="{{ route('eleves.import.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="classe_id" class="form-label">Classe cible <span class="text-danger">*</span></label>
                            <select name="classe_id" id="classe_id" class="form-select @error('classe_id') is-invalid @enderror" required>
                                <option value="">— Sélectionner —</option>
                                @foreach($classes as $classe)
                                    <option value="{{ $classe->id }}" {{ old('classe_id') == $classe->id ? 'selected' : '' }}>
                                        {{ $classe->nom }}
                                        @if($classe->effectif_max)
                                            ({{ $classe->effectif_actuel ?? 0 }}/{{ $classe->effectif_max }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('classe_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-5">
                            <label for="fichier" class="form-label">Fichier Excel ou CSV <span class="text-danger">*</span></label>
                            <input type="file" name="fichier" id="fichier"
                                   class="form-control @error('fichier') is-invalid @enderror"
                                   accept=".xlsx,.csv" required>
                            @error('fichier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Formats acceptés : .xlsx, .csv (max 5 Mo)</div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i> Analyser le fichier
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>Colonnes du fichier</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-danger">Obligatoires</h6>
                        <ul class="small mb-0">
                            <li><code>prenom</code>, <code>nom</code></li>
                            <li><code>sexe</code> — <strong>F</strong> (Féminin) ou <strong>H</strong> (Homme)</li>
                            <li><code>type_inscription</code> — <strong>1</strong> (Inscription) ou <strong>2</strong> (Réinscription)</li>
                        </ul>
                        <p class="small text-muted mt-2 mb-0">
                            Automatiques (sans colonne) : date d'inscription = aujourd'hui, statut = actif.
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Optionnelles</h6>
                        <ul class="small mb-0">
                            <li><code>numero_etudiant</code> (auto si vide)</li>
                            <li><code>date_naissance</code>, <code>lieu_naissance</code></li>
                            <li><code>telephone</code>, <code>adresse</code></li>
                            <li><code>situation_matrimoniale</code>, <code>ecole_origine</code></li>
                            <li><code>exempte_frais</code>, <code>paiement_annuel</code></li>
                            <li><code>gratuit_inscription</code>, <code>gratuit_reinscription</code></li>
                            <li><code>nom_parent</code>, <code>prenom_parent</code> — si les deux sont remplis, le parent est créé (ou réutilisé)</li>
                            <li><code>telephone_parent</code> — optionnel ; si déjà utilisé, le parent existant est lié</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @else
        @php
            $classeSelectionnee = $classes->firstWhere('id', $preview['classe_id']);
        @endphp

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <strong>Fichier :</strong> {{ $preview['filename'] ?? '—' }}<br>
                        <strong>Classe :</strong> {{ $classeSelectionnee->nom ?? '—' }}
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success">{{ $preview['valid_count'] ?? 0 }} valide(s)</span>
                        <span class="badge bg-warning text-dark">{{ $preview['warning_count'] ?? 0 }} avertissement(s)</span>
                        <span class="badge bg-danger">{{ $preview['error_count'] ?? 0 }} erreur(s)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><strong>2. Prévisualisation</strong></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ligne</th>
                                <th>Statut</th>
                                <th>Matricule</th>
                                <th>Prénom</th>
                                <th>Nom</th>
                                <th>Sexe</th>
                                <th>Type inscr.</th>
                                <th>Statut</th>
                                <th>Parent</th>
                                <th>Messages</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($preview['rows'] as $row)
                                <tr class="@if($row['status'] === 'error') table-danger @elseif($row['status'] === 'warning') table-warning @endif">
                                    <td>{{ $row['line'] ?: '—' }}</td>
                                    <td>
                                        @if($row['status'] === 'ok')
                                            <span class="badge bg-success">OK</span>
                                        @elseif($row['status'] === 'warning')
                                            <span class="badge bg-warning text-dark">Alerte</span>
                                        @else
                                            <span class="badge bg-danger">Erreur</span>
                                        @endif
                                    </td>
                                    <td>{{ $row['display']['numero_etudiant'] ?? '—' }}</td>
                                    <td>{{ $row['display']['prenom'] ?? '—' }}</td>
                                    <td>{{ $row['display']['nom'] ?? '—' }}</td>
                                    <td>{{ $row['display']['sexe'] ?? '—' }}</td>
                                    <td>{{ $row['display']['type_inscription'] ?? '—' }}</td>
                                    <td>{{ $row['display']['statut'] ?? '—' }}</td>
                                    <td class="small">{{ $row['display']['parent'] ?? '—' }}</td>
                                    <td class="small">
                                        @if(!empty($row['messages']))
                                            {{ implode(' | ', $row['messages']) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>3. Confirmer l'import</strong></div>
            <div class="card-body d-flex flex-wrap gap-2">
                @if(($preview['error_count'] ?? 0) === 0 && ($preview['importable_count'] ?? 0) > 0)
                    <form method="POST" action="{{ route('eleves.import.confirm') }}">
                        @csrf
                        <button type="submit" class="btn btn-success"
                                onclick="return confirm('Confirmer l\'import de {{ $preview['importable_count'] }} élève(s) dans la classe {{ $classeSelectionnee->nom ?? '' }} ?');">
                            <i class="fas fa-check me-1"></i>
                            Importer {{ $preview['importable_count'] }} élève(s)
                        </button>
                    </form>
                @else
                    <button type="button" class="btn btn-success" disabled>
                        <i class="fas fa-check me-1"></i> Import impossible
                    </button>
                @endif

                <form method="POST" action="{{ route('eleves.import.cancel') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Annuler et recommencer
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
