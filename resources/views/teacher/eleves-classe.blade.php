@extends('layouts.app')

@section('title', 'Élèves de la classe ' . $classe->nom)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-users me-2"></i>
        Élèves de la classe {{ $classe->nom }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('teacher.classes') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour aux classes
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

<!-- Informations de la classe -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informations de la classe</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nom de la classe :</strong> {{ $classe->nom }}</p>
                        <p><strong>Niveau :</strong> {{ $classe->niveau }}</p>
                        <p><strong>Effectif actuel :</strong> {{ $classe->eleves->count() }} élèves</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Effectif maximum :</strong> {{ $classe->effectif_max ?? 'Non défini' }}</p>
                        <p><strong>Statut :</strong> 
                            <span class="badge bg-success">{{ $classe->actif ? 'Active' : 'Inactive' }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Actions rapides</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('teacher.absences.classe', $classe->id) }}" class="btn btn-warning">
                        <i class="fas fa-user-times me-2"></i>
                        Saisir absences
                    </a>
                    <a href="{{ route('teacher.notes.classe', $classe->id) }}" class="btn btn-success">
                        <i class="fas fa-edit me-2"></i>
                        Saisir notes
                    </a>
                    <button type="button" class="btn btn-info" onclick="exporterEleves()">
                        <i class="fas fa-download me-2"></i>
                        Exporter liste
                    </button>
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
            Liste des élèves ({{ $classe->eleves->count() }} élèves)
        </h5>
    </div>
    <div class="card-body">
        @if($classe->eleves->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover" id="elevesTable">
                    <thead class="table-dark">
                        <tr>
                            <th width="8%">Photo</th>
                            <th width="12%">Matricule</th>
                            <th width="20%">Nom</th>
                            <th width="20%">Prénom</th>
                            <th width="15%">Date de naissance</th>
                            <th width="10%">Sexe</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classe->eleves as $eleve)
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
                                {{ $eleve->utilisateur->date_naissance ? $eleve->utilisateur->date_naissance->format('d/m/Y') : 'Non renseigné' }}
                            </td>
                            <td>
                                @if($eleve->utilisateur->sexe)
                                    <span class="badge bg-{{ $eleve->utilisateur->sexe == 'M' ? 'primary' : 'pink' }}">
                                        {{ $eleve->utilisateur->sexe == 'M' ? 'Masculin' : 'Féminin' }}
                                    </span>
                                @else
                                    <span class="text-muted">Non renseigné</span>
                                @endif
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
                <h5>Aucun élève dans cette classe</h5>
                <p>Cette classe ne contient aucun élève pour le moment.</p>
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
    // Ici vous pouvez ajouter une requête AJAX pour charger les détails de l'élève
    document.getElementById('profilContent').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>';
    new bootstrap.Modal(document.getElementById('profilModal')).show();
}

function marquerAbsence(eleveId) {
    // Rediriger vers la page de saisie des absences
    window.location.href = '{{ route("teacher.absences.classe", $classe->id) }}';
}

function saisirNote(eleveId) {
    // Rediriger vers la page de saisie des notes
    window.location.href = '{{ route("teacher.notes.classe", $classe->id) }}';
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
.table th { border-top: none; }
</style>
@endpush
@endsection