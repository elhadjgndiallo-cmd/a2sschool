@extends('layouts.app')

@section('title', 'Bons de salaire (avances)')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0"><i class="fas fa-hand-holding-usd mr-2"></i>Bons de salaire — Avances</h3>
                    <div>
                        <a href="{{ route('salaires.index') }}" class="btn btn-secondary btn-sm">Bulletins</a>
                        <a href="{{ route('salaires.bons.create') }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-plus mr-1"></i> Nouvelle avance
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-3">
                            <select name="enseignant_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Tous les enseignants</option>
                                @foreach($enseignants as $e)
                                    <option value="{{ $e->id }}" {{ request('enseignant_id') == $e->id ? 'selected' : '' }}>
                                        {{ $e->utilisateur->nom }} {{ $e->utilisateur->prenom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="statut" class="form-select" onchange="this.form.submit()">
                                <option value="">Tous statuts</option>
                                <option value="actif" {{ request('statut') === 'actif' ? 'selected' : '' }}>Actif</option>
                                <option value="deduit" {{ request('statut') === 'deduit' ? 'selected' : '' }}>Déduit</option>
                                <option value="annule" {{ request('statut') === 'annule' ? 'selected' : '' }}>Annulé</option>
                            </select>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>N° Bon</th>
                                    <th>Date</th>
                                    <th>Enseignant</th>
                                    <th class="text-end">Montant</th>
                                    <th>Statut</th>
                                    <th>Bulletin lié</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bons as $bon)
                                    <tr>
                                        <td><strong>{{ $bon->numero_bon }}</strong></td>
                                        <td>{{ $bon->date_bon->format('d/m/Y') }}</td>
                                        <td>{{ $bon->enseignant->utilisateur->nom }} {{ $bon->enseignant->utilisateur->prenom }}</td>
                                        <td class="text-end">{{ number_format($bon->montant, 0, ',', ' ') }} GNF</td>
                                        <td>
                                            @if($bon->statut === 'actif')<span class="badge bg-warning text-dark">Actif</span>
                                            @elseif($bon->statut === 'deduit')<span class="badge bg-success">Déduit</span>
                                            @else<span class="badge bg-secondary">{{ $bon->statut_libelle }}</span>@endif
                                        </td>
                                        <td>
                                            @if($bon->salaireEnseignant)
                                                <a href="{{ route('salaires.show', $bon->salaireEnseignant) }}">Bulletin</a>
                                            @else — @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('salaires.bons.show', $bon) }}" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fas fa-print"></i></a>
                                            @if($bon->statut === 'actif')
                                                <form action="{{ route('salaires.bons.destroy', $bon) }}" method="POST" class="d-inline" onsubmit="return confirm('Annuler ce bon ?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-4">Aucun bon de salaire.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $bons->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
