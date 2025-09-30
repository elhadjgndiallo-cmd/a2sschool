@extends('layouts.app')

@section('title', 'Historique des notes - ' . $classe->nom)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>
                            Historique des notes - {{ $classe->nom }}
                        </h4>
                        <div>
                            <a href="{{ route('teacher.eleves-classe', $classe) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>
                                Retour
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="matiere_filter" class="form-label">Filtrer par matière</label>
                            <select id="matiere_filter" class="form-select">
                                <option value="">Toutes les matières</option>
                                @foreach($matieres as $mat)
                                    <option value="{{ $mat->id }}" {{ $matiere && $matiere->id == $mat->id ? 'selected' : '' }}>
                                        {{ $mat->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="type_filter" class="form-label">Filtrer par type</label>
                            <select id="type_filter" class="form-select">
                                <option value="">Tous les types</option>
                                <option value="devoir">Devoir</option>
                                <option value="composition">Composition</option>
                                <option value="examen">Examen</option>
                                <option value="oral">Oral</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="date_filter" class="form-label">Filtrer par date</label>
                            <input type="date" id="date_filter" class="form-control">
                        </div>
                    </div>

                    @if($notes->isEmpty())
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <h5>Aucune note enregistrée</h5>
                            <p>Il n'y a pas encore de notes pour cette classe.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="notesTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Élève</th>
                                        <th>Matière</th>
                                        <th>Type</th>
                                        <th>Note</th>
                                        <th>Observations</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notes as $note)
                                        <tr data-matiere="{{ $note->matiere->id }}" 
                                            data-type="{{ $note->type_evaluation }}"
                                            data-date="{{ $note->date_evaluation }}">
                                            <td>{{ \Carbon\Carbon::parse($note->date_evaluation)->format('d/m/Y') }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($note->eleve->utilisateur->photo)
                                                        <img src="{{ asset('storage/' . $note->eleve->utilisateur->photo_profil) }}" 
                                                             alt="Photo" class="rounded-circle me-2" 
                                                             style="width: 32px; height: 32px; object-fit: cover;">
                                                    @else
                                                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                             style="width: 32px; height: 32px; font-size: 0.875rem;">
                                                            {{ substr($note->eleve->utilisateur->nom, 0, 1) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold">{{ $note->eleve->utilisateur->nom }}</div>
                                                        <small class="text-muted">ID: {{ $note->eleve->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $note->matiere->nom }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ ucfirst($note->type_evaluation) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ ($note->note_finale ?? 0) >= 10 ? 'bg-success' : (($note->note_finale ?? 0) >= 8 ? 'bg-warning' : 'bg-danger') }} fs-6">
                                                    {{ number_format($note->note_finale ?? 0, 2) }}/20
                                                </span>
                                            </td>
                                            <td>
                                                @if($note->observations)
                                                    <span class="text-muted">{{ Str::limit($note->observations, 50) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editNoteModal{{ $note->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteNote({{ $note->id }})">
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
    const matiereFilter = document.getElementById('matiere_filter');
    const typeFilter = document.getElementById('type_filter');
    const dateFilter = document.getElementById('date_filter');
    const table = document.getElementById('notesTable');
    
    function filterTable() {
        const matiereValue = matiereFilter.value;
        const typeValue = typeFilter.value;
        const dateValue = dateFilter.value;
        
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            let show = true;
            
            if (matiereValue && row.dataset.matiere !== matiereValue) {
                show = false;
            }
            
            if (typeValue && row.dataset.type !== typeValue) {
                show = false;
            }
            
            if (dateValue && row.dataset.date !== dateValue) {
                show = false;
            }
            
            row.style.display = show ? '' : 'none';
        });
    }
    
    matiereFilter.addEventListener('change', filterTable);
    typeFilter.addEventListener('change', filterTable);
    dateFilter.addEventListener('change', filterTable);
});

function deleteNote(noteId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette note ?')) {
        // Ici vous pouvez ajouter une requête AJAX pour supprimer la note
        console.log('Suppression de la note:', noteId);
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

.badge.fs-6 {
    font-size: 0.875rem !important;
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
}
</style>
@endpush
@endsection
