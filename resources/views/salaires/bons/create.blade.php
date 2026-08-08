@extends('layouts.app')

@section('title', 'Nouveau bon de salaire')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-hand-holding-usd mr-2"></i>Nouveau bon de salaire (avance)</h3>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <div class="alert alert-info">
                        L'avance sera automatiquement déduite du prochain bulletin de salaire de l'enseignant.
                    </div>

                    <form action="{{ route('salaires.bons.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Enseignant <span class="text-danger">*</span></label>
                            <select name="enseignant_id" class="form-select" required>
                                <option value="">Sélectionner</option>
                                @foreach($enseignants as $e)
                                    <option value="{{ $e->id }}" {{ old('enseignant_id') == $e->id ? 'selected' : '' }}>
                                        {{ $e->utilisateur->nom }} {{ $e->utilisateur->prenom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Montant avancé (GNF) <span class="text-danger">*</span></label>
                                <input type="number" name="montant" class="form-control" min="1" step="1" value="{{ old('montant') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date du bon <span class="text-danger">*</span></label>
                                <input type="date" name="date_bon" class="form-control" value="{{ old('date_bon', now()->format('Y-m-d')) }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mois de référence (optionnel)</label>
                                <input type="month" name="mois_reference" class="form-control" value="{{ old('mois_reference') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                                <select name="mode_paiement" class="form-select" required>
                                    @foreach(['especes' => 'Espèces', 'cheque' => 'Chèque', 'virement' => 'Virement', 'carte' => 'Carte'] as $v => $l)
                                        <option value="{{ $v }}" {{ old('mode_paiement', 'especes') === $v ? 'selected' : '' }}>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Référence paiement</label>
                            <input type="text" name="reference_paiement" class="form-control" value="{{ old('reference_paiement') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observations</label>
                            <textarea name="observations" class="form-control" rows="2">{{ old('observations') }}</textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning">Créer le bon</button>
                            <a href="{{ route('salaires.bons.index') }}" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
