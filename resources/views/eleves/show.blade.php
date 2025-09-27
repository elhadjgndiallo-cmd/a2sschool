@extends('layouts.app')

@section('title', 'Détails Élève')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-graduate me-2"></i>
        Détails de l'Élève
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('eleves.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
        <a href="{{ route('eleves.edit', $eleve->id) }}" class="btn btn-outline-primary ms-2">
            <i class="fas fa-edit me-1"></i>
            Modifier
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

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
                        @if($eleve->utilisateur && $eleve->utilisateur->photo_profil)
                            <div class="mb-3">
                                @php
                                    $photoPath = $eleve->utilisateur->photo_profil;
                                    // Vérifier si c'est un chemin absolu (URL complète) ou relatif
                                    if (filter_var($photoPath, FILTER_VALIDATE_URL)) {
                                        $photoUrl = $photoPath;
                                    } elseif (file_exists(public_path('storage/' . $photoPath))) {
                                        $photoUrl = asset('storage/' . $photoPath);
                                    } elseif (file_exists(public_path($photoPath))) {
                                        $photoUrl = asset($photoPath);
                                    } else {
                                        $photoUrl = null;
                                    }
                                @endphp
                                
                                @if($photoUrl)
                                    <img src="{{ $photoUrl }}" alt="Photo de profil" class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                @else
                                    <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center text-white mx-auto" style="width: 150px; height: 150px; font-size: 2rem;">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <small class="text-warning d-block mt-2">Photo introuvable</small>
                                @endif
                                
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal">
                                        <i class="fas fa-camera me-1"></i> Changer la photo
                                    </button>
                                    <form action="{{ route('eleves.delete-photo', $eleve->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette photo de profil ?')">
                                            <i class="fas fa-trash-alt me-1"></i> Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="mb-3">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white mx-auto" style="width: 150px; height: 150px; font-size: 3rem; font-weight: bold;">
                                    @if($eleve->utilisateur && $eleve->utilisateur->prenom && $eleve->utilisateur->nom)
                                        {{ strtoupper(substr($eleve->utilisateur->prenom, 0, 1)) }}{{ strtoupper(substr($eleve->utilisateur->nom, 0, 1)) }}
                                    @elseif($eleve->utilisateur && $eleve->utilisateur->name)
                                        {{ strtoupper(substr($eleve->utilisateur->name, 0, 2)) }}
                                    @else
                                        ??
                                    @endif
                                </div>
                                <small class="text-muted d-block mt-2">Aucune photo de profil</small>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal">
                                    <i class="fas fa-camera me-1"></i> Ajouter une photo
                                </button>
                            </div>
                        @endif
                        <h4 class="mt-3">
                            @if($eleve->utilisateur)
                                @if($eleve->utilisateur->nom && $eleve->utilisateur->prenom)
                                    {{ strtoupper($eleve->utilisateur->nom) }} {{ ucfirst($eleve->utilisateur->prenom) }}
                                @elseif($eleve->utilisateur->name)
                                    {{ $eleve->utilisateur->name }}
                                @else
                                    Nom non disponible
                                @endif
                            @else
                                Utilisateur manquant
                            @endif
                        </h4>
                        <p class="text-muted">
                            <span class="badge bg-{{ $eleve->actif ? 'success' : 'danger' }}">
                                {{ $eleve->actif ? 'Actif' : 'Inactif' }}
                            </span>
                            <span class="badge bg-info ms-2">{{ ucfirst($eleve->statut) }}</span>
                        </p>
                    </div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Matricule:</div>
                    <div class="col-md-8">{{ $eleve->numero_etudiant }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Email:</div>
                    <div class="col-md-8">{{ $eleve->utilisateur->email ?? 'Non défini' }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Téléphone:</div>
                    <div class="col-md-8">{{ $eleve->utilisateur->telephone ?? 'Non défini' }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Adresse:</div>
                    <div class="col-md-8">{{ $eleve->utilisateur->adresse }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Date de naissance:</div>
                    <div class="col-md-8">{{ $eleve->utilisateur->date_naissance }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Lieu de naissance:</div>
                    <div class="col-md-8">{{ $eleve->utilisateur->lieu_naissance }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Sexe:</div>
                    <div class="col-md-8">{{ $eleve->utilisateur->sexe == 'M' ? 'Masculin' : 'Féminin' }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Date d'inscription:</div>
                    <div class="col-md-8">{{ $eleve->date_inscription }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Informations scolaires -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Informations Scolaires</h5>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Classe:</div>
                    <div class="col-md-8">
                        @if($eleve->classe)
                            <a href="{{ route('classes.show', $eleve->classe->id) }}" class="text-decoration-none">
                                {{ $eleve->classe->nom }}
                            </a>
                        @else
                            Non assigné
                        @endif
                    </div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Statut:</div>
                    <div class="col-md-8">
                        <span class="badge bg-{{ $eleve->statut == 'actif' ? 'success' : ($eleve->statut == 'suspendu' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($eleve->statut) }}
                        </span>
                    </div>
                </div>
                
                <!-- Statistiques -->
                <div class="mt-4">
                    <h6 class="border-bottom pb-2">Statistiques</h6>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body py-2 text-center">
                                    <h3 class="mb-0">{{ $eleve->notes->count() }}</h3>
                                    <small class="text-muted">Notes</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body py-2 text-center">
                                    <h3 class="mb-0">{{ $eleve->absences->count() }}</h3>
                                    <small class="text-muted">Absences</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <div class="mt-3">
                    <h6 class="border-bottom pb-2">Actions rapides</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('absences.eleve', $eleve->id) }}" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-calendar-times me-1"></i>
                            Voir absences
                        </a>
                        <a href="#" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-graduation-cap me-1"></i>
                            Voir notes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Parents -->
<div class="card mb-4">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Parents / Tuteurs</h5>
        <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addParentModal">
            <i class="fas fa-plus me-1"></i>
            Ajouter un parent
        </button>
    </div>
    <div class="card-body">
        @if($eleve->parents->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Lien de parenté</th>
                        <th>Contact</th>
                        <th>Responsable légal</th>
                        <th>Contact d'urgence</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($eleve->parents as $parent)
                    <tr>
                        <td>
                            @if($parent->utilisateur)
                                <strong class="text-primary">
                                    @if($parent->utilisateur->nom && $parent->utilisateur->prenom)
                                        {{ strtoupper($parent->utilisateur->nom) }} {{ ucfirst($parent->utilisateur->prenom) }}
                                    @elseif($parent->utilisateur->name)
                                        {{ $parent->utilisateur->name }}
                                    @else
                                        Parent sans nom (ID: {{ $parent->utilisateur->id }})
                                    @endif
                                </strong>
                            @else
                                <span class="text-danger">Parent sans utilisateur</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                {{ ucfirst($parent->pivot->lien_parente ?? 'Non défini') }}
                            </span>
                        </td>
                        <td>
                            @if($parent->utilisateur)
                                <small class="d-block">
                                    <i class="fas fa-envelope me-1"></i>
                                    {{ $parent->utilisateur->email ?? 'Email non défini' }}
                                </small>
                                <small class="d-block">
                                    <i class="fas fa-phone me-1"></i>
                                    {{ $parent->utilisateur->telephone ?? 'Téléphone non défini' }}
                                </small>
                            @else
                                <span class="text-muted">Contact non disponible</span>
                            @endif
                        </td>
                        <td>
                            @if(isset($parent->pivot->responsable_legal) && $parent->pivot->responsable_legal)
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>Oui
                                </span>
                            @else
                                <span class="badge bg-secondary">Non</span>
                            @endif
                        </td>
                        <td>
                            @if(isset($parent->pivot->contact_urgence) && $parent->pivot->contact_urgence)
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Oui
                                </span>
                            @else
                                <span class="badge bg-secondary">Non</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="#" class="btn btn-outline-primary" title="Voir détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" title="Dissocier">
                                    <i class="fas fa-unlink"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Aucun parent ou tuteur associé à cet élève.
            </p>
        </div>
        @endif
    </div>
</div>

<!-- Modal pour upload de photo -->
<div class="modal fade" id="uploadPhotoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-camera me-2"></i>
                    Ajouter une photo de profil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eleves.update', $eleve->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="photo_profil" class="form-label">Sélectionner une photo</label>
                        <input type="file" class="form-control" id="photo_profil" name="photo_profil" accept="image/*" required>
                        <div class="form-text">
                            Formats acceptés : JPG, PNG, GIF. Taille maximale : 2MB.
                            Recommandé : 300x300 pixels.
                        </div>
                    </div>
                    
                    <!-- Prévisualisation -->
                    <div id="preview" class="text-center" style="display: none;">
                        <img id="preview-image" src="" alt="Prévisualisation" class="img-thumbnail rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>
                        Télécharger
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('photo_profil').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-image').src = e.target.result;
            document.getElementById('preview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('preview').style.display = 'none';
    }
});
</script>
@endpush
@endsection