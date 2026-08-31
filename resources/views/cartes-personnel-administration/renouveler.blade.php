@extends('layouts.app')

@section('title', 'Renouveler la Carte Administration')

@push('styles')
    @include('cartes-enseignants._carte-styles')
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-sync me-2"></i>Renouveler la Carte Administration</h2>
        <a href="{{ route('cartes-personnel-administration.show', $cartes_personnel_administration) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        Le renouvellement créera une nouvelle carte et désactivera l'ancienne.
                    </div>
                    <form action="{{ route('cartes-personnel-administration.traiter-renouvellement', $cartes_personnel_administration) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="date_expiration" class="form-label">Nouvelle date d'expiration <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_expiration" name="date_expiration"
                                   value="{{ old('date_expiration', now()->addYear()->format('Y-m-d')) }}"
                                   min="{{ now()->addDay()->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="observations" class="form-label">Observations</label>
                            <textarea class="form-control" id="observations" name="observations" rows="3">{{ old('observations') }}</textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('cartes-personnel-administration.show', $cartes_personnel_administration) }}" class="btn btn-secondary me-2">Annuler</a>
                            <button type="submit" class="btn btn-success" onclick="return confirm('Renouveler cette carte ?')">
                                <i class="fas fa-sync me-1"></i>Renouveler
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Aperçu de la carte actuelle</h5>
                </div>
                <div class="card-body">
                    <div class="carte-preview-wrap">
                        <div class="carte-preview-stage">
                            @include('cartes-personnel-administration._carte', ['carte' => $cartes_personnel_administration])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
