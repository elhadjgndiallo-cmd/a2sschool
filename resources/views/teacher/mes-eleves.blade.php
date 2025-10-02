@extends('layouts.app')

@section('title', 'Mes Élèves')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-users me-2"></i>
        Mes Élèves
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour au Dashboard
        </a>
    </div>
</div>

<!-- Messages de session -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-users fa-2x mb-2"></i>
                <h4>{{ $eleves->count() }}</h4>
                <p class="mb-0">Total Élèves</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-graduation-cap fa-2x mb-2"></i>
                <h4>{{ $classes->count() }}</h4>
                <p class="mb-0">Classes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-book fa-2x mb-2"></i>
                <h4>{{ auth()->user()->enseignant->matieres->count() }}</h4>
                <p class="mb-0">Matières</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <i class="fas fa-calendar fa-2x mb-2"></i>
                <h4>{{ now()->format('d/m/Y') }}</h4>
                <p class="mb-0">Aujourd'hui</p>
            </div>
        </div>
    </div>
</div>

<!-- Actions rapides -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Actions Rapides</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('teacher.absences') }}" class="btn btn-warning btn-block">
                            <i class="fas fa-user-times me-2"></i>
                            Saisir Absences
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('notes.index') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-edit me-2"></i>
                            Saisir Notes
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('teacher.emploi-temps') }}" class="btn btn-info btn-block">
                            <i class="fas fa-calendar me-2"></i>
                            Emploi du Temps
                        </a>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-success btn-block" onclick="exporterEleves()">
                            <i class="fas fa-download me-2"></i>
                            Exporter Liste
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Liste des élèves -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Liste de mes élèves ({{ $eleves->count() }} élèves)
        </h5>
    </div>
    <div class="card-body">
        @if($eleves->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover" id="elevesTable">
                    <thead class="table-dark">
                        <tr>
                            <th width="10%">Photo</th>
                            <th width="15%">Matricule</th>
                            <th width="20%">Nom</th>
                            <th width="20%">Prénom</th>
                            <th width="20%">Classe</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($eleves as $eleve)
                        <tr>
                            <td class="text-center">
                                @if($eleve->utilisateur->photo_profil)
                                    <img src="{{ Storage::url($eleve->utilisateur->photo_profil) }}" 
                                         alt="Photo de {{ $eleve->utilisateur->prenom }} {{ $eleve->utilisateur->nom }}" 
                                         class="rounded-circle" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $eleve->numero_etudiant }}</strong>
                            </td>
                            <td>
                                <strong>{{ $eleve->utilisateur->nom }}</strong>
                            </td>
                            <td>
                                <strong>{{ $eleve->utilisateur->prenom }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $eleve->classe->nom ?? 'Non assigné' }}</span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            onclick="voirProfil({{ $eleve->id }})" 
                                            title="Voir le profil">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning" 
                                            onclick="marquerAbsence({{ $eleve->id }})" 
                                            title="Marquer absence">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="saisirNote({{ $eleve->id }})" 
                                            title="Saisir note">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center text-muted py-5">
                <i class="fas fa-users fa-3x mb-3"></i>
                <h5>Aucun élève trouvé</h5>
                <p>Vous n'avez pas encore d'élèves assignés à vos classes.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal pour voir le profil d'un élève -->
<div class="modal fade" id="profilModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Profil de l'élève</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="profilContent">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function voirProfil(eleveId) {
    // Afficher le modal avec un indicateur de chargement
    document.getElementById('profilContent').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>';
    new bootstrap.Modal(document.getElementById('profilModal')).show();
    
    // Faire une requête AJAX pour charger les détails de l'élève
    fetch(`/teacher/eleves/${eleveId}/profil`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur lors du chargement du profil');
        }
        return response.text();
    })
    .then(html => {
        document.getElementById('profilContent').innerHTML = html;
    })
    .catch(error => {
        console.error('Erreur:', error);
        document.getElementById('profilContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Erreur lors du chargement du profil: ${error.message}
            </div>
        `;
    });
}

function marquerAbsence(eleveId) {
    // Rediriger vers la page de saisie des absences
    window.location.href = '{{ route("teacher.absences") }}';
}

function saisirNote(eleveId) {
    // Rediriger vers la page de saisie des notes
    window.location.href = '{{ route("notes.index") }}';
}

function exporterEleves() {
    // Fonction pour exporter la liste des élèves
    alert('Fonction d\'export en cours de développement');
}

// Initialiser DataTables si nécessaire
$(document).ready(function() {
    $('#elevesTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/French.json"
        },
        "pageLength": 25,
        "order": [[ 2, "asc" ]]
    });
});
</script>
@endpush

@push('styles')
<style>
.btn-block { width: 100%; }
.table th { border-top: none; }
</style>
@endpush
@endsection





