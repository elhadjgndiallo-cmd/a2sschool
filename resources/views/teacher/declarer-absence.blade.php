@extends('layouts.app')

@section('title', 'Déclarer une absence')
@include('absences-enseignants._styles')

@section('content')
<div class="ae-page">
    <div class="ae-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1><i class="fas fa-paper-plane me-2"></i>Nouvelle déclaration</h1>
            <p>Indiquez la période et le motif. L’administration validera ou refusera votre demande.</p>
        </div>
        <a href="{{ route('teacher.mes-absences') }}" class="btn btn-outline-light">Retour</a>
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
                    <form method="POST" action="{{ route('teacher.mes-absences.store') }}" enctype="multipart/form-data">
                        @csrf
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
                        </div>

                        <div class="ae-form-section">
                            <h6>Motif</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        <option value="absence" @selected(old('type') === 'absence')>Absence</option>
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
                                    <textarea name="motif" class="form-control" rows="4" required>{{ old('motif') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Justificatif (PDF ou photo)</label>
                                    <input type="file" name="document_justificatif" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('teacher.mes-absences') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary px-4">Envoyer la déclaration</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
