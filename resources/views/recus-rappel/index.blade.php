@extends('layouts.app')

@section('title', 'Reçus de Rappel')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-bell mr-2"></i>
                        Reçus de Rappel
                    </h3>
                    <div>
                        <a href="{{ route('recus-rappel.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i>
                            Nouveau Reçu de Rappel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <select name="classe_id" class="form-select">
                                        <option value="">Toutes les classes</option>
                                        @foreach($classes as $classe)
                                            <option value="{{ $classe->id }}" {{ request('classe_id') == $classe->id ? 'selected' : '' }}>
                                                {{ $classe->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="statut" class="form-select">
                                        <option value="">Tous les statuts</option>
                                        <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                                        <option value="expire" {{ request('statut') == 'expire' ? 'selected' : '' }}>Expiré</option>
                                        <option value="paye" {{ request('statut') == 'paye' ? 'selected' : '' }}>Payé</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date_debut" class="form-control" placeholder="Date début" value="{{ request('date_debut') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date_fin" class="form-control" placeholder="Date fin" value="{{ request('date_fin') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tableau des reçus de rappel -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>N° Reçu</th>
                                    <th>Élève</th>
                                    <th>Classe</th>
                                    <th>Montant Total</th>
                                    <th>Montant Payé</th>
                                    <th>Montant Restant</th>
                                    <th>Montant à Payer</th>
                                    <th>Date Rappel</th>
                                    <th>Date Échéance</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recusRappel as $recu)
                                    <tr>
                                        <td>
                                            <span class="badge bg-info">{{ $recu->numero_recu_rappel }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $recu->eleve->nom_complet }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $recu->eleve->numero_etudiant }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $recu->eleve->classe->nom ?? 'N/A' }}</td>
                                        <td>{{ number_format($recu->montant_total_du, 0, ',', ' ') }} GNF</td>
                                        <td>{{ number_format($recu->montant_paye, 0, ',', ' ') }} GNF</td>
                                        <td>{{ number_format($recu->montant_restant, 0, ',', ' ') }} GNF</td>
                                        <td>
                                            @if($recu->montant_a_payer)
                                                <span class="text-primary fw-bold">{{ number_format($recu->montant_a_payer, 0, ',', ' ') }} GNF</span>
                                            @else
                                                <span class="text-muted">Non défini</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($recu->date_rappel)->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="{{ $recu->isExpire() ? 'text-danger' : 'text-success' }}">
                                                {{ \Carbon\Carbon::parse($recu->date_echeance)->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            @switch($recu->statut)
                                                @case('actif')
                                                    <span class="badge bg-success">Actif</span>
                                                    @break
                                                @case('expire')
                                                    <span class="badge bg-danger">Expiré</span>
                                                    @break
                                                @case('paye')
                                                    <span class="badge bg-primary">Payé</span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('recus-rappel.show', $recu) }}" class="btn btn-sm btn-outline-info" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('recus-rappel.pdf', $recu) }}" class="btn btn-sm btn-outline-success" title="Imprimer">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                <a href="{{ route('recus-rappel.edit', $recu) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('recus-rappel.destroy', $recu) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce reçu de rappel ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <br>
                                            Aucun reçu de rappel trouvé
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $recusRappel->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
