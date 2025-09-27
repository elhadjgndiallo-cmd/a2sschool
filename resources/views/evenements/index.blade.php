@extends('layouts.app')

@section('title', 'Événements')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Gestion des Événements
                    </h3>
                    <div>
                        @if(auth()->user()->hasPermission('evenements.create'))
                            <a href="{{ route('evenements.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-1"></i>
                                Nouvel Événement
                            </a>
                        @endif
                        <a href="{{ route('evenements.calendrier') }}" class="btn btn-info">
                            <i class="fas fa-calendar mr-1"></i>
                            Calendrier
                        </a>
                        @if(auth()->user()->hasPermission('evenements.manage_all'))
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-cog mr-1"></i>
                                    Actions Admin
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="exportEvenements()">
                                        <i class="fas fa-download mr-1"></i>Exporter
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="bulkDelete()">
                                        <i class="fas fa-trash mr-1"></i>Suppression en lot
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="generateReport()">
                                        <i class="fas fa-chart-bar mr-1"></i>Rapport
                                    </a></li>
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    @if(auth()->user()->hasPermission('evenements.manage_all'))
                        <!-- Statistiques pour l'admin -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h4>{{ $evenements->total() }}</h4>
                                        <p class="mb-0">Total Événements</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h4>{{ $evenements->filter(function($event) { return $event->estAVenir(); })->count() }}</h4>
                                        <p class="mb-0">À Venir</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h4>{{ $evenements->filter(function($event) { return $event->estEnCours(); })->count() }}</h4>
                                        <p class="mb-0">En Cours</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h4>{{ $evenements->where('public', true)->count() }}</h4>
                                        <p class="mb-0">Publics</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Filtres -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="type">Type d'événement</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="">Tous les types</option>
                                    <option value="cours" {{ request('type') == 'cours' ? 'selected' : '' }}>Cours</option>
                                    <option value="examen" {{ request('type') == 'examen' ? 'selected' : '' }}>Examen</option>
                                    <option value="reunion" {{ request('type') == 'reunion' ? 'selected' : '' }}>Réunion</option>
                                    <option value="conge" {{ request('type') == 'conge' ? 'selected' : '' }}>Congé</option>
                                    <option value="autre" {{ request('type') == 'autre' ? 'selected' : '' }}>Autre</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="classe_id">Classe</label>
                                <select name="classe_id" id="classe_id" class="form-control">
                                    <option value="">Toutes les classes</option>
                                    @foreach($classes as $classe)
                                        <option value="{{ $classe->id }}" {{ request('classe_id') == $classe->id ? 'selected' : '' }}>
                                            {{ $classe->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_debut">Date début</label>
                                <input type="date" name="date_debut" id="date_debut" class="form-control" value="{{ request('date_debut') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date_fin">Date fin</label>
                                <input type="date" name="date_fin" id="date_fin" class="form-control" value="{{ request('date_fin') }}">
                            </div>
                            @if(auth()->user()->hasPermission('evenements.manage_all'))
                                <div class="col-md-2">
                                    <label for="createur_id">Créateur</label>
                                    <select name="createur_id" id="createur_id" class="form-control">
                                        <option value="">Tous les créateurs</option>
                                        @foreach(\App\Models\Utilisateur::whereIn('role', ['admin', 'personnel_admin', 'teacher'])->get() as $createur)
                                            <option value="{{ $createur->id }}" {{ request('createur_id') == $createur->id ? 'selected' : '' }}>
                                                {{ $createur->nom ?? $createur->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search mr-1"></i>
                                        Filtrer
                                    </button>
                                    <a href="{{ route('evenements.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times mr-1"></i>
                                        Effacer
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Liste des événements -->
                    @if($evenements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Titre</th>
                                        <th>Type</th>
                                        <th>Date début</th>
                                        <th>Date fin</th>
                                        <th>Lieu</th>
                                        <th>Classe</th>
                                        <th>Public</th>
                                        <th>Créateur</th>
                                        @if(auth()->user()->hasPermission('evenements.manage_all'))
                                            <th>Statut</th>
                                            <th>Créé le</th>
                                        @endif
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($evenements as $evenement)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="color-indicator" style="background-color: {{ $evenement->couleur }}; width: 15px; height: 15px; border-radius: 3px; margin-right: 8px; border: 1px solid #dee2e6;"></div>
                                                    <div>
                                                        <strong style="color: #333;">{{ $evenement->titre }}</strong>
                                                        @if(auth()->user()->hasPermission('evenements.manage_all') && $evenement->createur_id !== auth()->id())
                                                            <br><small class="text-muted">Créé par {{ $evenement->createur->nom ?? 'N/A' }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $evenement->type == 'examen' ? 'danger' : ($evenement->type == 'cours' ? 'primary' : 'info') }}">
                                                    {{ ucfirst($evenement->type) }}
                                                </span>
                                            </td>
                                            <td>{{ $evenement->date_debut->format('d/m/Y') }}</td>
                                            <td>{{ $evenement->date_fin->format('d/m/Y') }}</td>
                                            <td>{{ $evenement->lieu ?? '-' }}</td>
                                            <td>{{ $evenement->classe ? $evenement->classe->nom : 'Toutes' }}</td>
                                            <td>
                                                @if($evenement->public)
                                                    <span class="badge badge-success">Public</span>
                                                @else
                                                    <span class="badge badge-warning">Privé</span>
                                                @endif
                                            </td>
                                            <td>{{ $evenement->createur->nom ?? 'N/A' }}</td>
                                            @if(auth()->user()->hasPermission('evenements.manage_all'))
                                                <td>
                                                    @if($evenement->estAVenir())
                                                        <span class="badge badge-success">À venir</span>
                                                    @elseif($evenement->estEnCours())
                                                        <span class="badge badge-warning">En cours</span>
                                                    @else
                                                        <span class="badge badge-secondary">Terminé</span>
                                                    @endif
                                                </td>
                                                <td>{{ $evenement->created_at->format('d/m/Y H:i') }}</td>
                                            @endif
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('evenements.show', $evenement->id) }}" class="btn btn-sm btn-info" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(auth()->user()->hasPermission('evenements.edit') && (auth()->user()->hasPermission('evenements.manage_all') || $evenement->createur_id === Auth::id()))
                                                        <a href="{{ route('evenements.edit', $evenement->id) }}" class="btn btn-sm btn-warning" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    @if(auth()->user()->hasPermission('evenements.delete') && (auth()->user()->hasPermission('evenements.manage_all') || $evenement->createur_id === Auth::id()))
                                                        <form action="{{ route('evenements.destroy', $evenement->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
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
                            {{ $evenements->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun événement trouvé</h5>
                            <p class="text-muted">Il n'y a pas d'événements correspondant à vos critères de recherche.</p>
                            @if(auth()->user()->hasPermission('evenements.create'))
                                <a href="{{ route('evenements.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus mr-1"></i>
                                    Créer le premier événement
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
/* Amélioration de la lisibilité des textes */
.color-indicator {
    border: 1px solid #dee2e6 !important;
}

.table td {
    color: #333 !important;
}

.table th {
    color: #495057 !important;
    font-weight: 600;
}

.card-title {
    color: #333 !important;
}

.text-muted {
    color: #6c757d !important;
}

/* Amélioration des badges */
.badge {
    font-weight: 500;
}

/* Amélioration des boutons */
.btn {
    font-weight: 500;
}

/* Actions admin */
.dropdown-menu {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}
</style>

<script>
// Actions pour l'administrateur
function exportEvenements() {
    alert('Fonction d\'export en cours de développement...');
}

function bulkDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer plusieurs événements ?')) {
        alert('Fonction de suppression en lot en cours de développement...');
    }
}

function generateReport() {
    alert('Fonction de rapport en cours de développement...');
}
</script>
