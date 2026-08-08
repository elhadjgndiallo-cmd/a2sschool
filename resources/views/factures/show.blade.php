@extends('layouts.app')

@section('title', 'Facture ' . $facture->numero_facture)

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2><i class="fas fa-file-invoice me-2"></i>Facture {{ $facture->numero_facture }}</h2>
            <p class="text-muted mb-0">{{ $facture->date_facture->format('d/m/Y') }} — {{ $facture->anneeScolaire->nom ?? '' }}</p>
        </div>
        <div class="col-md-4 text-end">
            @if(auth()->user()->hasPermission('paiements.edit') && $facture->statut === 'en_cours' && $facture->resteAPayer() > 0.01)
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#payerResteFacture">
                    <i class="fas fa-money-bill-wave me-1"></i> Payer le reste ({{ number_format($facture->resteAPayer(), 0, ',', ' ') }} GNF)
                </button>
            @endif
            @if(auth()->user()->hasPermission('paiements.edit') && $facture->estModifiable())
                <a href="{{ route('factures.edit', $facture) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i> Modifier
                </a>
            @endif
            <a href="{{ route('factures.pdf', $facture) }}" class="btn btn-success" target="_blank"><i class="fas fa-receipt me-1"></i> Reçu / PDF</a>
            <a href="{{ route('factures.index') }}" class="btn btn-secondary">Retour</a>
            @if(auth()->user()->hasPermission('paiements.delete') && $facture->estModifiable())
                <form method="POST" action="{{ route('factures.destroy', $facture) }}" class="d-inline"
                      onsubmit="return confirm('Supprimer cette facture ? Les paiements et l\'entrée comptable seront également retirés.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Supprimer
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header"><strong>Élève</strong></div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $facture->eleve->utilisateur->prenom }} {{ $facture->eleve->utilisateur->nom }}</strong></p>
                    <p class="mb-1">Matricule : {{ $facture->eleve->numero_etudiant }}</p>
                    <p class="mb-0">Classe : {{ $facture->eleve->classe->nom ?? '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header"><strong>Paiement</strong></div>
                <div class="card-body">
                    <p class="mb-1">Mode : {{ ucfirst(str_replace('_', ' ', $facture->mode_paiement)) }}</p>
                    <p class="mb-1">Statut : <span class="badge bg-{{ $facture->statutBadgeClass() }}">{{ $facture->statutLibelle() }}</span></p>
                    <p class="mb-1">Émis par : {{ $facture->generePar->prenom ?? '' }} {{ $facture->generePar->nom ?? '' }}</p>
                    @if($facture->observations)
                        <p class="mb-0"><em>{{ $facture->observations }}</em></p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Lignes de facturation</strong></div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Libellé</th>
                        <th>Type</th>
                        <th class="text-end">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($facture->lignes as $ligne)
                        <tr>
                            <td>{{ $ligne->libelleAffiche() }}</td>
                            <td>{{ ucfirst($ligne->type_frais) }}</td>
                            <td class="text-end">{{ number_format($ligne->montantAffiche(), 0, ',', ' ') }} GNF</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    @php
                        $totalApresRemise = round((float) $facture->sous_total - (float) $facture->montant_remise, 2);
                        $resteAPayer = max(0, round($totalApresRemise - (float) $facture->total, 2));
                    @endphp
                    <tr>
                        <td colspan="2" class="text-end border-top pt-2"><strong>Total brut</strong></td>
                        <td class="text-end border-top pt-2"><strong>{{ number_format($facture->sous_total, 0, ',', ' ') }} GNF</strong></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-end text-danger"><strong>Remise</strong></td>
                        <td class="text-end text-danger"><strong>−{{ number_format($facture->montant_remise, 0, ',', ' ') }} GNF</strong></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-end"><strong>Total</strong></td>
                        <td class="text-end"><strong>{{ number_format($totalApresRemise, 0, ',', ' ') }} GNF</strong></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-end text-success"><strong>Total payé</strong></td>
                        <td class="text-end text-success"><strong>{{ number_format($facture->total, 0, ',', ' ') }} GNF</strong></td>
                    </tr>
                    @if($resteAPayer > 0)
                    <tr>
                        <td colspan="2" class="text-end text-warning"><strong>Reste à payer</strong></td>
                        <td class="text-end text-warning"><strong>{{ number_format($resteAPayer, 0, ',', ' ') }} GNF</strong></td>
                    </tr>
                    @endif
                </tfoot>
            </table>
            <p class="text-muted small px-3 pb-3 mb-0"><em>NB : le total est le montant qui reste après la remise.</em></p>
        </div>
    </div>

    @if(auth()->user()->hasPermission('paiements.edit') && $facture->statut === 'en_cours' && $facture->resteAPayer() > 0.01)
        @push('modals')
            @include('factures.partials.modal-payer-reste', ['facture' => $facture, 'modalId' => 'payerResteFacture'])
        @endpush
    @endif
</div>
@endsection
