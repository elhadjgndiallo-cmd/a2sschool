@extends('layouts.app')

@section('title', 'Notes de Mes Enfants')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
                <h1 class="h3 mb-0">
                    <i class="fas fa-chart-line text-primary me-2"></i>
                    Notes de Mes Enfants
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="{{ route('parent.notes.export', ['eleve' => 'all']) }}" class="btn btn-outline-secondary">
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
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4>{{ $stats['total_notes'] }}</h4>
                                <p class="mb-0">Total Notes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4>{{ number_format($stats['moyenne_generale'], 2) }}</h4>
                                <p class="mb-0">Moyenne Générale</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h4>{{ $stats['notes_sup_10'] }}</h4>
                                <p class="mb-0">Notes ≥ 10</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h4>{{ $stats['notes_inf_10'] }}</h4>
                                <p class="mb-0">Notes < 10</p>
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
                        <form method="GET" action="{{ route('parent.notes.index') }}">
                            <div class="row g-3">
                                <div class="col-12 col-sm-6 col-md-3">
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
                                <div class="col-12 col-sm-6 col-md-3">
                                    <label class="form-label">Matière</label>
                                    <select name="matiere_id" class="form-select">
                                        <option value="">Toutes les matières</option>
                                        @foreach($matieres as $matiere)
                                            <option value="{{ $matiere->id }}" {{ request('matiere_id') == $matiere->id ? 'selected' : '' }}>
                                                {{ $matiere->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <label class="form-label">Type d'évaluation</label>
                                    <select name="type_evaluation" class="form-select">
                                        <option value="">Tous les types</option>
                                        <option value="devoir" {{ request('type_evaluation') == 'devoir' ? 'selected' : '' }}>Devoir</option>
                                        <option value="composition" {{ request('type_evaluation') == 'composition' ? 'selected' : '' }}>Composition</option>
                                        <option value="examen" {{ request('type_evaluation') == 'examen' ? 'selected' : '' }}>Examen</option>
                                        <option value="oral" {{ request('type_evaluation') == 'oral' ? 'selected' : '' }}>Oral</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
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
                                    <a href="{{ route('parent.notes.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Effacer
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Liste des notes -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Liste des Notes</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Enfant</th>
                                        <th>Matière</th>
                                        <th>Type</th>
                                        <th>Note</th>
                                        <th>Enseignant</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($notes as $note)
                                        <tr>
                                            <td>{{ $note->date_evaluation->format('d/m/Y') }}</td>
                                            <td>
                                                <strong>{{ $note->eleve->utilisateur->nom }} {{ $note->eleve->utilisateur->prenom }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $note->eleve->classe->nom ?? 'N/A' }}</small>
                                            </td>
                                            <td>{{ $note->matiere->nom }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ ucfirst($note->type_evaluation) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $note->note_sur >= 10 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ number_format($note->note_sur, 2) }}/20
                                                </span>
                                            </td>
                                            <td>{{ $note->enseignant->utilisateur->nom ?? 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('parent.notes.show', $note->eleve) }}" class="btn btn-sm btn-outline-primary" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-chart-line fa-2x mb-2"></i>
                                                <br>
                                                Aucune note trouvée
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                @if($notes->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <small class="text-muted">
                                Affichage de {{ $notes->firstItem() ?? 0 }} à {{ $notes->lastItem() ?? 0 }} sur {{ $notes->total() }} note{{ $notes->total() > 1 ? 's' : '' }}
                            </small>
                        </div>
                        <div>
                            {{ $notes->appends(request()->query())->links('vendor.pagination.custom') }}
                        </div>
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
