@extends('layouts.app')

@section('title', 'Tableau de Bord Administrateur')

@section('content')
<div class="container-fluid">
    <!-- En-tête de l'école -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white shadow-lg school-header">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            @php
                                $schoolInfo = \App\Models\Etablissement::where('actif', true)->first();
                            @endphp
                            @if($schoolInfo && $schoolInfo->logo)
                                <img src="{{ asset('storage/' . $schoolInfo->logo) }}" 
                                     alt="Logo de l'école" 
                                     class="img-fluid rounded-circle school-logo" 
                                     style="max-width: 80px; max-height: 80px;">
                            @else
                                <i class="fas fa-school fa-4x school-logo"></i>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h2 class="mb-2 fw-bold school-name">
                                {{ $schoolInfo ? $schoolInfo->nom : 'École A2School' }}
                            </h2>
                            <p class="mb-2 fs-4 fw-light school-slogan">
                                <i class="fas fa-quote-left me-2 opacity-75"></i>
                                {{ $schoolInfo ? $schoolInfo->slogan : 'Excellence et Innovation dans l\'Éducation' }}
                                <i class="fas fa-quote-right ms-2 opacity-75"></i>
                            </p>
                            <p class="mb-0 opacity-75">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                {{ $schoolInfo ? $schoolInfo->adresse : 'Adresse de l\'école' }}
                            </p>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="text-end">
                                <h4 class="mb-0">{{ now()->format('d/m/Y') }}</h4>
                                <p class="mb-0 opacity-75">{{ now()->format('H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-tachometer-alt text-primary me-2"></i>
                        Tableau de Bord Administrateur
                    </h1>
                    <p class="text-muted mb-0">Bienvenue {{ $user->nom }} {{ $user->prenom }}</p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-1"></i>
                            <span class="d-none d-sm-inline">Messages Parents</span>
                        </a>
                        <button type="button" class="btn btn-outline-secondary">
                            <i class="fas fa-download me-1"></i>
                            <span class="d-none d-sm-inline">Exporter</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques générales -->
    <div class="row mb-4 g-3">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-user-graduate fa-2x"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0">{{ $stats['eleves'] }}</h3>
                        <p class="mb-0">Élèves</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-chalkboard-teacher fa-2x"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0">{{ $stats['enseignants'] }}</h3>
                        <p class="mb-0">Enseignants</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-user-friends fa-2x"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0">{{ $stats['parents'] }}</h3>
                        <p class="mb-0">Parents</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-school fa-2x"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0">{{ $stats['classes'] }}</h3>
                        <p class="mb-0">Classes</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques financières et académiques -->
    <div class="row mb-4 g-3">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-credit-card fa-2x"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0">{{ number_format($stats['paiements_total'], 0, ',', ' ') }} GNF</h3>
                        <p class="mb-0">Paiements</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0">{{ $stats['absences_total'] }}</h3>
                        <p class="mb-0">Absences</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0">{{ $stats['notes_total'] }}</h3>
                        <p class="mb-0">Notes</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-book fa-2x"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0">{{ $stats['matieres'] }}</h3>
                        <p class="mb-0">Matières</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Derniers paiements -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Derniers paiements</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead>
                                <tr>
                                    <th>Élève</th>
                                    <th>Montant</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($derniersPaiements as $paiement)
                                <tr>
                                    <td>{{ $paiement->fraisScolarite->eleve->utilisateur->prenom ?? 'N/A' }} {{ $paiement->fraisScolarite->eleve->utilisateur->nom ?? 'N/A' }}</td>
                                    <td>{{ number_format($paiement->montant_paye, 0, ',', ' ') }} GNF</td>
                                    <td>{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                                    <td><span class="badge bg-success">{{ ucfirst($paiement->mode_paiement) }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Aucun paiement récent</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <a href="{{ route('paiements.index') }}" class="btn btn-sm btn-primary">Voir tous les paiements</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dernières absences -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Dernières absences</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead>
                                <tr>
                                    <th>Élève</th>
                                    <th>Matière</th>
                                    <th>Date</th>
                                    <th>Justifiée</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dernieresAbsences as $absence)
                                <tr>
                                    <td>{{ $absence->eleve->utilisateur->prenom }} {{ $absence->eleve->utilisateur->nom }}</td>
                                    <td>{{ $absence->matiere ? $absence->matiere->nom : 'Toutes matières' }}</td>
                                    <td>{{ $absence->date_absence->format('d/m/Y') }}</td>
                                    <td>
                                        @if($absence->statut == 'justifiee')
                                            <span class="badge bg-success">Oui</span>
                                        @else
                                            <span class="badge bg-danger">Non</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Aucune absence récente</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <a href="{{ route('absences.index') }}" class="btn btn-sm btn-primary">Voir toutes les absences</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liens rapides -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Accès rapides</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 col-6 mb-4">
                            <a href="{{ route('eleves.index') }}" class="text-decoration-none">
                                <div class="p-3 rounded bg-light-primary">
                                    <i class="fas fa-user-graduate fa-3x text-primary mb-2"></i>
                                    <h5 class="mb-0">Gestion des élèves</h5>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-4">
                            <a href="{{ route('enseignants.index') }}" class="text-decoration-none">
                                <div class="p-3 rounded bg-light-success">
                                    <i class="fas fa-chalkboard-teacher fa-3x text-success mb-2"></i>
                                    <h5 class="mb-0">Gestion des enseignants</h5>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-4">
                            <a href="{{ route('classes.index') }}" class="text-decoration-none">
                                <div class="p-3 rounded bg-light-warning">
                                    <i class="fas fa-school fa-3x text-warning mb-2"></i>
                                    <h5 class="mb-0">Gestion des classes</h5>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-4">
                            <a href="{{ route('matieres.index') }}" class="text-decoration-none">
                                <div class="p-3 rounded bg-light-info">
                                    <i class="fas fa-book fa-3x text-info mb-2"></i>
                                    <h5 class="mb-0">Gestion des matières</h5>
                                </div>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Deuxième ligne d'accès rapides -->
                    <div class="row text-center">
                        <div class="col-md-3 col-6 mb-4">
                            <a href="{{ route('admin.notifications.index') }}" class="text-decoration-none">
                                <div class="p-3 rounded bg-light-warning">
                                    <i class="fas fa-envelope fa-3x text-warning mb-2"></i>
                                    <h5 class="mb-0">Messages Parents</h5>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-4">
                            <a href="{{ route('paiements.index') }}" class="text-decoration-none">
                                <div class="p-3 rounded bg-light-success">
                                    <i class="fas fa-credit-card fa-3x text-success mb-2"></i>
                                    <h5 class="mb-0">Gestion Paiements</h5>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-4">
                            <a href="{{ route('notes.index') }}" class="text-decoration-none">
                                <div class="p-3 rounded bg-light-primary">
                                    <i class="fas fa-edit fa-3x text-primary mb-2"></i>
                                    <h5 class="mb-0">Gestion Notes</h5>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-4">
                            <a href="{{ route('etablissement.informations') }}" class="text-decoration-none">
                                <div class="p-3 rounded bg-light-secondary">
                                    <i class="fas fa-cog fa-3x text-secondary mb-2"></i>
                                    <h5 class="mb-0">Paramètres</h5>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.school-header {
    border-radius: 15px;
    overflow: hidden;
}

.school-logo {
    transition: transform 0.3s ease;
}

.school-logo:hover {
    transform: scale(1.05);
}

.school-name {
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.school-slogan {
    font-style: italic;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
}
</style>
@endpush

@section('scripts')
<script>
    // Vous pouvez ajouter des scripts spécifiques au tableau de bord ici
    // Par exemple, des graphiques avec Chart.js
</script>
@endsection