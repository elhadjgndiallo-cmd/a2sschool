@extends('layouts.app')

@section('title', 'Historique des absences - ' . $classe->nom)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-calendar-times me-2"></i>
                            Historique des absences - {{ $classe->nom }}
                        </h4>
                        <div>
                            <a href="{{ route('teacher.eleves-classe', $classe) }}" class="btn btn-dark btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>
                                Retour
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistiques -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $absences->count() }}</h3>
                                    <small>Total absences</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $absences->where('justifiee', false)->count() }}</h3>
                                    <small>Non justifiées</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $absences->where('justifiee', true)->count() }}</h3>
                                    <small>Justifiées</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $absences->groupBy('eleve_id')->count() }}</h3>
                                    <small>Élèves absents</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="justification_filter" class="form-label">Filtrer par justification</label>
                            <select id="justification_filter" class="form-select">
                                <option value="">Toutes</option>
                                <option value="justifiee">Justifiées</option>
                                <option value="non_justifiee">Non justifiées</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="date_filter" class="form-label">Filtrer par date</label>
                            <input type="date" id="date_filter" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="eleve_filter" class="form-label">Filtrer par élève</label>
                            <select id="eleve_filter" class="form-select">
                                <option value="">Tous les élèves</option>
                                @foreach($absences->groupBy('eleve_id') as $eleveId => $eleveAbsences)
                                    <option value="{{ $eleveId }}">
                                        {{ $eleveAbsences->first()->eleve->utilisateur->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if($absences->isEmpty())
                        <div class="alert alert-success text-center">
                            <i class="fas fa-check-circle fa-2x mb-3"></i>
                            <h5>Aucune absence enregistrée</h5>
                            <p>Excellent ! Tous les élèves de cette classe sont présents.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="absencesTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Élève</th>
                                        <th>Justifiée</th>
                                        <th>Motif</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($absences as $absence)
                                        <tr data-justifiee="{{ $absence->justifiee ? 'justifiee' : 'non_justifiee' }}"
                                            data-date="{{ $absence->date_absence }}"
                                            data-eleve="{{ $absence->eleve_id }}">
                                            <td>{{ \Carbon\Carbon::parse($absence->date_absence)->format('d/m/Y') }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($absence->eleve->utilisateur->photo)
                                                        <img src="{{ asset('storage/' . $absence->eleve->utilisateur->photo) }}" 
                                                             alt="Photo" class="rounded-circle me-2" 
                                                             style="width: 32px; height: 32px; object-fit: cover;">
                                                    @else
                                                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                             style="width: 32px; height: 32px; font-size: 0.875rem;">
                                                            {{ substr($absence->eleve->utilisateur->nom, 0, 1) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold">{{ $absence->eleve->utilisateur->nom }}</div>
                                                        <small class="text-muted">ID: {{ $absence->eleve->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($absence->justifiee)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i>
                                                        Justifiée
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times me-1"></i>
                                                        Non justifiée
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($absence->motif)
                                                    <span class="text-muted">{{ Str::limit($absence->motif, 50) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editAbsenceModal{{ $absence->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteAbsence({{ $absence->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const justificationFilter = document.getElementById('justification_filter');
    const dateFilter = document.getElementById('date_filter');
    const eleveFilter = document.getElementById('eleve_filter');
    const table = document.getElementById('absencesTable');
    
    function filterTable() {
        const justificationValue = justificationFilter.value;
        const dateValue = dateFilter.value;
        const eleveValue = eleveFilter.value;
        
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            let show = true;
            
            if (justificationValue && row.dataset.justifiee !== justificationValue) {
                show = false;
            }
            
            if (dateValue && row.dataset.date !== dateValue) {
                show = false;
            }
            
            if (eleveValue && row.dataset.eleve !== eleveValue) {
                show = false;
            }
            
            row.style.display = show ? '' : 'none';
        });
    }
    
    justificationFilter.addEventListener('change', filterTable);
    dateFilter.addEventListener('change', filterTable);
    eleveFilter.addEventListener('change', filterTable);
});

function deleteAbsence(absenceId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette absence ?')) {
        // Ici vous pouvez ajouter une requête AJAX pour supprimer l'absence
        console.log('Suppression de l\'absence:', absenceId);
    }
}
</script>
@endpush

@push('styles')
<style>
.avatar {
    font-size: 0.875rem;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .d-flex.align-items-center {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .avatar, img {
        margin-bottom: 0.5rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.2rem 0.4rem;
        font-size: 0.75rem;
    }
    
    .row .col-md-3 {
        margin-bottom: 1rem;
    }
}
</style>
@endpush
@endsection
