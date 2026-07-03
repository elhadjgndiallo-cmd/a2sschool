@extends('layouts.app')

@section('title', 'Facturation scolaire')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2><i class="fas fa-file-invoice me-2"></i>Facturation scolaire</h2>
            @if($anneeScolaire ?? null)
                <p class="text-muted mb-0">Année scolaire : <strong>{{ $anneeScolaire->nom }}</strong></p>
            @endif
        </div>
        <div class="col-md-4 text-end">
            @if(auth()->user()->hasPermission('paiements.create'))
                <a href="{{ route('factures.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Nouvelle facture
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-2">
                    <select name="annee_scolaire_id" class="form-select" onchange="this.form.submit()">
                        @foreach($anneesScolaires ?? \App\Models\AnneeScolaire::orderByDesc('date_debut')->get() as $annee)
                            <option value="{{ $annee->id }}" {{ request('annee_scolaire_id', $anneeScolaire->id ?? null) == $annee->id ? 'selected' : '' }}>
                                {{ $annee->nom }}{{ $annee->active ? ' (active)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="N° facture, élève..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="classe_id" class="form-select">
                        <option value="">Toutes les classes</option>
                        @foreach($classes as $classe)
                            <option value="{{ $classe->id }}" {{ request('classe_id') == $classe->id ? 'selected' : '' }}>{{ $classe->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary"><i class="fas fa-filter"></i> Filtrer</button>
                    <a href="{{ route('factures.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>N° Facture</th>
                            <th>Date</th>
                            <th>Élève</th>
                            <th>Classe</th>
                            <th class="text-end">Total</th>
                            <th>Mode</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($factures as $facture)
                            <tr>
                                <td><strong>{{ $facture->numero_facture }}</strong></td>
                                <td>{{ $facture->date_facture->format('d/m/Y') }}</td>
                                <td>{{ $facture->eleve->utilisateur->prenom }} {{ $facture->eleve->utilisateur->nom }}</td>
                                <td>{{ $facture->eleve->classe->nom ?? '—' }}</td>
                                <td class="text-end">{{ number_format($facture->total, 0, ',', ' ') }} GNF</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $facture->mode_paiement)) }}</td>
                                <td>
                                    <span class="badge bg-{{ $facture->statut === 'payee' ? 'success' : 'secondary' }}">
                                        {{ $facture->statut === 'payee' ? 'Payée' : 'Annulée' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('factures.show', $facture) }}" class="btn btn-outline-primary" title="Voir"><i class="fas fa-eye"></i></a>
                                        @if(auth()->user()->hasPermission('paiements.edit') && $facture->statut === 'payee')
                                            <a href="{{ route('factures.edit', $facture) }}" class="btn btn-outline-warning" title="Modifier"><i class="fas fa-edit"></i></a>
                                        @endif
                                        <a href="{{ route('factures.pdf', $facture) }}" class="btn btn-outline-success" title="PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                        @if(auth()->user()->hasPermission('paiements.delete') && $facture->statut === 'payee')
                                            <form method="POST" action="{{ route('factures.destroy', $facture) }}" class="d-inline"
                                                  onsubmit="return confirm('Supprimer cette facture ? Les paiements et l\'entrée comptable seront retirés.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Aucune facture enregistrée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($factures->hasPages())
            <div class="card-footer">{{ $factures->links() }}</div>
        @endif
    </div>
</div>
@endsection
