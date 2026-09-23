@extends('layouts.app')

@section('title', 'Enregistrer une absence enseignant')
@include('absences-enseignants._styles')

@section('content')
<div class="ae-page">
    <div class="ae-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1><i class="fas fa-user-plus me-2"></i>Enregistrer une absence</h1>
            <p>Choisissez l’enseignant, la période et le motif. Inutile de pointer tout le personnel.</p>
        </div>
        <a href="{{ route('absences-enseignants.index') }}" class="btn btn-outline-light">
            <i class="fas fa-arrow-left me-1"></i> Retour
        </a>
    </div>

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card ae-card">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('absences-enseignants.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="ae-form-section">
                            <h6>Enseignant</h6>
                            <label class="form-label">Qui est concerné ? <span class="text-danger">*</span></label>
                            <select name="enseignant_id" class="form-select form-select-lg" required>
                                <option value="">Sélectionner un enseignant…</option>
                                @foreach($enseignants as $enseignant)
                                    <option value="{{ $enseignant->id }}" @selected((string) old('enseignant_id', $enseignantId) === (string) $enseignant->id)>
                                        {{ $enseignant->nom_complet }}
                                        @if($enseignant->specialite) — {{ $enseignant->specialite }} @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="ae-form-section">
                            <h6>Période</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Du <span class="text-danger">*</span></label>
                                    <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut', now()->toDateString()) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Au <span class="text-danger">*</span></label>
                                    <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin', now()->toDateString()) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Heure de début</label>
                                    <input type="time" name="heure_debut" class="form-control" value="{{ old('heure_debut') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Heure de fin</label>
                                    <input type="time" name="heure_fin" class="form-control" value="{{ old('heure_fin') }}">
                                </div>
                            </div>
                            <div class="form-text mt-2">Laissez les heures vides pour une journée complète. Pour un retard, indiquez l’heure d’arrivée.</div>
                        </div>

                        <div class="ae-form-section">
                            <h6>Motif</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        <option value="absence" @selected(old('type', 'absence') === 'absence')>Absence</option>
                                        <option value="retard" @selected(old('type') === 'retard')>Retard</option>
                                        <option value="conge" @selected(old('type') === 'conge')>Congé</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                                    <select name="motif_categorie" class="form-select" required>
                                        <option value="">Choisir…</option>
                                        <option value="maladie" @selected(old('motif_categorie') === 'maladie')>Maladie</option>
                                        <option value="personnel" @selected(old('motif_categorie') === 'personnel')>Raison personnelle</option>
                                        <option value="mission" @selected(old('motif_categorie') === 'mission')>Mission / déplacement</option>
                                        <option value="autre" @selected(old('motif_categorie') === 'autre')>Autre</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Précisions <span class="text-danger">*</span></label>
                                    <textarea name="motif" class="form-control" rows="3" required placeholder="Ex. rendez-vous médical, déplacement officiel…">{{ old('motif') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Justificatif (PDF ou photo)</label>
                                    <input type="file" name="document_justificatif" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="justifiee" name="justifiee" value="1" @checked(old('_token') ? old('justifiee') : true)>
                                        <label class="form-check-label" for="justifiee">Marquer comme justifiée</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('absences-enseignants.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card ae-card">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-3">Comment ça marche</h6>
                    <ol class="mb-0 ps-3 text-muted" style="line-height:1.7">
                        <li>Sélectionnez un seul enseignant.</li>
                        <li>Indiquez le jour ou la période d’absence.</li>
                        <li>Précisez le type et le motif.</li>
                        <li>Les déclarations faites par l’enseignant apparaissent ensuite dans la liste, à valider.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
