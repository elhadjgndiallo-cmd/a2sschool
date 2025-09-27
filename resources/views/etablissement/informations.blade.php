@extends('layouts.app')

@section('title', 'Informations de l\'Établissement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-school text-primary me-2"></i>
                    Informations de l'Établissement
                </h1>
                @if($etablissement)
                    <div class="btn-group">
                        <a href="{{ route('etablissement.informations.edit') }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Modifier
                        </a>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </div>
                @else
                    <a href="{{ route('etablissement.informations.edit') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Ajouter les Informations
                    </a>
                @endif
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Utiliser le composant de carte d'informations --}}
            <x-school-info-card :editable="false" />

            {{-- Si aucune information n'est configurée --}}
            @if(!$etablissement)
                <div class="card mt-4">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-school fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted mb-3">Aucune information d'établissement configurée</h4>
                        <p class="text-muted mb-4">
                            Configurez les informations de votre établissement pour les utiliser dans vos documents scolaires 
                            (bulletins, relevés, listes, certificats, etc.).
                        </p>
                        <a href="{{ route('etablissement.informations.edit') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Configurer les Informations
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal de confirmation pour la suppression --}}
@if($etablissement)
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmer la Suppression
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention!</strong> Cette action supprimera définitivement toutes les informations de l'établissement.
                </div>
                <p><strong>Seront supprimés :</strong></p>
                <ul>
                    <li>Nom, adresse, téléphone, email</li>
                    <li>Slogan et description</li>
                    <li>Logo et cachet</li>
                    <li>Informations des responsables</li>
                    <li>Configuration des matricules</li>
                </ul>
                <p class="text-danger"><strong>Cette action est irréversible.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <form action="{{ route('etablissement.reset') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Confirmer la Suppression
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
@endif

@endsection
