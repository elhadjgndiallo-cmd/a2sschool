@extends('layouts.app')

@section('title', 'Paramètres du système')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Paramètres du système</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Informations de l'école</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.parametres.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom_ecole" class="form-label">Nom de l'école</label>
                                    <input type="text" class="form-control @error('nom_ecole') is-invalid @enderror" id="nom_ecole" name="nom_ecole" value="{{ env('APP_NAME') }}" required>
                                    @error('nom_ecole')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="annee_scolaire" class="form-label">Année scolaire</label>
                                    <input type="text" class="form-control @error('annee_scolaire') is-invalid @enderror" id="annee_scolaire" name="annee_scolaire" value="{{ env('SCHOOL_YEAR', date('Y').'-'.(date('Y')+1)) }}" required>
                                    @error('annee_scolaire')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="adresse_ecole" class="form-label">Adresse</label>
                            <textarea class="form-control @error('adresse_ecole') is-invalid @enderror" id="adresse_ecole" name="adresse_ecole" rows="2" required>{{ env('SCHOOL_ADDRESS') }}</textarea>
                            @error('adresse_ecole')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telephone_ecole" class="form-label">Téléphone</label>
                                    <input type="text" class="form-control @error('telephone_ecole') is-invalid @enderror" id="telephone_ecole" name="telephone_ecole" value="{{ env('SCHOOL_PHONE') }}" required>
                                    @error('telephone_ecole')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_ecole" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email_ecole') is-invalid @enderror" id="email_ecole" name="email_ecole" value="{{ env('SCHOOL_EMAIL') }}" required>
                                    @error('email_ecole')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Maintenance du système</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-warning" type="button" onclick="if(confirm('Êtes-vous sûr de vouloir vider le cache ?')) { window.location.href = '{{ route('admin.cache.clear') }}'; }">
                            <i class="fas fa-broom me-1"></i> Vider le cache
                        </button>
                        <button class="btn btn-info" type="button" onclick="if(confirm('Êtes-vous sûr de vouloir optimiser la base de données ?')) { window.location.href = '{{ route('admin.db.optimize') }}'; }">
                            <i class="fas fa-database me-1"></i> Optimiser la base de données
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Sauvegardes</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" type="button" onclick="if(confirm('Êtes-vous sûr de vouloir créer une sauvegarde ?')) { window.location.href = '{{ route('admin.backup.create') }}'; }">
                            <i class="fas fa-download me-1"></i> Créer une sauvegarde
                        </button>
                        <a href="{{ route('admin.backup.list') }}" class="btn btn-secondary">
                            <i class="fas fa-list me-1"></i> Gérer les sauvegardes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection