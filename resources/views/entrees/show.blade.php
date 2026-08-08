@extends('layouts.app')

@section('title', 'Détails de l\'Entrée')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h2 mb-0">
                <i class="fas fa-arrow-down me-2"></i>
                Détails de l'Entrée
            </h1>
            <p class="text-muted mb-0">Informations détaillées sur l'entrée</p>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('entrees.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations de l'Entrée</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Libellé :</label>
                                <p class="form-control-plaintext">{{ $entree->libelle }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Source :</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-info">{{ $entree->source }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Montant :</label>
                                <p class="form-control-plaintext text-success fw-bold fs-5">
                                    {{ $entree->montant_formate }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Date d'entrée :</label>
                                <p class="form-control-plaintext">{{ $entree->date_entree->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mode de paiement :</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-secondary">{{ ucfirst($entree->mode_paiement) }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Référence :</label>
                                <p class="form-control-plaintext">{{ $entree->reference ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    @if($entree->description)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Description :</label>
                            <p class="form-control-plaintext">{{ $entree->description }}</p>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Enregistré par :</label>
                                <p class="form-control-plaintext">{{ $entree->enregistrePar->nom ?? 'N/A' }} {{ $entree->enregistrePar->prenom ?? '' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Date de création :</label>
                                <p class="form-control-plaintext">{{ $entree->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 no-print">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions</h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('entrees.edit', $entree) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i> Modifier
                </a>
                <a href="{{ route('entrees.recu', $entree) }}" class="btn btn-info" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> Télécharger reçu
                </a>
                <form action="{{ route('entrees.destroy', $entree) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette entrée ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fas fa-trash me-1"></i> Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
