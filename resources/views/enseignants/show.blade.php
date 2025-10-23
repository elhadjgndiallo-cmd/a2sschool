@extends('layouts.app')

@section('title', 'Détails Enseignant')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-circle me-2"></i>
        Détails de l'Enseignant
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('enseignants.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
        <a href="{{ route('enseignants.edit', $enseignant->id) }}" class="btn btn-outline-primary ms-2">
            <i class="fas fa-edit me-1"></i>
            Modifier
        </a>
        <button type="button" class="btn btn-info ms-2 no-print" onclick="window.print()">
            <i class="fas fa-print me-1"></i>
            Imprimer (A4)
        </button>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@php
    $photo = $enseignant->utilisateur->photo_profil ?? null;
    $schoolInfo = \App\Helpers\SchoolHelper::getSchoolInfo();
@endphp

<!-- Fiche d'impression compacte (une seule page A4) -->
<div class="fiche-impression d-none d-print-block">
    <div style="width:100%;">
        <!-- En-tête avec logo et nom de l'école -->
        <div style="text-align:center; margin-bottom:16px; border-bottom:2px solid #333; padding-bottom:8px;">
            <div style="display:flex; align-items:center; justify-content:center; gap:12px; margin-bottom:8px;">
                @if($schoolInfo['logo_url'])
                    <img src="{{ $schoolInfo['logo_url'] }}" alt="Logo" style="height:50px; max-width:120px; object-fit:contain;">
                @endif
                <div>
                    <h2 style="margin:0; font-size:20px; font-weight:bold;">{{ $schoolInfo['school_name'] }}</h2>
                    @if($schoolInfo['school_slogan'])
                        <p style="margin:2px 0 0 0; font-size:12px; color:#666; font-style:italic;">{{ $schoolInfo['school_slogan'] }}</p>
                    @endif
                </div>
            </div>
            <h3 style="margin:0; font-size:16px; color:#333;">Fiche Enseignant</h3>
        </div>
        <div style="display:flex; gap:16px; align-items:flex-start;">
            <div style="flex:0 0 120px; text-align:center;">
                <div style="width:110px; height:110px; border:1px solid #ccc; border-radius:6px; overflow:hidden; display:inline-block;">
                    @if($photo && Storage::disk('public')->exists($photo))
                        <img src="{{ asset('storage/' . $photo) }}" alt="Photo" style="width:110px; height:110px; object-fit:cover;">
                    @else
                        <img src="{{ asset('images/default-avatar.png') }}" alt="Photo" style="width:110px; height:110px; object-fit:cover;">
                    @endif
                </div>
            </div>
            <div style="flex:1 1 auto;">
                <div style="display:grid; grid-template-columns:220px 1fr; column-gap:8px; row-gap:6px; font-size:14px;">
                    <div><strong>Numéro Employé:</strong></div><div>{{ $enseignant->numero_employe ?? 'N/A' }}</div>
                    <div><strong>Nom:</strong></div><div>{{ $enseignant->utilisateur->nom ?? 'N/A' }}</div>
                    <div><strong>Prénom:</strong></div><div>{{ $enseignant->utilisateur->prenom ?? 'N/A' }}</div>
                    <div><strong>Email:</strong></div><div>{{ $enseignant->utilisateur->email ?? 'N/A' }}</div>
                    <div><strong>Téléphone:</strong></div><div>{{ $enseignant->utilisateur->telephone ?? 'N/A' }}</div>
                    <div><strong>Adresse:</strong></div><div>{{ $enseignant->utilisateur->adresse ?? 'N/A' }}</div>
                    <div><strong>Date de naissance:</strong></div><div>
                        @if($enseignant->utilisateur->date_naissance)
                            {{ \Carbon\Carbon::parse($enseignant->utilisateur->date_naissance)->format('d/m/Y') }}
                        @else
                            N/A
                        @endif
                    </div>
                    <div><strong>Lieu de naissance:</strong></div><div>{{ $enseignant->utilisateur->lieu_naissance ?? 'N/A' }}</div>
                    <div><strong>Sexe:</strong></div><div>
                        @if($enseignant->utilisateur->sexe)
                            {{ $enseignant->utilisateur->sexe == 'M' ? 'Masculin' : 'Féminin' }}
                        @else
                            N/A
                        @endif
                    </div>
                    <div><strong>Spécialité:</strong></div><div>{{ $enseignant->specialite ?? 'N/A' }}</div>
                    <div><strong>Diplôme:</strong></div><div>{{ $enseignant->diplome ?? 'N/A' }}</div>
                    <div><strong>Date d'embauche:</strong></div><div>
                        @if($enseignant->date_embauche)
                            {{ \Carbon\Carbon::parse($enseignant->date_embauche)->format('d/m/Y') }}
                        @else
                            N/A
                        @endif
                    </div>
                    <div><strong>Statut:</strong></div><div>{{ ucfirst($enseignant->statut ?? 'N/A') }}</div>
                    <div><strong>Mot de passe enseignant:</strong></div><div>password1234</div>
                </div>
                <div style="margin-top:10px;">
                    <div><strong>Matières enseignées:</strong></div>
                    @if($enseignant->matieres && $enseignant->matieres->count() > 0)
                        <ul style="margin:4px 0 0 16px; padding:0;">
                            @foreach($enseignant->matieres as $matiere)
                                <li style="margin:2px 0;">{{ $matiere->nom ?? 'N/A' }} @if(!empty($matiere->code))<span style="color:#666;">({{ $matiere->code }})</span>@endif</li>
                            @endforeach
                        </ul>
                    @else
                        <div>N/A</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- Pied de page avec adresse et téléphone -->
    <div style="margin-top:16px; padding-top:8px; border-top:1px solid #ccc; text-align:center; font-size:12px; color:#666;">
        <div style="margin-bottom:4px;"><strong>{{ $schoolInfo['school_name'] }}</strong></div>
        <div style="margin-bottom:2px;">{{ $schoolInfo['school_address'] }}</div>
        @if($schoolInfo['school_phone'])
            <div>Téléphone: {{ $schoolInfo['school_phone'] }}</div>
        @endif
        @if($schoolInfo['school_email'])
            <div>Email: {{ $schoolInfo['school_email'] }}</div>
        @endif
    </div>
</div>
<!-- Mot de passe par défaut pour impression et rappel -->
<div class="alert alert-info py-2 px-3 mb-3">
    <i class="fas fa-key me-2"></i>
    Mot de passe par défaut de l'enseignant: <strong>password1234</strong>
    <span class="text-muted">(à modifier après première connexion)</span>
    <span class="d-inline d-print-inline ms-2"><i class="fas fa-info-circle me-1"></i>Cette information sera imprimée.</span>
    <span class="d-inline d-print-none ms-2">Cette information sera incluse lors de l'impression.</span>
</div>

<div class="row">
    <!-- Informations personnelles -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Informations Personnelles</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12 text-center mb-4">
                        @if($enseignant->utilisateur->photo_profil && Storage::disk('public')->exists($enseignant->utilisateur->photo_profil))
                            <div class="mb-3">
                                <img src="{{ asset('storage/' . $enseignant->utilisateur->photo_profil) }}" alt="Photo de profil" class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                <form action="{{ route('enseignants.delete-photo', $enseignant->id) }}" method="POST" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette photo de profil ?')">
                                        <i class="fas fa-trash-alt me-1"></i> Supprimer la photo
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="avatar-lg mx-auto">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 100px; height: 100px; font-size: 2.5rem;">
                                    {{ substr($enseignant->utilisateur->prenom, 0, 1) }}{{ substr($enseignant->utilisateur->nom, 0, 1) }}
                                </div>
                            </div>
                        @endif
                        <h4 class="mt-3">{{ $enseignant->utilisateur->nom }} {{ $enseignant->utilisateur->prenom }}</h4>
                        <p class="text-muted">
                            <span class="badge bg-{{ $enseignant->actif ? 'success' : 'danger' }}">
                                {{ $enseignant->actif ? 'Actif' : 'Inactif' }}
                            </span>
                            <span class="badge bg-info ms-2">{{ ucfirst($enseignant->statut) }}</span>
                        </p>
                    </div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Numéro Employé:</div>
                    <div class="col-md-8">{{ $enseignant->numero_employe }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Email:</div>
                    <div class="col-md-8">{{ $enseignant->utilisateur->email }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Téléphone:</div>
                    <div class="col-md-8">{{ $enseignant->utilisateur->telephone }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Adresse:</div>
                    <div class="col-md-8">{{ $enseignant->utilisateur->adresse }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Date de naissance:</div>
                    <div class="col-md-8">
                        @if($enseignant->utilisateur->date_naissance)
                            {{ \Carbon\Carbon::parse($enseignant->utilisateur->date_naissance)->format('d/m/Y') }}
                        @else
                            <span class="text-muted">Non renseignée</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Lieu de naissance:</div>
                    <div class="col-md-8">
                        @if($enseignant->utilisateur->lieu_naissance)
                            {{ $enseignant->utilisateur->lieu_naissance }}
                        @else
                            <span class="text-muted">Non renseigné</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Sexe:</div>
                    <div class="col-md-8">
                        @if($enseignant->utilisateur->sexe)
                            {{ $enseignant->utilisateur->sexe == 'M' ? 'Masculin' : 'Féminin' }}
                        @else
                            <span class="text-muted">Non renseigné</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Informations professionnelles -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Informations Professionnelles</h5>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Spécialité:</div>
                    <div class="col-md-8">
                        @if($enseignant->specialite)
                            {{ $enseignant->specialite }}
                        @else
                            <span class="text-muted">Non renseignée</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Diplôme:</div>
                    <div class="col-md-8">
                        @if($enseignant->diplome)
                            {{ $enseignant->diplome }}
                        @else
                            <span class="text-muted">Non renseigné</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Date d'embauche:</div>
                    <div class="col-md-8">
                        @if($enseignant->date_embauche)
                            {{ \Carbon\Carbon::parse($enseignant->date_embauche)->format('d/m/Y') }}
                        @else
                            <span class="text-muted">Non renseignée</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Statut:</div>
                    <div class="col-md-8">
                        <span class="badge bg-info">{{ ucfirst($enseignant->statut) }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Matières enseignées -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Matières Enseignées</h5>
            </div>
            <div class="card-body">
                @if($enseignant->matieres->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Code</th>
                                <th>Coefficient</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enseignant->matieres as $matiere)
                            <tr>
                                <td>{{ $matiere->nom ?? 'N/A' }}</td>
                                <td>{{ $matiere->code ?? 'N/A' }}</td>
                                <td>{{ $matiere->coefficient ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center">
                    <i class="fas fa-info-circle me-1"></i>
                    Aucune matière assignée à cet enseignant.
                </p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-book fa-2x text-primary mb-2"></i>
                <h5 class="card-title">{{ $enseignant->matieres->count() }}</h5>
                <p class="card-text">Matières</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                <h5 class="card-title">{{ $enseignant->emploisTemps->count() }}</h5>
                <p class="card-text">Cours</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-graduation-cap fa-2x text-success mb-2"></i>
                <h5 class="card-title">{{ $enseignant->notes->count() }}</h5>
                <p class="card-text">Notes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-calendar-alt fa-2x text-info mb-2"></i>
                <h5 class="card-title">
                    @if($enseignant->date_embauche)
                        {{ \Carbon\Carbon::parse($enseignant->date_embauche)->diffInYears(now()) }} ans
                    @else
                        N/A
                    @endif
                </h5>
                <p class="card-text">Ancienneté</p>
            </div>
        </div>
    </div>
</div>

<!-- Emploi du temps -->
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Emploi du Temps</h5>
    </div>
    <div class="card-body">
        @if($enseignant->emploisTemps->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Jour</th>
                        <th>Heure Début</th>
                        <th>Heure Fin</th>
                        <th>Matière</th>
                        <th>Classe</th>
                        <th>Salle</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enseignant->emploisTemps as $emploi)
                    <tr>
                        <td>{{ ucfirst($emploi->jour_semaine ?? 'N/A') }}</td>
                        <td>{{ $emploi->heure_debut ?? 'N/A' }}</td>
                        <td>{{ $emploi->heure_fin ?? 'N/A' }}</td>
                        <td>{{ $emploi->matiere->nom ?? 'N/A' }}</td>
                        <td>{{ $emploi->classe->nom ?? 'N/A' }}</td>
                        <td>{{ $emploi->salle ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted text-center">
            <i class="fas fa-info-circle me-1"></i>
            Aucun emploi du temps défini pour cet enseignant.
        </p>
        @endif
    </div>
</div>
@push('styles')
<style>
/* Styles d'impression A4 pour la page Enseignant */
@media print {
    @page { size: A4 portrait; margin: 12mm; }
    .no-print, .top-navbar, .sidebar, .btn, .btn-toolbar, .navbar, .sidebar-overlay { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    .container-fluid { padding: 0 !important; }
    /* N'afficher que la fiche d'impression */
    .main-content .container-fluid > *:not(.fiche-impression) { display: none !important; }
    .fiche-impression { display: block !important; }
    /* Nettoyage visuel */
    .card, .card-header { box-shadow: none !important; border: none !important; background: transparent !important; }
}
</style>
@endpush
@endsection