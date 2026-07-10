@extends('layouts.app')

@section('title', 'Résultats Annuels - ' . $classe->nom)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-trophy me-2"></i>
        Résultats Annuels - {{ $classe->nom }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('notes.annuel.resultats.pdf', $classe->id) }}" 
               class="btn btn-sm btn-primary" target="_blank">
                <i class="fas fa-file-pdf me-1"></i>
                Télécharger PDF Résultats
            </a>
            <a href="{{ route('notes.annuel.detail-notes.imprimer', $classe->id) }}" 
               class="btn btn-sm btn-success" target="_blank">
                <i class="fas fa-print me-1"></i>
                Imprimer Détail Notes
            </a>
            <a href="{{ route('notes.annuel.satisfecit', $classe->id) }}" 
               class="btn btn-sm btn-warning" target="_blank">
                <i class="fas fa-award me-1"></i>
                Satisfécits (Top 5)
            </a>
        </div>
        <a href="{{ route('notes.annuel.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Informations de la classe -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong><i class="fas fa-school me-2"></i>Classe :</strong> {{ $classe->nom }}
                    </div>
                    <div class="col-md-4">
                        <strong><i class="fas fa-calendar-alt me-2"></i>Année scolaire :</strong> {{ $anneeScolaireActive->nom }}
                    </div>
                    <div class="col-md-4">
                        <strong><i class="fas fa-users me-2"></i>Effectif :</strong> {{ count($resultats) }} élèves
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tableau des résultats -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Classement Annuel
                </h5>
            </div>
            <div class="card-body">
                @if(count($resultats) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 8%">Matricule</th>
                                <th style="width: 12%">Nom</th>
                                <th style="width: 12%">Prénom</th>
                                <th style="width: 10%" class="text-center">Moyenne T1</th>
                                <th style="width: 8%" class="text-center">Rang T1</th>
                                <th style="width: 10%" class="text-center">Moyenne T2</th>
                                <th style="width: 8%" class="text-center">Rang T2</th>
                                @if($isPrimaire)
                                <th style="width: 10%" class="text-center">Moyenne T3</th>
                                <th style="width: 8%" class="text-center">Rang T3</th>
                                @endif
                                <th style="width: 10%" class="text-center">Moyenne Annuelle</th>
                                <th style="width: 8%" class="text-center">Rang Annuel</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resultats as $resultat)
                            <tr>
                                <td>{{ $resultat['matricule'] }}</td>
                                <td>{{ $resultat['eleve']->utilisateur->nom }}</td>
                                <td>{{ $resultat['eleve']->utilisateur->prenom }}</td>
                                <td class="text-center">
                                    @if($resultat['moyenneT1'] !== null)
                                        <span class="badge bg-secondary">{{ number_format($resultat['moyenneT1'], 2) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(isset($resultat['rangT1']))
                                        <strong>{{ $resultat['rangT1'] }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($resultat['moyenneT2'] !== null)
                                        <span class="badge bg-secondary">{{ number_format($resultat['moyenneT2'], 2) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(isset($resultat['rangT2']))
                                        <strong>{{ $resultat['rangT2'] }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                @if($isPrimaire)
                                <td class="text-center">
                                    @if($resultat['moyenneT3'] !== null)
                                        <span class="badge bg-secondary">{{ number_format($resultat['moyenneT3'], 2) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(isset($resultat['rangT3']))
                                        <strong>{{ $resultat['rangT3'] }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                @endif
                                <td class="text-center">
                                    @if($resultat['moyenneAnnuelle'] !== null)
                                        <span class="badge bg-primary fs-6">{{ number_format($resultat['moyenneAnnuelle'], 2) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(isset($resultat['rangAnnuel']))
                                        @if($resultat['rangAnnuel'] == 1)
                                            <span class="badge bg-success fs-6"><i class="fas fa-trophy me-1"></i>{{ $resultat['rangAnnuel'] }}</span>
                                        @elseif($resultat['rangAnnuel'] == 2)
                                            <span class="badge bg-info fs-6"><i class="fas fa-medal me-1"></i>{{ $resultat['rangAnnuel'] }}</span>
                                        @elseif($resultat['rangAnnuel'] == 3)
                                            <span class="badge bg-warning fs-6"><i class="fas fa-award me-1"></i>{{ $resultat['rangAnnuel'] }}</span>
                                        @else
                                            <strong class="fs-5">{{ $resultat['rangAnnuel'] }}</strong>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun résultat disponible</h5>
                    <p class="text-muted">Aucune note n'a été enregistrée pour cette classe.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Statistiques -->
@if(count($resultats) > 0)
<div class="row mt-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-trophy fa-2x text-success mb-2"></i>
                <h6 class="text-muted">Meilleure Moyenne</h6>
                @php
                    $meilleure = collect($resultats)->where('moyenneAnnuelle', '!==', null)->max('moyenneAnnuelle');
                @endphp
                <h3 class="mb-0">{{ $meilleure ? number_format($meilleure, 2) : '-' }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                <h6 class="text-muted">Moyenne Générale</h6>
                @php
                    $moyennes = collect($resultats)->whereNotNull('moyenneAnnuelle')->pluck('moyenneAnnuelle');
                    $moyenneGenerale = $moyennes->count() > 0 ? $moyennes->average() : 0;
                @endphp
                <h3 class="mb-0">{{ number_format($moyenneGenerale, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-users fa-2x text-info mb-2"></i>
                <h6 class="text-muted">Élèves avec notes</h6>
                @php
                    $elevesAvecNotes = collect($resultats)->whereNotNull('moyenneAnnuelle')->count();
                @endphp
                <h3 class="mb-0">{{ $elevesAvecNotes }} / {{ count($resultats) }}</h3>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
