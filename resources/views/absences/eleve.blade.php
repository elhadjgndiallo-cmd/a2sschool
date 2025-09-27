@extends('layouts.app')

@section('title', 'Historique des Absences - ' . $eleve->utilisateur->nom . ' ' . $eleve->utilisateur->prenom)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-history me-2"></i>
        Historique des Absences
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('absences.saisir', $eleve->classe_id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
    </div>
</div>

<!-- Informations de l'élève -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Informations de l'élève</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div class="avatar-lg me-3">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 80px; height: 80px;">
                            {{ substr($eleve->utilisateur->prenom, 0, 1) }}{{ substr($eleve->utilisateur->nom, 0, 1) }}
                        </div>
                    </div>
                    <div>
                        <h4 class="mb-1">{{ $eleve->utilisateur->nom }} {{ $eleve->utilisateur->prenom }}</h4>
                        <p class="text-muted mb-1">
                            <i class="fas fa-id-card me-1"></i>
                            {{ $eleve->numero_etudiant }}
                        </p>
                        <p class="text-muted mb-0">
                            <i class="fas fa-users me-1"></i>
                            {{ $eleve->classe->nom }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="row text-center">
                    <div class="col-4">
                        <h3 class="text-danger">{{ $totalAbsences }}</h3>
                        <small>Total Absences</small>
                    </div>
                    <div class="col-4">
                        <h3 class="text-warning">{{ $absencesNonJustifiees }}</h3>
                        <small>Non Justifiées</small>
                    </div>
                    <div class="col-4">
                        <h3 class="text-info">{{ $retards }}</h3>
                        <small>Retards</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Liste des absences -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Historique des absences</h5>
        <div>
            <button type="button" class="btn btn-sm btn-warning" id="notifier-parents-btn">
                <i class="fas fa-bell me-1"></i>
                Notifier les parents
            </button>
        </div>
    </div>
    <div class="card-body">
        @if($absences->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Matière</th>
                        <th>Type</th>
                        <th>Heure</th>
                        <th>Statut</th>
                        <th>Motif</th>
                        <th>Saisi par</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($absences as $absence)
                    <tr>
                        <td>
                            <strong>{{ $absence->date_absence->format('d/m/Y') }}</strong>
                            <br>
                            <small class="text-muted">{{ $absence->date_absence->format('l') }}</small>
                        </td>
                        <td>
                            @if($absence->matiere)
                                {{ $absence->matiere->nom }}
                            @else
                                <span class="text-muted">Toutes matières</span>
                            @endif
                        </td>
                        <td>
                            @switch($absence->type)
                                @case('absence')
                                    <span class="badge bg-danger">Absence</span>
                                    @break
                                @case('retard')
                                    <span class="badge bg-warning">Retard</span>
                                    @break
                                @case('sortie_anticipee')
                                    <span class="badge bg-info">Sortie anticipée</span>
                                    @break
                            @endswitch
                        </td>
                        <td>
                            @if($absence->heure_debut && $absence->heure_fin)
                                {{ date('H:i', strtotime($absence->heure_debut)) }} - {{ date('H:i', strtotime($absence->heure_fin)) }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($absence->statut == 'justifiee')
                                <span class="badge bg-success">Justifiée</span>
                            @else
                                <span class="badge bg-danger">Non justifiée</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $absence->motif }}">
                                {{ $absence->motif }}
                            </span>
                        </td>
                        <td>
                            @if($absence->saisiPar)
                                {{ $absence->saisiPar->name }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @if($absence->statut == 'non_justifiee')
                                <button type="button" 
                                        class="btn btn-outline-success btn-sm justifier-btn" 
                                        data-absence-id="{{ $absence->id }}"
                                        title="Justifier">
                                    <i class="fas fa-check"></i>
                                </button>
                                @endif
                                
                                @if($absence->statut == 'non_justifiee' && !$absence->notifie_parents_at)
                                <button type="button" 
                                        class="btn btn-outline-warning btn-sm notifier-btn" 
                                        data-absence-id="{{ $absence->id }}"
                                        title="Notifier les parents">
                                    <i class="fas fa-bell"></i>
                                </button>
                                @elseif($absence->notifie_parents_at)
                                <span class="badge bg-success" title="Notifié le {{ $absence->notifie_parents_at->format('d/m/Y H:i') }}">
                                    <i class="fas fa-check"></i>
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $absences->links('vendor.pagination.custom') }}
        </div>
        @else
        <div class="text-center text-muted py-5">
            <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
            <h5>Aucune absence enregistrée</h5>
            <p>Cet élève n'a aucune absence dans son historique.</p>
        </div>
        @endif
    </div>
</div>

<!-- Modal de justification -->
<div class="modal fade" id="justifierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Justifier l'absence</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="justifierForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="motif_justification" class="form-label">Motif de justification</label>
                        <textarea class="form-control" 
                                  id="motif_justification" 
                                  name="motif" 
                                  rows="3" 
                                  required
                                  placeholder="Expliquez le motif de l'absence..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="document_justificatif" class="form-label">Document justificatif (optionnel)</label>
                        <input type="file" 
                               class="form-control" 
                               id="document_justificatif" 
                               name="document_justificatif"
                               accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text">Formats acceptés: PDF, JPG, JPEG, PNG (max 2MB)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>
                        Justifier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Justifier une absence
    document.querySelectorAll('.justifier-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const absenceId = this.dataset.absenceId;
            const form = document.getElementById('justifierForm');
            form.action = `/absences/${absenceId}/justifier`;
            
            const modal = new bootstrap.Modal(document.getElementById('justifierModal'));
            modal.show();
        });
    });

    // Notifier les parents pour une absence spécifique
    document.querySelectorAll('.notifier-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const absenceId = this.dataset.absenceId;
            
            if (confirm('Êtes-vous sûr de vouloir notifier les parents pour cette absence ?')) {
                fetch(`/absences/${absenceId}/notifier`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erreur lors de la notification: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la notification des parents');
                });
            }
        });
    });

    // Notifier les parents pour toutes les absences non justifiées
    document.getElementById('notifier-parents-btn').addEventListener('click', function() {
        if (confirm('Êtes-vous sûr de vouloir notifier les parents pour toutes les absences non justifiées de cet élève ?')) {
            // Ici vous pouvez implémenter la logique pour notifier toutes les absences
            alert('Fonctionnalité de notification groupée à implémenter');
        }
    });
});
</script>
@endpush
@endsection













