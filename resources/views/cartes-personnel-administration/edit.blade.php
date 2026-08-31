@extends('layouts.app')

@section('title', 'Modifier la Carte Administration')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-edit me-2"></i>Modifier la Carte Administration</h2>
        <a href="{{ route('cartes-personnel-administration.show', $cartes_personnel_administration) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('cartes-personnel-administration.update', $cartes_personnel_administration) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Numéro de carte</label>
                        <input type="text" class="form-control" value="{{ $cartes_personnel_administration->numero_carte }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="type_carte" class="form-label">Type de carte <span class="text-danger">*</span></label>
                        <select class="form-select" id="type_carte" name="type_carte" required>
                            <option value="standard" {{ old('type_carte', $cartes_personnel_administration->type_carte) == 'standard' ? 'selected' : '' }}>Standard</option>
                            <option value="temporaire" {{ old('type_carte', $cartes_personnel_administration->type_carte) == 'temporaire' ? 'selected' : '' }}>Temporaire</option>
                            <option value="remplacement" {{ old('type_carte', $cartes_personnel_administration->type_carte) == 'remplacement' ? 'selected' : '' }}>Remplacement</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="date_emission" class="form-label">Date d'émission <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date_emission" name="date_emission"
                               value="{{ old('date_emission', $cartes_personnel_administration->date_emission?->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="date_expiration" class="form-label">Date d'expiration <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date_expiration" name="date_expiration"
                               value="{{ old('date_expiration', $cartes_personnel_administration->date_expiration?->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                        <select class="form-select" id="statut" name="statut" required>
                            <option value="active" {{ old('statut', $cartes_personnel_administration->statut) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expiree" {{ old('statut', $cartes_personnel_administration->statut) == 'expiree' ? 'selected' : '' }}>Expirée</option>
                            <option value="suspendue" {{ old('statut', $cartes_personnel_administration->statut) == 'suspendue' ? 'selected' : '' }}>Suspendue</option>
                            <option value="annulee" {{ old('statut', $cartes_personnel_administration->statut) == 'annulee' ? 'selected' : '' }}>Annulée</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="observations" class="form-label">Observations</label>
                        <textarea class="form-control" id="observations" name="observations" rows="3">{{ old('observations', $cartes_personnel_administration->observations) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('cartes-personnel-administration.show', $cartes_personnel_administration) }}" class="btn btn-secondary me-2">Annuler</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
