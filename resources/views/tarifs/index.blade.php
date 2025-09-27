@extends('layouts.app')

@section('title', 'Tarifs par Classe')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-table me-2"></i>Tarifs par Classe
            </h1>
            <p class="text-muted">Gestion des tarifs de scolarité par classe et année scolaire</p>
        </div>
        <a href="{{ route('tarifs.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouveau Tarif
        </a>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filtres
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('tarifs.index') }}">
                <div class="row g-3">
                    <!-- Filtre par classe -->
                    <div class="col-md-4">
                        <label for="classe_id" class="form-label">Classe</label>
                        <select class="form-select" id="classe_id" name="classe_id">
                            <option value="">Toutes les classes</option>
                            @foreach($classes as $classe)
                            <option value="{{ $classe->id }}" {{ request('classe_id') == $classe->id ? 'selected' : '' }}>
                                {{ $classe->nom }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtre par année scolaire -->
                    <div class="col-md-4">
                        <label for="annee_scolaire" class="form-label">Année Scolaire</label>
                        <select class="form-select" id="annee_scolaire" name="annee_scolaire">
                            <option value="">Toutes les années</option>
                            @foreach($anneesScolaires as $annee)
                            <option value="{{ $annee }}" {{ request('annee_scolaire') == $annee ? 'selected' : '' }}>
                                {{ $annee }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtre par statut -->
                    <div class="col-md-4">
                        <label for="actif" class="form-label">Statut</label>
                        <select class="form-select" id="actif" name="actif">
                            <option value="">Tous les statuts</option>
                            <option value="1" {{ request('actif') === '1' ? 'selected' : '' }}>Actif</option>
                            <option value="0" {{ request('actif') === '0' ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search me-2"></i>Filtrer
                    </button>
                    <a href="{{ route('tarifs.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des tarifs -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Liste des Tarifs
                <span class="badge bg-primary ms-2">{{ $tarifs->total() }}</span>
            </h5>
        </div>
        <div class="alert alert-info mx-3 mt-3 mb-0">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Information:</strong> Les calculs du total annuel sont basés sur <strong>9 mois d'école</strong> 
            (Octobre à Juin). Les frais de réinscription représentent 50% des frais d'inscription.
        </div>
        <div class="card-body">
            @if($tarifs->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Classe</th>
                            <th>Année Scolaire</th>
                            <th>Inscription</th>
                            <th>Réinscription</th>
                            <th>Mensuel</th>
                            <th>Total Annuel</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tarifs as $tarif)
                        @php
                            // Calcul du total mensuel (scolarité + cantine + transport)
                            $totalMensuel = $tarif->frais_scolarite_mensuel + 
                                           $tarif->frais_cantine_mensuel + 
                                           $tarif->frais_transport_mensuel;
                            
                            // Calcul du total annuel sur 9 mois d'école
                            $totalAnnuel = $tarif->frais_inscription + ($totalMensuel * 9) + 
                                          $tarif->frais_uniforme + $tarif->frais_livres + $tarif->frais_autres;
                            
                            // Frais de réinscription configurés ou 50% de l'inscription si non défini
                            $fraisReinscription = $tarif->frais_reinscription > 0 ? $tarif->frais_reinscription : ($tarif->frais_inscription * 0.5);
                        @endphp
                        <tr>
                            <td>
                                @if($tarif->classe)
                                    <strong class="text-primary">{{ $tarif->classe->nom }}</strong>
                                    @if($tarif->classe->niveau)
                                    <br>
                                    <small class="text-muted">{{ $tarif->classe->niveau }}</small>
                                    @endif
                                @else
                                    <span class="text-danger">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Classe supprimée (ID: {{ $tarif->classe_id }})
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info fs-6">{{ $tarif->annee_scolaire }}</span>
                            </td>
                            <td>
                                <strong class="text-success">{{ number_format($tarif->frais_inscription, 0, ',', ' ') }}</strong>
                                <br>
                                <small class="text-muted">GNF</small>
                            </td>
                            <td>
                                <strong class="text-warning">{{ number_format($fraisReinscription, 0, ',', ' ') }}</strong>
                                <br>
                                <small class="text-muted">GNF</small>
                            </td>
                            <td>
                                <strong class="text-primary">{{ number_format($totalMensuel, 0, ',', ' ') }}</strong>
                                <br>
                                <small class="text-muted">GNF/mois</small>
                                @if($tarif->frais_cantine_mensuel > 0 || $tarif->frais_transport_mensuel > 0)
                                <br>
                                <small class="text-info">
                                    @if($tarif->frais_cantine_mensuel > 0)
                                        <i class="fas fa-utensils me-1"></i>{{ number_format($tarif->frais_cantine_mensuel, 0, ',', ' ') }}
                                    @endif
                                    @if($tarif->frais_transport_mensuel > 0)
                                        <i class="fas fa-bus me-1"></i>{{ number_format($tarif->frais_transport_mensuel, 0, ',', ' ') }}
                                    @endif
                                </small>
                                @endif
                            </td>
                            <td>
                                <strong class="text-danger fs-5">{{ number_format($totalAnnuel, 0, ',', ' ') }}</strong>
                                <br>
                                <small class="text-muted">GNF (9 mois)</small>
                                @if($tarif->frais_uniforme > 0 || $tarif->frais_livres > 0 || $tarif->frais_autres > 0)
                                <br>
                                <small class="text-secondary">
                                    + frais uniques: {{ number_format($tarif->frais_uniforme + $tarif->frais_livres + $tarif->frais_autres, 0, ',', ' ') }}
                                </small>
                                @endif
                            </td>
                            <td>
                                @if($tarif->actif)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Actif
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-pause-circle me-1"></i>Inactif
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('tarifs.show', $tarif) }}" class="btn btn-sm btn-outline-info" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('tarifs.edit', $tarif) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('tarifs.destroy', $tarif) }}" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce tarif ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <small class="text-muted">
                        Affichage de {{ $tarifs->firstItem() ?? 0 }} à {{ $tarifs->lastItem() ?? 0 }} sur {{ $tarifs->total() }} tarif{{ $tarifs->total() > 1 ? 's' : '' }}
                    </small>
                </div>
                <div>
                    {{ $tarifs->appends(request()->query())->links('vendor.pagination.custom') }}
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-table fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucun tarif trouvé</h5>
                <p class="text-muted">Commencez par créer un tarif pour une classe.</p>
                <a href="{{ route('tarifs.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Créer le premier tarif
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

