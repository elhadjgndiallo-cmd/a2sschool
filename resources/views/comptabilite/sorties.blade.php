@extends('layouts.app')

@section('title', 'Sorties - Comptabilité')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-arrow-down text-danger me-2"></i>
                        <span class="d-none d-sm-inline">Sorties (Dépenses)</span>
                        <span class="d-sm-none">Sorties</span>
                    </h3>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>
                            <span class="d-none d-sm-inline">Imprimer</span>
                            <span class="d-sm-none">Print</span>
                        </button>
                        <a href="{{ route('depenses.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            <span class="d-none d-sm-inline">Nouvelle Dépense</span>
                            <span class="d-sm-none">Nouveau</span>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres compacts (comme liste élèves / entrées) -->
                    <form method="GET" action="{{ route('comptabilite.sorties') }}" class="mb-3">
                        <div class="row g-2">
                            <div class="col-12 col-sm-6 col-md-2">
                                <select class="form-control" name="annee_scolaire_id" onchange="this.form.submit()">
                                    @foreach($anneesScolaires ?? \App\Models\AnneeScolaire::orderBy('date_debut','desc')->get() as $annee)
                                        <option value="{{ $annee->id }}" {{ (string) request('annee_scolaire_id', $anneeScolaire->id) === (string) $annee->id ? 'selected' : '' }}>
                                            {{ $annee->nom }}{{ $annee->active ? ' (active)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-sm-6 col-md-2">
                                <input type="date"
                                       class="form-control"
                                       name="date_debut"
                                       value="{{ request('date_debut') }}"
                                       title="Date de début"
                                       placeholder="Date début">
                            </div>
                            <div class="col-6 col-sm-6 col-md-2">
                                <input type="date"
                                       class="form-control"
                                       name="date_fin"
                                       value="{{ request('date_fin') }}"
                                       title="Date de fin"
                                       placeholder="Date fin">
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <select class="form-control" name="type_depense">
                                    <option value="">Tous les types</option>
                                    @foreach($typesDepense as $type)
                                        <option value="{{ $type }}" {{ request('type_depense') == $type ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary flex-fill">
                                        <i class="fas fa-search"></i>
                                        <span class="d-none d-sm-inline">Filtrer</span>
                                    </button>
                                    <a href="{{ route('comptabilite.sorties') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Stats compactes -->
                    <div class="mb-3 d-flex flex-wrap gap-3 align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-coins text-danger me-1"></i>
                            Total : <strong class="text-danger">{{ number_format($statsSorties['total'], 0, ',', ' ') }} GNF</strong>
                        </small>
                        <small class="text-muted">
                            <i class="fas fa-list me-1"></i>
                            {{ $statsSorties['nombre'] }} sortie(s)
                        </small>
                        @if(request()->hasAny(['date_debut', 'date_fin', 'type_depense']))
                            <small>
                                <a href="{{ route('comptabilite.sorties', request()->only('annee_scolaire_id')) }}" class="text-danger">
                                    <i class="fas fa-times"></i> Effacer les filtres
                                </a>
                            </small>
                        @endif
                    </div>

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
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sorties as $sortie)
                                        @php
                                            $sortieHref = match(true) {
                                                isset($sortie->type) && $sortie->type == 'salaire' && isset($sortie->data) => route('salaires.show', $sortie->data),
                                                isset($sortie->data) => route('depenses.show', $sortie->data),
                                                default => '#',
                                            };
                                        @endphp
                                        <tr class="table-row-clickable" data-href="{{ $sortieHref }}" role="button" tabindex="0">
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
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <small class="text-muted">
                                    Affichage de {{ $sorties->firstItem() }} à {{ $sorties->lastItem() }}
                                    sur {{ $sorties->total() }} sorties
                                </small>
                            </div>
                            <div>
                                <nav aria-label="Pagination">
                                    <ul class="pagination pagination-simple mb-0">
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
                            <a href="{{ route('depenses.create') }}" class="btn btn-primary">
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
