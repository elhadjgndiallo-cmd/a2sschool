@extends('layouts.app')

@section('title', 'Sélection - Fiche de Notes')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-file-alt me-2"></i>
        Fiche de Notes - Sélection
    </h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i>
                    Sélectionner la classe, l'enseignant et la matière
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('notes.fiche', ['classe' => '0', 'enseignant' => '0', 'matiere' => '0']) }}" method="GET" id="ficheForm">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="classe" class="form-label">
                                <i class="fas fa-users me-1"></i>
                                Classe <span class="text-danger">*</span>
                            </label>
                            <select name="classe" id="classe" class="form-select" required>
                                <option value="">-- Sélectionner une classe --</option>
                                @foreach($classes as $classe)
                                    <option value="{{ $classe->id }}">{{ $classe->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="enseignant" class="form-label">
                                <i class="fas fa-chalkboard-teacher me-1"></i>
                                Enseignant <span class="text-danger">*</span>
                            </label>
                            <select name="enseignant" id="enseignant" class="form-select" required>
                                <option value="">-- Sélectionner un enseignant --</option>
                                @foreach($enseignants as $enseignant)
                                    <option value="{{ $enseignant->id }}">
                                        {{ $enseignant->utilisateur->nom ?? '' }} {{ $enseignant->utilisateur->prenom ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="matiere" class="form-label">
                                <i class="fas fa-book me-1"></i>
                                Matière <span class="text-danger">*</span>
                            </label>
                            <select name="matiere" id="matiere" class="form-select" required>
                                <option value="">-- Sélectionner une matière --</option>
                                @foreach($matieres as $matiere)
                                    <option value="{{ $matiere->id }}">{{ $matiere->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="semestre" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Semestre
                            </label>
                            <select name="semestre" id="semestre" class="form-select">
                                <option value="sem1">Premier Semestre</option>
                                <option value="sem2">Deuxième Semestre</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-alt me-2"></i>
                                Générer la Fiche de Notes
                            </button>
                            <a href="{{ route('notes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Retour
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('ficheForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const classe = document.getElementById('classe').value;
    const enseignant = document.getElementById('enseignant').value;
    const matiere = document.getElementById('matiere').value;
    const semestre = document.getElementById('semestre').value;
    
    if (!classe || !enseignant || !matiere) {
        alert('Veuillez sélectionner la classe, l\'enseignant et la matière.');
        return;
    }
    
    const url = "{{ route('notes.fiche', ['classe' => ':classe', 'enseignant' => ':enseignant', 'matiere' => ':matiere']) }}"
        .replace(':classe', classe)
        .replace(':enseignant', enseignant)
        .replace(':matiere', matiere);
    
    window.location.href = url + '?semestre=' + semestre;
});
</script>
@endpush
@endsection

