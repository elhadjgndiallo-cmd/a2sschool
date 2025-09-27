@extends('layouts.app')

@section('title', 'Absences de Mes Enfants')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
                <h1 class="h3 mb-0">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Absences de Mes Enfants
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="{{ route('parent.absences.export', ['eleve' => 'all']) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-download me-1"></i>
                        <span class="d-none d-sm-inline">Exporter</span>
                    </a>
                </div>
            </div>

            @if(isset($message))
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ $message }}
                </div>
            @endif

            @if($enfants->count() > 0)
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

                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Filtres</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('parent.absences.index') }}">
                            <div class="row g-3">
                                <div class="col-12 col-sm-6 col-md-4">
                                    <label class="form-label">Enfant</label>
                                    <select name="eleve_id" class="form-select">
                                        <option value="">Tous les enfants</option>
                                        @foreach($enfants as $enfant)
                                            <option value="{{ $enfant->id }}" {{ request('eleve_id') == $enfant->id ? 'selected' : '' }}>
                                                {{ $enfant->nom }} {{ $enfant->prenom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <label class="form-label">Statut</label>
                                    <select name="statut" class="form-select">
                                        <option value="">Tous les statuts</option>
                                        <option value="justifiee" {{ request('statut') == 'justifiee' ? 'selected' : '' }}>Justifiée</option>
                                        <option value="non_justifiee" {{ request('statut') == 'non_justifiee' ? 'selected' : '' }}>Non Justifiée</option>
                                        <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En Attente</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <label class="form-label">Période</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}" placeholder="Début">
                                        </div>
                                        <div class="col-6">
                                            <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}" placeholder="Fin">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>Filtrer
                                    </button>
                                    <a href="{{ route('parent.absences.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Effacer
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Liste des absences -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Liste des Absences</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Enfant</th>
                                        <th>Matière</th>
                                        <th>Statut</th>
                                        <th>Motif</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($absences as $absence)
                                        <tr>
                                            <td>{{ $absence->date_absence->format('d/m/Y') }}</td>
                                            <td>
                                                <strong>{{ $absence->eleve->utilisateur->nom }} {{ $absence->eleve->utilisateur->prenom }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $absence->eleve->classe->nom ?? 'N/A' }}</small>
                                            </td>
                                            <td>{{ $absence->matiere->nom ?? 'Journée complète' }}</td>
                                            <td>
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
                                                <a href="{{ route('parent.absences.show', $absence->eleve) }}" class="btn btn-sm btn-outline-primary" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($absence->statut == 'non_justifiee' || $absence->statut == 'en_attente')
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#justifierModal{{ $absence->id }}" 
                                                            title="Justifier">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>

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
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                                <br>
                                                Aucune absence trouvée
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                @if($absences->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $absences->links('vendor.pagination.custom') }}
                    </div>
                @endif

            @else
                <div class="text-center text-muted py-5">
                    <i class="fas fa-child fa-3x mb-3"></i>
                    <h5>Aucun enfant trouvé</h5>
                    <p>Veuillez contacter l'administration pour associer vos enfants à votre compte.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

