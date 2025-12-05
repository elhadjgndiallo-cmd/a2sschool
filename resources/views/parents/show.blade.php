@extends('layouts.app')

@section('title', 'Détails du Parent - ' . $parent->utilisateur->prenom . ' ' . $parent->utilisateur->nom)

@section('content')
<style>
    /* Amélioration de l'affichage des numéros de téléphone sur mobile/tablette */
    .phone-link {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }
    
    .phone-link:hover {
        background-color: #f0f0f0;
        transform: scale(1.05);
    }
    
    .phone-number {
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    /* Sur mobile et tablette */
    @media (max-width: 768px) {
        .phone-number {
            font-size: 18px !important;
            font-weight: 700 !important;
            color: #0d6efd !important;
            letter-spacing: 1px;
        }
        
        .phone-link {
            padding: 10px 16px;
            background-color: #e7f3ff;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            min-height: 48px; /* Taille minimale pour faciliter le clic sur mobile */
            margin: 4px 0;
        }
        
        .phone-link i {
            font-size: 20px;
            color: #0d6efd;
            margin-right: 10px;
        }
        
        .phone-link:active {
            background-color: #b3d9ff;
            transform: scale(0.98);
        }
    }
    
    /* Sur très petits écrans */
    @media (max-width: 576px) {
        .phone-number {
            font-size: 20px !important;
            font-weight: 700 !important;
        }
        
        .phone-link {
            padding: 12px 18px;
            width: 100%;
            justify-content: center;
            min-height: 52px;
        }
        
        .phone-link i {
            font-size: 22px;
        }
    }
</style>
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-user me-2"></i>
            Détails du Parent - {{ $parent->utilisateur->prenom }} {{ $parent->utilisateur->nom }}
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('parents.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour à la liste
            </a>
        </div>
    </div>

    <!-- Informations personnelles -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle me-2"></i>
                        Informations personnelles
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Nom complet:</th>
                            <td><strong>{{ $parent->utilisateur->prenom }} {{ $parent->utilisateur->nom }}</strong></td>
                        </tr>
                        <tr>
                            <th>Téléphone:</th>
                            <td>
                                @if($parent->utilisateur->telephone)
                                    <a href="tel:{{ $parent->utilisateur->telephone }}" class="phone-link text-decoration-none">
                                        <i class="fas fa-phone me-1"></i>
                                        <span class="phone-number">{{ $parent->utilisateur->telephone }}</span>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>
                                @if($parent->utilisateur->email)
                                    <a href="mailto:{{ $parent->utilisateur->email }}" class="text-decoration-none">
                                        <i class="fas fa-envelope me-1"></i>{{ $parent->utilisateur->email }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Adresse:</th>
                            <td>{{ $parent->utilisateur->adresse ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Date de naissance:</th>
                            <td>
                                @if($parent->utilisateur->date_naissance)
                                    {{ \Carbon\Carbon::parse($parent->utilisateur->date_naissance)->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Sexe:</th>
                            <td>
                                @if($parent->utilisateur->sexe == 'M')
                                    <span class="badge bg-primary">Masculin</span>
                                @elseif($parent->utilisateur->sexe == 'F')
                                    <span class="badge bg-pink">Féminin</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-briefcase me-2"></i>
                        Informations professionnelles
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Profession:</th>
                            <td>{{ $parent->profession ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Employeur:</th>
                            <td>{{ $parent->employeur ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Téléphone travail:</th>
                            <td>
                                @if($parent->telephone_travail)
                                    <a href="tel:{{ $parent->telephone_travail }}" class="phone-link text-decoration-none">
                                        <i class="fas fa-phone me-1"></i>
                                        <span class="phone-number">{{ $parent->telephone_travail }}</span>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Lien de parenté:</th>
                            <td>
                                @if($parent->lien_parente == 'pere')
                                    <span class="badge bg-primary">Père</span>
                                @elseif($parent->lien_parente == 'mere')
                                    <span class="badge bg-pink">Mère</span>
                                @elseif($parent->lien_parente == 'tuteur')
                                    <span class="badge bg-info">Tuteur</span>
                                @else
                                    <span class="badge bg-secondary">Autre</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Contact urgence:</th>
                            <td>
                                @if($parent->contact_urgence)
                                    <span class="badge bg-danger">Oui</span>
                                @else
                                    <span class="badge bg-secondary">Non</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Statut:</th>
                            <td>
                                @if($parent->actif)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-danger">Inactif</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des enfants -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-user-graduate me-2"></i>
                Enfants ({{ $parent->eleves->count() }})
            </h5>
        </div>
        <div class="card-body p-0">
            @if($parent->eleves->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Matricule</th>
                            <th scope="col">Nom</th>
                            <th scope="col">Prénom</th>
                            <th scope="col">Classe</th>
                            <th scope="col">Lien</th>
                            <th scope="col" class="text-center">Responsable légal</th>
                            <th scope="col" class="text-center">Contact urgence</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parent->eleves as $eleve)
                        @php
                            $pivot = $eleve->pivot;
                        @endphp
                        <tr>
                            <td><strong>{{ $eleve->numero_etudiant }}</strong></td>
                            <td>{{ $eleve->utilisateur->nom }}</td>
                            <td>{{ $eleve->utilisateur->prenom }}</td>
                            <td>
                                @if($eleve->classe)
                                    <span class="badge bg-info">{{ $eleve->classe->nom }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($pivot->lien_parente == 'pere')
                                    <span class="badge bg-primary">Père</span>
                                @elseif($pivot->lien_parente == 'mere')
                                    <span class="badge bg-pink">Mère</span>
                                @elseif($pivot->lien_parente == 'tuteur')
                                    <span class="badge bg-info">Tuteur</span>
                                @else
                                    <span class="badge bg-secondary">{{ $pivot->autre_lien_parente ?? 'Autre' }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($pivot->responsable_legal)
                                    <span class="badge bg-success">Oui</span>
                                @else
                                    <span class="badge bg-secondary">Non</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($pivot->contact_urgence)
                                    <span class="badge bg-danger">Oui</span>
                                @else
                                    <span class="badge bg-secondary">Non</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('eleves.show', $eleve->id) }}" 
                                   class="btn btn-sm btn-outline-info" 
                                   title="Voir les détails de l'élève">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucun enfant</h5>
                <p class="text-muted">Ce parent n'a pas d'enfant associé.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

