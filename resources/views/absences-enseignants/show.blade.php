@extends('layouts.app')

@section('title', 'Détail absence enseignant')
@include('absences-enseignants._styles')

@section('content')
@php
    $user = $absenceEnseignant->enseignant->utilisateur ?? null;
    $photo = $user->photo_profil ?? null;
    $initiales = $user ? strtoupper(substr($user->prenom ?? '', 0, 1) . substr($user->nom ?? '', 0, 1)) : '?';
@endphp
<div class="ae-page">
    <div class="ae-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="ae-avatar" style="width:56px;height:56px;background:rgba(255,255,255,.18);color:#fff;font-size:1.1rem;">
                @if($photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($photo))
                    <img src="{{ asset('storage/' . $photo) }}" alt="">
                @else
                    {{ $initiales }}
                @endif
            </div>
            <div>
                <h1 class="mb-1">{{ $absenceEnseignant->enseignant->nom_complet ?? 'Enseignant' }}</h1>
                <p>{{ $absenceEnseignant->type_label }} · {{ $absenceEnseignant->periode_label }}</p>
            </div>
        </div>
        <a href="{{ route('absences-enseignants.index') }}" class="btn btn-outline-light">Retour</a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card ae-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Détails</span>
                    <span class="badge ae-badge bg-{{ $absenceEnseignant->statut_badge }}">{{ $absenceEnseignant->statut_label }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Période</div>
                        <div class="col-sm-8">{{ $absenceEnseignant->periode_label }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Classe</div>
                        <div class="col-sm-8">{{ $absenceEnseignant->classe->nom ?? '—' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Heures</div>
                        <div class="col-sm-8">{{ $absenceEnseignant->creneau_label }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Type</div>
                        <div class="col-sm-8">{{ $absenceEnseignant->type_label }} · {{ $absenceEnseignant->motif_categorie_label }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Motif</div>
                        <div class="col-sm-8">{{ $absenceEnseignant->motif ?: '—' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Origine</div>
                        <div class="col-sm-8">{{ $absenceEnseignant->origine_label }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Saisi par</div>
                        <div class="col-sm-8">{{ $absenceEnseignant->saisiPar->nom_complet ?? '—' }}</div>
                    </div>
                    @if($absenceEnseignant->traitePar)
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Traité par</div>
                        <div class="col-sm-8">{{ $absenceEnseignant->traitePar->nom_complet }} · {{ $absenceEnseignant->traite_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    @endif
                    @if($absenceEnseignant->commentaire_admin)
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Commentaire</div>
                        <div class="col-sm-8">{{ $absenceEnseignant->commentaire_admin }}</div>
                    </div>
                    @endif
                    @if($absenceEnseignant->document_justificatif)
                    <div class="row">
                        <div class="col-sm-4 text-muted">Justificatif</div>
                        <div class="col-sm-8">
                            <a href="{{ asset('storage/' . $absenceEnseignant->document_justificatif) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-paperclip me-1"></i> Voir le document
                            </a>
                        </div>
                    </div>
                    @endif
                    <div class="mt-3">
                        <a href="{{ route('absences-enseignants.fiche', $absenceEnseignant->enseignant_id) }}" class="btn btn-sm btn-outline-secondary">
                            Toutes les absences de cet enseignant
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            @if(auth()->user()->hasPermission('absences-enseignants.edit') && $absenceEnseignant->peutEtreTraitee())
            <div class="card ae-card mb-3">
                <div class="card-header">Déclaration à traiter</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('absences-enseignants.valider', $absenceEnseignant) }}" class="mb-3">
                        @csrf
                        <textarea name="commentaire_admin" class="form-control mb-2" rows="2" placeholder="Commentaire (optionnel)">{{ old('commentaire_admin') }}</textarea>
                        <button type="submit" class="btn btn-success w-100">Valider la déclaration</button>
                    </form>
                    <form method="POST" action="{{ route('absences-enseignants.refuser', $absenceEnseignant) }}">
                        @csrf
                        <textarea name="commentaire_admin" class="form-control mb-2" rows="2" placeholder="Motif du refus *" required>{{ old('commentaire_admin') }}</textarea>
                        <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Refuser cette déclaration ?')">Refuser</button>
                    </form>
                </div>
            </div>
            @elseif(auth()->user()->hasPermission('absences-enseignants.edit') && $absenceEnseignant->peutEtreJustifiee())
            <div class="card ae-card mb-3">
                <div class="card-header">Justifier</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('absences-enseignants.justifier', $absenceEnseignant) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Catégorie</label>
                            <select name="motif_categorie" class="form-select">
                                <option value="">—</option>
                                <option value="maladie" @selected($absenceEnseignant->motif_categorie === 'maladie')>Maladie</option>
                                <option value="personnel" @selected($absenceEnseignant->motif_categorie === 'personnel')>Personnel</option>
                                <option value="mission" @selected($absenceEnseignant->motif_categorie === 'mission')>Mission</option>
                                <option value="autre" @selected($absenceEnseignant->motif_categorie === 'autre')>Autre</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Motif <span class="text-danger">*</span></label>
                            <textarea name="motif" class="form-control" rows="3" required>{{ old('motif', $absenceEnseignant->motif) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pièce jointe</label>
                            <input type="file" name="document_justificatif" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <button type="submit" class="btn btn-success w-100">Marquer comme justifiée</button>
                    </form>
                </div>
            </div>
            @endif

            @if(auth()->user()->hasPermission('absences-enseignants.delete'))
            <form method="POST" action="{{ route('absences-enseignants.destroy', $absenceEnseignant) }}" onsubmit="return confirm('Supprimer cette absence ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger w-100">Supprimer</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
