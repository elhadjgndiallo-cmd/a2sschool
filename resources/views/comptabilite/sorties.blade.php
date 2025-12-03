@extends('layouts.app')

@section('title', 'Sorties - Comptabilité')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-arrow-down text-danger me-2"></i>
                    Sorties (Dépenses)
                </h2>
                <div class="btn-group">
                    <a href="{{ route('depenses.create') }}" class="btn btn-danger">
                        <i class="fas fa-plus me-1"></i>Nouvelle Dépense
                    </a>
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>Imprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filtres
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('comptabilite.sorties') }}">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="annee_scolaire_id" class="form-label">Année scolaire</label>
                                <select class="form-select" id="annee_scolaire_id" name="annee_scolaire_id">
                                    @foreach(\App\Models\AnneeScolaire::orderBy('date_debut','desc')->get() as $annee)
                                        <option value="{{ $annee->id }}" {{ request('annee_scolaire_id') == $annee->id ? 'selected' : ($annee->active && !request('annee_scolaire_id') ? 'selected' : '') }}>
                                            {{ $annee->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="date_debut" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                       value="{{ request('date_debut') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="date_fin" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                       value="{{ request('date_fin') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="type_depense" class="form-label">Type de dépense</label>
                                <select class="form-select" id="type_depense" name="type_depense">
                                    <option value="">Tous les types</option>
                                    @foreach($typesDepense as $type)
                                        <option value="{{ $type }}" {{ request('type_depense') == $type ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i>Filtrer
                                </button>
                                <a href="{{ route('comptabilite.sorties') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-refresh me-1"></i>Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($statsSorties['total'], 0, ',', ' ') }}</h3>
                    <small>Total (GNF)</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $statsSorties['nombre'] }}</h3>
                    <small>Nombre de dépenses</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($statsSorties['moyenne'], 0, ',', ' ') }}</h3>
                    <small>Moyenne (GNF)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des sorties -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Liste des Dépenses
                    </h5>
                </div>
                <div class="card-body">
                    @if($sorties->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Libellé</th>
                                        <th>Type</th>
                                        <th class="text-end" style="width: 150px;">Montant</th>
                                        <th>Enregistré par</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sorties as $sortie)
                                        <tr>
                                            <td>
                                                <i class="fas fa-calendar text-muted me-1"></i>
                                                @if(isset($sortie->date) && $sortie->date)
                                                    {{ $sortie->date->format('d/m/Y') }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $sortie->libelle ?? 'N/A' }}</strong>
                                                @if(isset($sortie->description) && $sortie->description)
                                                    <br><small class="text-muted">{{ Str::limit($sortie->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ isset($sortie->type_depense) && $sortie->type_depense == 'salaire_enseignant' ? 'primary' : (isset($sortie->type_depense) && $sortie->type_depense == 'achat_materiel' ? 'success' : 'warning') }}">
                                                    {{ isset($sortie->type_depense) ? ucfirst(str_replace('_', ' ', $sortie->type_depense)) : 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="text-end" style="width: 150px;">
                                                <strong class="text-danger">
                                                    {{ number_format($sortie->montant ?? 0, 0, ',', ' ') }} GNF
                                                </strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @php
                                                        $personne = $sortie->approuve_par ?? $sortie->paye_par ?? null;
                                                        $photoProfil = $personne && isset($personne->photo_profil) ? $personne->photo_profil : null;
                                                    @endphp
                                                    @if($photoProfil)
                                                        <img src="{{ asset('storage/' . $photoProfil) }}" 
                                                             alt="Photo" class="rounded-circle me-2" 
                                                             style="width: 30px; height: 30px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                             style="width: 30px; height: 30px;">
                                                            <i class="fas fa-user text-white" style="font-size: 12px;"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        @if($personne)
                                                            <div class="fw-bold">{{ $personne->nom ?? 'N/A' }} {{ $personne->prenom ?? '' }}</div>
                                                            <small class="text-muted">{{ ucfirst($personne->role ?? 'Système') }}</small>
                                                        @else
                                                            <div class="fw-bold">Non assigné</div>
                                                            <small class="text-muted">En attente</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if(isset($sortie->type) && $sortie->type == 'depense' && isset($sortie->data))
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('depenses.show', $sortie->data) }}" class="btn btn-outline-primary" title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('depenses.edit', $sortie->data) }}" class="btn btn-outline-warning" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="POST" action="{{ route('depenses.destroy', $sortie->data) }}" 
                                                              style="display: inline;" 
                                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette dépense ?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @elseif(isset($sortie->type) && $sortie->type == 'salaire' && isset($sortie->data))
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('salaires.show', $sortie->data) }}" class="btn btn-outline-primary" title="Voir">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            @if(auth()->user()->hasPermission('salaires.delete'))
                                                                <form method="POST" action="{{ route('salaires.destroy', $sortie->data) }}" 
                                                                      style="display: inline;" 
                                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce paiement de salaire ?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                        <span class="badge bg-info">Salaire</span>
                                                    </div>
                                                @elseif(isset($sortie->data))
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('depenses.show', $sortie->data) }}" class="btn btn-outline-primary" title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Simple -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <small class="text-muted">
                                    Affichage de {{ $sorties->firstItem() }} à {{ $sorties->lastItem() }} 
                                    sur {{ $sorties->total() }} sorties
                                </small>
                            </div>
                            <div>
                                <nav aria-label="Pagination">
                                    <ul class="pagination pagination-simple">
                                        <!-- Bouton Précédent -->
                                        @if($sorties->currentPage() > 1)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $sorties->previousPageUrl() }}" aria-label="Précédent">
                                                    <i class="fas fa-chevron-left"></i> Précédent
                                                </a>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link">
                                                    <i class="fas fa-chevron-left"></i> Précédent
                                                </span>
                                            </li>
                                        @endif

                                        <!-- Numéros de pages -->
                                        @php
                                            $currentPage = $sorties->currentPage();
                                            $lastPage = $sorties->lastPage();
                                            $start = max(1, $currentPage - 2);
                                            $end = min($lastPage, $currentPage + 2);
                                        @endphp

                                        @if($start <= $end)
                                            @for($i = $start; $i <= $end; $i++)
                                                <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                                    <a class="page-link" href="{{ $sorties->url($i) }}">{{ $i }}</a>
                                                </li>
                                            @endfor
                                        @endif

                                        <!-- Bouton Suivant -->
                                        @if($sorties->hasMorePages())
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $sorties->nextPageUrl() }}" aria-label="Suivant">
                                                    Suivant <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link">
                                                    Suivant <i class="fas fa-chevron-right"></i>
                                                </span>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune dépense trouvée</h5>
                            <p class="text-muted">Commencez par ajouter une nouvelle dépense.</p>
                            <a href="{{ route('depenses.create') }}" class="btn btn-danger">
                                <i class="fas fa-plus me-1"></i>Ajouter une dépense
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

