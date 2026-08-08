@extends('layouts.app')

@section('title', 'Modifier facture ' . $facture->numero_facture)

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-edit me-2"></i>Modifier facture {{ $facture->numero_facture }}
            </h3>
            <span class="badge bg-info">Mise à jour comptabilité automatique</span>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('factures.update', $facture) }}" method="POST" id="facture-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="eleve_id" id="eleve_id" value="{{ $eleve->id }}">
                <input type="hidden" name="mode" value="mois">

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-1"><strong>Élève :</strong> {{ $eleve->utilisateur->prenom }} {{ $eleve->utilisateur->nom }}</p>
                                <p class="mb-1"><strong>Matricule :</strong> {{ $eleve->numero_etudiant }}</p>
                                <p class="mb-0"><strong>Classe :</strong> {{ $eleve->classe->nom ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-muted small mb-3">
                    Modifiez les mois, la remise ou le montant versé. Les paiements, tranches et l'entrée comptable seront recalculés.
                </p>

                <div class="table-responsive mb-2">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px"><input type="checkbox" id="select_all_lignes" title="Tout sélectionner"></th>
                                <th>Type</th>
                                <th>Libellé</th>
                                <th class="text-end">Montant</th>
                                <th>Source</th>
                            </tr>
                        </thead>
                        <tbody id="lignes_body">
                            <tr><td colspan="5" class="text-center">Chargement...</td></tr>
                        </tbody>
                        <tfoot class="table-light" id="totaux_footer">
                            <tr>
                                <td colspan="3" class="text-end border-top pt-2"><strong>Total brut</strong></td>
                                <td class="text-end border-top pt-2"><strong id="foot_brut">0 GNF</strong></td>
                                <td class="border-top"></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end text-danger"><strong>Remise</strong></td>
                                <td class="text-end text-danger"><strong id="foot_remise">0 GNF</strong></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total</strong></td>
                                <td class="text-end"><strong id="foot_total">0 GNF</strong></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end text-success"><strong>Total payé</strong></td>
                                <td class="text-end text-success"><strong id="foot_paye">0 GNF</strong></td>
                                <td></td>
                            </tr>
                            <tr id="foot_reste_row">
                                <td colspan="3" class="text-end text-warning"><strong>Reste à payer</strong></td>
                                <td class="text-end text-warning"><strong id="foot_reste">0 GNF</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <p class="text-muted small mb-3"><em>NB : le total est le montant qui reste après la remise.</em></p>

                <div class="row mb-3 align-items-start g-3">
                    <div class="col-md-6 col-lg-5">
                        <div class="mb-3">
                            <label class="form-label">Date facture / paiement</label>
                            <input type="date" name="date_facture" class="form-control" required
                                   value="{{ old('date_facture', $facture->date_facture->format('Y-m-d')) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mode de paiement</label>
                            <select name="mode_paiement" class="form-select" required>
                                @foreach(['especes' => 'Espèces', 'cheque' => 'Chèque', 'virement' => 'Virement', 'carte' => 'Carte', 'mobile_money' => 'Mobile Money'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('mode_paiement', $facture->mode_paiement) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Référence (optionnel)</label>
                            <input type="text" name="reference_paiement" class="form-control" placeholder="Réf. externe"
                                   value="{{ old('reference_paiement', $facture->reference_paiement) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date échéance (optionnel)</label>
                            <input type="date" name="date_echeance" class="form-control"
                                   value="{{ old('date_echeance', $facture->date_echeance?->format('Y-m-d')) }}">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Observations</label>
                            <textarea name="observations" class="form-control" rows="3">{{ old('observations', $facture->observations) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-5 ms-lg-auto">
                        <div class="mb-3">
                            <label class="form-label">Type de remise</label>
                            <select name="remise_type" id="remise_type" class="form-select">
                                <option value="montant" {{ old('remise_type', $facture->remise_type) === 'montant' ? 'selected' : '' }}>Montant fixe (GNF)</option>
                                <option value="pourcentage" {{ old('remise_type', $facture->remise_type) === 'pourcentage' ? 'selected' : '' }}>Pourcentage (%)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Valeur remise</label>
                            <input type="number" name="remise_valeur" id="remise_valeur" class="form-control" min="0" step="0.01"
                                   value="{{ old('remise_valeur', $facture->remise_valeur) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Montant versé (GNF)</label>
                            <input type="number" name="montant_verse" id="montant_verse" class="form-control" min="1" step="1"
                                   value="{{ old('montant_verse', $facture->total) }}">
                        </div>
                        <div id="repartition_info" class="alert alert-info py-2 small mb-0" style="display:none;"></div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary" id="submit_btn" disabled>
                        <i class="fas fa-save me-1"></i> Enregistrer les modifications
                    </button>
                    <a href="{{ route('factures.show', $facture) }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const factureId = {{ $facture->id }};
    const selectionInitiale = @json($lignesSelectionIds);
    const lignesBody = document.getElementById('lignes_body');
    const submitBtn = document.getElementById('submit_btn');
    const remiseType = document.getElementById('remise_type');
    const remiseValeur = document.getElementById('remise_valeur');
    const montantVerse = document.getElementById('montant_verse');
    const repartitionInfo = document.getElementById('repartition_info');
    let lignesCache = [];
    let montantVerseManuel = true;
    let recapTimer = null;

    const typeLabels = {
        scolarite: 'Scolarité', cantine: 'Cantine', transport: 'Transport',
        inscription: 'Inscription', reinscription: 'Réinscription',
        uniforme: 'Uniforme', livres: 'Livres', autre: 'Autre', autres: 'Autres'
    };

    function libelleAffiche(l) {
        if (['inscription', 'reinscription', 'uniforme', 'livres', 'autre', 'autres'].includes(l.type_frais)) {
            return l.libelle;
        }
        return l.libelle.split('—').pop()?.trim() || l.mois;
    }

    function sourceBadge(l) {
        if (l.facture_actuelle) return '<span class="badge bg-success">Facture actuelle</span>';
        if (l.source === 'frais') return '<span class="badge bg-warning text-dark">Frais unique</span>';
        if (l.source === 'tranche') return '<span class="badge bg-primary">Tranche</span>';
        return '<span class="badge bg-secondary">Tarif</span>';
    }

    function formatGnf(n) {
        return Math.round(n).toLocaleString('fr-FR') + ' GNF';
    }

    function loadLignes() {
        lignesBody.innerHTML = '<tr><td colspan="5" class="text-center">Chargement...</td></tr>';
        fetch(`{{ url('/factures') }}/${factureId}/lignes-edition`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                lignesBody.innerHTML = `<tr><td colspan="5" class="text-danger text-center">${data.error}</td></tr>`;
                submitBtn.disabled = true;
                return;
            }
            lignesCache = data.lignes || [];
            const selected = data.selection || selectionInitiale;
            if (!lignesCache.length) {
                lignesBody.innerHTML = '<tr><td colspan="5" class="text-muted text-center">Aucun frais disponible</td></tr>';
                submitBtn.disabled = true;
                return;
            }
            lignesBody.innerHTML = lignesCache.map(l => `
                <tr data-ligne-id="${l.id}">
                    <td><input type="checkbox" name="lignes[]" value="${l.id}" class="ligne-check" data-montant="${l.montant}"
                        ${selected.includes(l.id) ? 'checked' : ''}></td>
                    <td>${typeLabels[l.type_frais] || l.type_frais}</td>
                    <td>${libelleAffiche(l)}</td>
                    <td class="text-end col-brut">${formatGnf(l.montant)}</td>
                    <td>${sourceBadge(l)}</td>
                </tr>
            `).join('');
            document.querySelectorAll('.ligne-check').forEach(cb => cb.addEventListener('change', () => {
                montantVerseManuel = false;
                updateRecap();
            }));
            updateRecap();
        });
    }

    function getSelectedLignes() {
        return [...document.querySelectorAll('.ligne-check:checked')].map(cb => cb.value);
    }

    function resetRecap() {
        document.getElementById('foot_brut').textContent = '0 GNF';
        document.getElementById('foot_remise').textContent = '0 GNF';
        document.getElementById('foot_total').textContent = '0 GNF';
        document.getElementById('foot_paye').textContent = '0 GNF';
        document.getElementById('foot_reste').textContent = '0 GNF';
        document.getElementById('foot_reste_row').style.display = '';
        repartitionInfo.style.display = 'none';
        repartitionInfo.innerHTML = '';
    }

    function applyRecap(t) {
        document.getElementById('foot_brut').textContent = formatGnf(t.sous_total || 0);
        document.getElementById('foot_remise').textContent = (t.montant_remise || 0) > 0
            ? '−' + formatGnf(t.montant_remise) : formatGnf(0);
        document.getElementById('foot_total').textContent = formatGnf(t.total_du ?? t.total ?? 0);
        document.getElementById('foot_paye').textContent = formatGnf(t.total || t.montant_verse || 0);

        const reste = t.reste_a_payer || 0;
        document.getElementById('foot_reste').textContent = formatGnf(reste);
        document.getElementById('foot_reste_row').style.display = reste > 0 ? '' : 'none';

        if (!montantVerseManuel && (t.total_du ?? t.total)) {
            montantVerse.value = Math.round(t.total_du ?? t.total ?? 0);
        }

        if (t.lignes && t.lignes.length && reste > 0) {
            repartitionInfo.style.display = 'block';
            repartitionInfo.innerHTML = '<strong>Répartition du paiement :</strong><ul class="mb-0 ps-3 mt-1">' + t.lignes.filter(l => l.montant > 0 || l.reste > 0).map(l => {
                const parts = [];
                if (l.montant > 0) parts.push(`<strong>${formatGnf(l.montant)}</strong>`);
                if (l.reste > 0) parts.push(`<span class="text-warning">(reste ${formatGnf(l.reste)})</span>`);
                return `<li>${l.libelle}${parts.length ? ' : ' + parts.join(' ') : ''}</li>`;
            }).join('') + '</ul>';
        } else {
            repartitionInfo.style.display = 'none';
            repartitionInfo.innerHTML = '';
        }
    }

    function updateRecap() {
        clearTimeout(recapTimer);
        recapTimer = setTimeout(() => {
            const selected = getSelectedLignes();
            submitBtn.disabled = selected.length === 0;

            if (!selected.length) {
                resetRecap();
                return;
            }

            const eleveId = document.getElementById('eleve_id').value;
            const montantSaisi = parseFloat(montantVerse.value) || 0;

            fetch('{{ route('factures.preview-totaux') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    eleve_id: eleveId,
                    facture_id: factureId,
                    lignes: selected,
                    remise_type: remiseType.value,
                    remise_valeur: parseFloat(remiseValeur.value) || 0,
                    montant_verse: montantVerseManuel && montantSaisi > 0 ? montantSaisi : null
                })
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                if (!ok || data.error) {
                    resetRecap();
                    submitBtn.disabled = true;
                    if (data.error) {
                        repartitionInfo.style.display = 'block';
                        repartitionInfo.className = 'alert alert-warning py-2 small mb-3';
                        repartitionInfo.textContent = data.error;
                    }
                    return;
                }

                repartitionInfo.className = 'alert alert-info py-2 small mb-3';
                applyRecap(data);
                submitBtn.disabled = false;
            });
        }, 200);
    }

    remiseType.addEventListener('change', () => { montantVerseManuel = false; updateRecap(); });
    remiseValeur.addEventListener('input', () => { montantVerseManuel = false; updateRecap(); });
    montantVerse.addEventListener('input', () => { montantVerseManuel = true; updateRecap(); });
    document.getElementById('select_all_lignes')?.addEventListener('change', function() {
        document.querySelectorAll('.ligne-check').forEach(cb => { cb.checked = this.checked; });
        montantVerseManuel = false;
        updateRecap();
    });

    loadLignes();
});
</script>
@endsection
