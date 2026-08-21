@extends('layouts.app')

@section('title', 'Détails Matière - ' . $matiere->nom)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <span class="badge me-2" style="background-color: {{ $matiere->couleur }}; color: white;">{{ $matiere->code }}</span>
        {{ $matiere->nom }}
        @if(!empty($anneeScolaireActive))
            <small class="text-muted fs-6">— année {{ $anneeScolaireActive->nom }}</small>
        @endif
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('matieres.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $statistiques['total_enseignants'] }}</h4>
                        <p class="mb-0">Enseignants (EDT)</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chalkboard-teacher fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $statistiques['total_notes'] }}</h4>
                        <p class="mb-0">Notes Saisies</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clipboard-list fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $statistiques['moyenne_generale'] ? round($statistiques['moyenne_generale'], 2) : '—' }}{{ $statistiques['moyenne_generale'] ? '/20' : '' }}</h4>
                        <p class="mb-0">Moyenne</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chart-bar fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $statistiques['classes_enseignees'] }}</h4>
                        <p class="mb-0">Classes</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informations Détaillées</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td><strong>Code:</strong></td>
                        <td>{{ $matiere->code }}</td>
                    </tr>
                    <tr>
                        <td><strong>Coefficient:</strong></td>
                        <td><span class="badge bg-secondary fs-6">{{ $matiere->coefficient }}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Couleur:</strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div style="width: 20px; height: 20px; background-color: {{ $matiere->couleur }}; border: 1px solid #ddd; border-radius: 3px; margin-right: 10px;"></div>
                                {{ $matiere->couleur }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Statut:</strong></td>
                        <td>
                            @if($matiere->actif)
                            <span class="badge bg-success">Actif</span>
                            @else
                            <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Description:</strong></td>
                        <td>{{ $matiere->description ?: 'Aucune description' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    Enseignants assignés (emploi du temps)
                    @if(!empty($anneeScolaireActive))
                        <small class="text-muted">— {{ $anneeScolaireActive->nom }}</small>
                    @endif
                </h5>
            </div>
            <div class="card-body p-0">
                @if(($enseignantsEdt ?? collect())->count() > 0)
                <ul class="list-group list-group-flush">
                    @foreach($enseignantsEdt as $enseignant)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-user me-2 text-muted"></i>
                            {{ $enseignant->nom_complet }}
                        </span>
                        @if($enseignant->numero_employe)
                            <span class="badge bg-light text-dark">{{ $enseignant->numero_employe }}</span>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Aucun enseignant pour cette matière dans l'EDT de l'année active
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Classes où la matière est enseignée -->
<div class="row mt-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    Classes où cette matière est enseignée
                    @if(!empty($anneeScolaireActive))
                        <small class="text-muted">— {{ $anneeScolaireActive->nom }}</small>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                @if(($emploisTemps ?? collect())->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Classe</th>
                                <th>Niveau</th>
                                <th>Enseignant</th>
                                <th>Jour</th>
                                <th>Heure</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($emploisTemps as $emploi)
                            <tr>
                                <td><strong>{{ $emploi->classe?->nom ?? '—' }}</strong></td>
                                <td>{{ $emploi->classe?->niveau ?? '—' }}</td>
                                <td>{{ $emploi->enseignant?->nom_complet ?: 'Non assigné' }}</td>
                                <td>{{ $emploi->jour_semaine ? ucfirst($emploi->jour_semaine) : '—' }}</td>
                                <td>
                                    @php
                                        $debut = $emploi->heure_debut;
                                        $fin = $emploi->heure_fin;
                                        $debutFmt = $debut instanceof \Carbon\Carbon ? $debut->format('H:i') : \Carbon\Carbon::parse($debut)->format('H:i');
                                        $finFmt = $fin instanceof \Carbon\Carbon ? $fin->format('H:i') : \Carbon\Carbon::parse($fin)->format('H:i');
                                    @endphp
                                    {{ $debutFmt }} - {{ $finFmt }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-calendar-times me-1"></i>
                    Aucun créneau d'emploi du temps pour l'année active
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 no-print mt-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions</h5>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('matieres.edit', $matiere) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Modifier
            </a>
            @if($matiere->actif)
                <form method="POST" action="{{ route('matieres.deactivate', $matiere) }}" class="d-inline"
                      onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver la matière {{ $matiere->nom }} ?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-pause me-1"></i> Désactiver
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('matieres.reactivate', $matiere) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-play me-1"></i> Réactiver
                    </button>
                </form>
            @endif
            <form method="POST" action="{{ route('matieres.delete-permanent', $matiere) }}" class="d-inline"
                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement la matière {{ $matiere->nom }} ?\n\nCette action est irréversible !')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-trash-alt me-1"></i> Supprimer définitivement
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
