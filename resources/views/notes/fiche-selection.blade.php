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
                        <div class="col-md-12 mb-3">
                            <label class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Sélectionner les semestres <span class="text-danger">*</span>
                            </label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="semestres[]" value="sem1" id="semestre1">
                                        <label class="form-check-label" for="semestre1">
                                            <strong>Premier Semestre</strong> (Octobre, Novembre, Décembre)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="semestres[]" value="sem2" id="semestre2">
                                        <label class="form-check-label" for="semestre2">
                                            <strong>Deuxième Semestre</strong> (Janvier, Février, Mars)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="semestres[]" value="sem3" id="semestre3">
                                        <label class="form-check-label" for="semestre3">
                                            <strong>Troisième Semestre</strong> (Avril, Mai, Juin)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">Sélectionnez au moins un semestre</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">
                                <i class="fas fa-calendar-check me-1"></i>
                                Ou sélectionner des mois spécifiques
                            </label>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <strong>Deuxième Semestre</strong>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="mois[]" value="1" id="mois_janvier">
                                                <label class="form-check-label" for="mois_janvier">Janvier</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="mois[]" value="2" id="mois_fevrier">
                                                <label class="form-check-label" for="mois_fevrier">Février</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="mois[]" value="3" id="mois_mars">
                                                <label class="form-check-label" for="mois_mars">Mars</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <strong>Troisième Semestre</strong>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="mois[]" value="4" id="mois_avril">
                                                <label class="form-check-label" for="mois_avril">Avril</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="mois[]" value="5" id="mois_mai">
                                                <label class="form-check-label" for="mois_mai">Mai</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="mois[]" value="6" id="mois_juin">
                                                <label class="form-check-label" for="mois_juin">Juin</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <strong>Premier Semestre</strong>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="mois[]" value="10" id="mois_octobre">
                                                <label class="form-check-label" for="mois_octobre">Octobre</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="mois[]" value="11" id="mois_novembre">
                                                <label class="form-check-label" for="mois_novembre">Novembre</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="mois[]" value="12" id="mois_decembre">
                                                <label class="form-check-label" for="mois_decembre">Décembre</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">Sélectionnez au moins un mois ou un semestre</small>
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
    
    // Récupérer les semestres sélectionnés
    const semestresCheckboxes = document.querySelectorAll('input[name="semestres[]"]:checked');
    const semestres = Array.from(semestresCheckboxes).map(cb => cb.value);
    
    // Récupérer les mois sélectionnés
    const moisCheckboxes = document.querySelectorAll('input[name="mois[]"]:checked');
    const mois = Array.from(moisCheckboxes).map(cb => parseInt(cb.value));
    
    if (!classe || !enseignant || !matiere) {
        alert('Veuillez sélectionner la classe, l\'enseignant et la matière.');
        return;
    }
    
    // Si des semestres sont sélectionnés, ajouter leurs mois
    let moisFinaux = [...mois];
    
    if (semestres.includes('sem1')) {
        // Premier semestre : Octobre (10), Novembre (11), Décembre (12)
        if (!moisFinaux.includes(10)) moisFinaux.push(10);
        if (!moisFinaux.includes(11)) moisFinaux.push(11);
        if (!moisFinaux.includes(12)) moisFinaux.push(12);
    }
    
    if (semestres.includes('sem2')) {
        // Deuxième semestre : Janvier (1), Février (2), Mars (3)
        if (!moisFinaux.includes(1)) moisFinaux.push(1);
        if (!moisFinaux.includes(2)) moisFinaux.push(2);
        if (!moisFinaux.includes(3)) moisFinaux.push(3);
    }
    
    if (semestres.includes('sem3')) {
        // Troisième semestre : Avril (4), Mai (5), Juin (6)
        if (!moisFinaux.includes(4)) moisFinaux.push(4);
        if (!moisFinaux.includes(5)) moisFinaux.push(5);
        if (!moisFinaux.includes(6)) moisFinaux.push(6);
    }
    
    if (moisFinaux.length === 0) {
        alert('Veuillez sélectionner au moins un mois ou un semestre.');
        return;
    }
    
    // Trier les mois
    moisFinaux.sort((a, b) => a - b);
    
    const url = "{{ route('notes.fiche', ['classe' => ':classe', 'enseignant' => ':enseignant', 'matiere' => ':matiere']) }}"
        .replace(':classe', classe)
        .replace(':enseignant', enseignant)
        .replace(':matiere', matiere);
    
    // Ajouter les mois comme paramètres de requête
    const params = new URLSearchParams();
    moisFinaux.forEach(m => params.append('mois[]', m));
    
    window.location.href = url + '?' + params.toString();
});
</script>
@endpush
@endsection

