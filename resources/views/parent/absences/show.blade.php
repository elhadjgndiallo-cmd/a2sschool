@extends('layouts.app')

@section('title', 'Absences de ' . $eleve->utilisateur->nom . ' ' . $eleve->utilisateur->prenom)

@section('content')
<div class="row">
    <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Absences de {{ $eleve->utilisateur->nom }} {{ $eleve->utilisateur->prenom }}
                    </h1>
                    <p class="text-muted mb-0">
                        <i class="fas fa-graduation-cap me-1"></i>
                        {{ $eleve->classe->nom ?? 'N/A' }} - {{ $eleve->numero_etudiant ?? 'N/A' }}
                    </p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="{{ route('parent.absences.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Retour
                    </a>
                    <a href="{{ route('parent.absences.export', ['eleve' => $eleve->id]) }}" class="btn btn-outline-primary">
                        <i class="fas fa-download me-1"></i>
                        Exporter
                    </a>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="row mb-4 g-3">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['total_absences'] }}</h4>
                            <p class="mb-0">Total Absences</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['absences_justifiees'] }}</h4>
                            <p class="mb-0">Justifiées</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['absences_non_justifiees'] }}</h4>
                            <p class="mb-0">Non Justifiées</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['absences_en_attente'] }}</h4>
                            <p class="mb-0">En Attente</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($absences->count() > 0)
                <!-- Absences par mois -->
                @foreach($absencesParMois as $mois => $absencesMois)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $mois)->format('F Y') }}
                                <span class="badge bg-primary ms-2">{{ $absencesMois->count() }} absence(s)</span>
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Matière</th>
                                            <th>Statut</th>
                                            <th>Motif</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($absencesMois as $absence)
                                            <tr>
                                                <td>
                                                    <strong>{{ $absence->date_absence->format('d/m/Y') }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $absence->date_absence->format('l') }}</small>
                                                </td>
                                                <td>
                                                    @if($absence->matiere)
                                                        <span class="badge" style="background-color: {{ $absence->matiere->couleur ?? '#007bff' }}">
                                                            {{ $absence->matiere->nom }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">Journée complète</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @switch($absence->statut)
                                                        @case('justifiee')
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-check me-1"></i>Justifiée
                                                            </span>
                                                            @break
                                                        @case('non_justifiee')
                                                            <span class="badge bg-danger">
                                                                <i class="fas fa-times me-1"></i>Non Justifiée
                                                            </span>
                                                            @break
                                                        @case('en_attente')
                                                            <span class="badge bg-warning">
                                                                <i class="fas fa-clock me-1"></i>En Attente
                                                            </span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-secondary">{{ ucfirst($absence->statut) }}</span>
                                                    @endswitch
                                                </td>
                                                <td>
                                                    @if($absence->motif_absence)
                                                        <small>{{ Str::limit($absence->motif_absence, 50) }}</small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-info" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#detailsModal{{ $absence->id }}" 
                                                                title="Voir détails">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        @if($absence->statut == 'non_justifiee' || $absence->statut == 'en_attente')
                                                            <button type="button" class="btn btn-outline-success" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#justifierModal{{ $absence->id }}" 
                                                                    title="Justifier">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Modal de détails -->
                                            <div class="modal fade" id="detailsModal{{ $absence->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Détails de l'absence</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <h6>Informations générales</h6>
                                                                    <p><strong>Date :</strong> {{ $absence->date_absence->format('d/m/Y') }}</p>
                                                                    <p><strong>Matière :</strong> {{ $absence->matiere->nom ?? 'Journée complète' }}</p>
                                                                    <p><strong>Statut :</strong> 
                                                                        @switch($absence->statut)
                                                                            @case('justifiee')
                                                                                <span class="badge bg-success">Justifiée</span>
                                                                                @break
                                                                            @case('non_justifiee')
                                                                                <span class="badge bg-danger">Non Justifiée</span>
                                                                                @break
                                                                            @case('en_attente')
                                                                                <span class="badge bg-warning">En Attente</span>
                                                                                @break
                                                                        @endswitch
                                                                    </p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h6>Détails</h6>
                                                                    @if($absence->motif_absence)
                                                                        <p><strong>Motif :</strong> {{ $absence->motif_absence }}</p>
                                                                    @endif
                                                                    @if($absence->motif_justification)
                                                                        <p><strong>Justification :</strong> {{ $absence->motif_justification }}</p>
                                                                    @endif
                                                                    @if($absence->piece_jointe)
                                                                        <p><strong>Pièce jointe :</strong> 
                                                                            <a href="{{ asset('storage/' . $absence->piece_jointe) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                                <i class="fas fa-download me-1"></i>Télécharger
                                                                            </a>
                                                                        </p>
                                                                    @endif
                                                                    <p><strong>Enregistrée le :</strong> {{ $absence->created_at->format('d/m/Y à H:i') }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal de justification -->
                                            @if($absence->statut == 'non_justifiee' || $absence->statut == 'en_attente')
                                                <div class="modal fade" id="justifierModal{{ $absence->id }}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Justifier l'absence</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form action="{{ route('parent.absences.justifier', $absence) }}" method="POST" enctype="multipart/form-data">
                                                                @csrf
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Date d'absence</label>
                                                                        <input type="text" class="form-control" value="{{ $absence->date_absence->format('d/m/Y') }}" readonly>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Matière</label>
                                                                        <input type="text" class="form-control" value="{{ $absence->matiere->nom ?? 'Journée complète' }}" readonly>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Motif de justification <span class="text-danger">*</span></label>
                                                                        <textarea name="motif_justification" class="form-control" rows="3" required></textarea>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Pièce jointe (optionnel)</label>
                                                                        <input type="file" name="piece_jointe" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                                                        <small class="form-text text-muted">Formats acceptés: PDF, JPG, PNG (max 2MB)</small>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                    <button type="submit" class="btn btn-success">Justifier</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center text-muted py-5">
                    <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                    <h5>Aucune absence</h5>
                    <p>Cet enfant n'a aucune absence enregistrée.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
