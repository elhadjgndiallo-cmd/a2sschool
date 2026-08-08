@extends('layouts.app')

@section('title', 'Rapports de Salaires')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Rapports de salaires — {{ $periodeLibelle }}
                    </h3>
                    <a href="{{ route('salaires.index') }}" class="btn btn-secondary btn-sm">Retour aux bulletins</a>
                </div>
                <div class="card-body">
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('salaires.rapports') }}">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label">Type de rapport</label>
                                        <select name="mode" id="mode_rapport" class="form-select">
                                            <option value="mois" {{ $mode === 'mois' ? 'selected' : '' }}>Par mois</option>
                                            <option value="annee" {{ $mode === 'annee' ? 'selected' : '' }}>Par année scolaire</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3" id="filtre_mois" style="{{ $mode === 'annee' ? 'display:none' : '' }}">
                                        <label class="form-label">Mois</label>
                                        <input type="month" name="mois" class="form-control" value="{{ $mois }}">
                                    </div>
                                    <div class="col-md-3" id="filtre_annee" style="{{ $mode === 'mois' ? 'display:none' : '' }}">
                                        <label class="form-label">Année scolaire</label>
                                        <select name="annee_scolaire_id" class="form-select">
                                            @foreach($anneesScolaires as $a)
                                                <option value="{{ $a->id }}" {{ (int)$anneeScolaireId === (int)$a->id ? 'selected' : '' }}>{{ $a->nom }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search mr-1"></i> Afficher</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-2"><div class="card bg-primary text-white text-center p-3"><h4>{{ $stats['total_salaires'] }}</h4><small>Bulletins</small></div></div>
                        <div class="col-md-2"><div class="card bg-success text-white text-center p-3"><h4>{{ $stats['salaires_payes'] }}</h4><small>Payés</small></div></div>
                        <div class="col-md-2"><div class="card bg-info text-white text-center p-3"><h4>{{ $stats['salaires_valides'] }}</h4><small>Validés</small></div></div>
                        <div class="col-md-2"><div class="card bg-warning text-dark text-center p-3"><h4>{{ $stats['salaires_calcules'] }}</h4><small>Calculés</small></div></div>
                        <div class="col-md-2"><div class="card bg-danger text-white text-center p-3"><h4>{{ number_format($stats['montant_total_brut']/1000,0) }}K</h4><small>Brut GNF</small></div></div>
                        <div class="col-md-2"><div class="card bg-secondary text-white text-center p-3"><h4>{{ number_format($stats['montant_total_net']/1000,0) }}K</h4><small>Net GNF</small></div></div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card"><div class="card-body">
                                <h6>Avances sur la période</h6>
                                <p class="mb-0"><strong>{{ $statsAvances['total_bons'] }}</strong> bon(s) — <strong>{{ number_format($statsAvances['montant_bons'], 0, ',', ' ') }} GNF</strong></p>
                            </div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="card"><div class="card-body">
                                <h6>Avances déduites sur bulletins</h6>
                                <p class="mb-0"><strong>{{ number_format($stats['montant_total_avances'] ?? 0, 0, ',', ' ') }} GNF</strong></p>
                            </div></div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header"><strong>Synthèse par enseignant</strong></div>
                        <div class="card-body p-0">
                            @if($salairesParEnseignant->count())
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Enseignant</th>
                                        <th class="text-center">Bulletins</th>
                                        <th class="text-end">Brut</th>
                                        <th class="text-end">Avances déduites</th>
                                        <th class="text-end">Net payé</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salairesParEnseignant as $row)
                                    <tr>
                                        <td>{{ $row->enseignant->utilisateur->nom }} {{ $row->enseignant->utilisateur->prenom }}</td>
                                        <td class="text-center">{{ $row->count }}</td>
                                        <td class="text-end">{{ number_format($row->total_brut, 0, ',', ' ') }}</td>
                                        <td class="text-end text-danger">{{ number_format($row->total_avances, 0, ',', ' ') }}</td>
                                        <td class="text-end text-success"><strong>{{ number_format($row->total_net, 0, ',', ' ') }}</strong></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <p class="text-muted p-3 mb-0">Aucun bulletin sur cette période.</p>
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><strong>Détail des bulletins</strong></div>
                        <div class="card-body p-0">
                            @if($salairesListe->count())
                            <table class="table table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Enseignant</th>
                                        <th>Période</th>
                                        <th>Statut</th>
                                        <th class="text-end">Brut</th>
                                        <th class="text-end">Avances</th>
                                        <th class="text-end">Net</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salairesListe as $s)
                                    <tr>
                                        <td>{{ $s->enseignant->utilisateur->nom }} {{ $s->enseignant->utilisateur->prenom }}</td>
                                        <td>{{ $s->periode_debut->format('d/m/Y') }} — {{ $s->periode_fin->format('d/m/Y') }}</td>
                                        <td>{{ $s->statut_libelle }}</td>
                                        <td class="text-end">{{ number_format($s->salaire_brut, 0, ',', ' ') }}</td>
                                        <td class="text-end">{{ number_format($s->deduction_avances, 0, ',', ' ') }}</td>
                                        <td class="text-end"><strong>{{ number_format($s->salaire_net, 0, ',', ' ') }}</strong></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <p class="text-muted p-3 mb-0">Aucun bulletin.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('mode_rapport')?.addEventListener('change', function() {
    document.getElementById('filtre_mois').style.display = this.value === 'mois' ? '' : 'none';
    document.getElementById('filtre_annee').style.display = this.value === 'annee' ? '' : 'none';
});
</script>
@endpush
