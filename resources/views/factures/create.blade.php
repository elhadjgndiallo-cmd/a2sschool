@extends('layouts.app')

@section('title', 'Nouvelle facture')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0"><i class="fas fa-file-invoice me-2"></i>Nouvelle facture</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('factures.store') }}" method="POST" id="facture-form">
                @csrf
                <input type="hidden" name="eleve_id" id="eleve_id" value="{{ $eleve?->id }}">

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="eleve_search" class="form-label">Rechercher un élève</label>
                        <div class="input-group">
                            <input type="text" id="eleve_search" class="form-control"
                                   placeholder="Nom, prénom ou matricule..."
                                   value="{{ $eleve ? $eleve->utilisateur->prenom . ' ' . $eleve->utilisateur->nom : '' }}">
                            <button type="button" class="btn btn-outline-secondary" id="search_eleve_btn"><i class="fas fa-search"></i></button>
                        </div>
                        <div id="eleves_results" class="mt-2" style="display:none;"></div>
                    </div>
                </div>

                <div id="eleve_info" class="row mb-3" style="display: {{ $eleve ? 'block' : 'none' }};">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body" id="eleve_details">
                                @if($eleve)
                                    <p class="mb-1"><strong>Nom :</strong> {{ $eleve->utilisateur->prenom }} {{ $eleve->utilisateur->nom }}</p>
                                    <p class="mb-1"><strong>Matricule :</strong> {{ $eleve->numero_etudiant }}</p>
                                    <p class="mb-0"><strong>Classe :</strong> {{ $eleve->classe->nom ?? 'N/A' }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div id="lignes_section" style="display: {{ $eleve ? 'block' : 'none' }};">
                    <input type="hidden" name="mode" id="mode_facturation" value="mois">

                    <ul class="nav nav-tabs mb-3" id="mode_tabs">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" data-mode="mois">
                                <i class="fas fa-calendar-check me-1"></i> Sélection par mois
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-mode="montant">
                                <i class="fas fa-coins me-1"></i> Montant versé
                            </button>
                        </li>
                    </ul>

                    <div id="panel_mois">
                    <p class="text-muted small mb-3">
                        Cochez les mois à facturer manuellement. Les mois déjà payés n'apparaissent pas.
                    </p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px"><input type="checkbox" id="select_all_lignes" title="Tout sélectionner"></th>
                                    <th>Type</th>
                                    <th>Mois</th>
                                    <th class="text-end">Montant</th>
                                    <th>Source</th>
                                </tr>
                            </thead>
                            <tbody id="lignes_body">
                                <tr><td colspan="5" class="text-muted text-center">Sélectionnez un élève</td></tr>
                            </tbody>
                        </table>
                    </div>
                    </div>

                    <div id="panel_montant" style="display:none;">
                        <p class="text-muted small mb-3">
                            Saisissez le montant reçu : le système le répartit automatiquement sur les mois dus
                            (mois en cours et mois passés impayés uniquement).
                        </p>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Type de frais</label>
                                <select name="type_frais_cible" id="type_frais_cible" class="form-select">
                                    <option value="scolarite">Scolarité</option>
                                    <option value="cantine">Cantine</option>
                                    <option value="transport">Transport</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Montant versé (GNF)</label>
                                <input type="number" name="montant_verse" id="montant_verse" class="form-control" min="1" step="1" placeholder="Ex. 300000">
                            </div>
                        </div>
                        <div id="repartition_preview" class="card bg-light mb-3" style="display:none;">
                            <div class="card-header py-2"><strong>Répartition automatique</strong></div>
                            <div class="card-body py-2" id="repartition_body"></div>
                        </div>
                        <div id="repartition_error" class="alert alert-warning py-2" style="display:none;"></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Type de remise</label>
                            <select name="remise_type" id="remise_type" class="form-select">
                                <option value="montant">Montant fixe (GNF)</option>
                                <option value="pourcentage">Pourcentage (%)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Valeur remise</label>
                            <input type="number" name="remise_valeur" id="remise_valeur" class="form-control" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light mt-4">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between"><span>Sous-total</span><strong id="recap_sous_total">0 GNF</strong></div>
                                    <div class="d-flex justify-content-between text-danger"><span>Remise</span><strong id="recap_remise">0 GNF</strong></div>
                                    <div class="d-flex justify-content-between fs-5 border-top pt-2 mt-2"><span>Total à payer</span><strong id="recap_total" class="text-success">0 GNF</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Date facture / paiement</label>
                            <input type="date" name="date_facture" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mode de paiement</label>
                            <select name="mode_paiement" class="form-select" required>
                                <option value="especes">Espèces</option>
                                <option value="cheque">Chèque</option>
                                <option value="virement">Virement</option>
                                <option value="carte">Carte</option>
                                <option value="mobile_money">Mobile Money</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Référence (optionnel)</label>
                            <input type="text" name="reference_paiement" class="form-control" placeholder="Réf. externe">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date échéance (optionnel)</label>
                            <input type="date" name="date_echeance" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observations</label>
                        <textarea name="observations" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success" id="submit_btn" disabled>
                        <i class="fas fa-check me-1"></i> Émettre et encaisser
                    </button>
                    <a href="{{ route('factures.index') }}" class="btn btn-secondary">Retour</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const eleveSearch = document.getElementById('eleve_search');
    const elevesResults = document.getElementById('eleves_results');
    const eleveInfo = document.getElementById('eleve_info');
    const eleveDetails = document.getElementById('eleve_details');
    const lignesSection = document.getElementById('lignes_section');
    const lignesBody = document.getElementById('lignes_body');
    const submitBtn = document.getElementById('submit_btn');
    const remiseType = document.getElementById('remise_type');
    const remiseValeur = document.getElementById('remise_valeur');
    const modeInput = document.getElementById('mode_facturation');
    const panelMontant = document.getElementById('panel_montant');
    const panelMois = document.getElementById('panel_mois');
    const montantVerse = document.getElementById('montant_verse');
    const typeFraisCible = document.getElementById('type_frais_cible');
    const repartitionPreview = document.getElementById('repartition_preview');
    const repartitionBody = document.getElementById('repartition_body');
    const repartitionError = document.getElementById('repartition_error');
    let lignesCache = [];
    let currentMode = 'mois';

    const typeLabels = { scolarite: 'Scolarité', cantine: 'Cantine', transport: 'Transport' };

    function formatGnf(n) {
        return Math.round(n).toLocaleString('fr-FR') + ' GNF';
    }

    function searchEleves() {
        const search = eleveSearch.value.trim();
        if (search.length < 2) { elevesResults.style.display = 'none'; return; }

        fetch(`{{ url('/factures/search-eleves') }}?search=${encodeURIComponent(search)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                elevesResults.innerHTML = '<div class="alert alert-info mb-0">Aucun élève trouvé</div>';
            } else {
                elevesResults.innerHTML = '<div class="list-group">' + data.map(e =>
                    `<a href="#" class="list-group-item list-group-item-action" data-id="${e.id}" data-nom="${e.utilisateur.nom}" data-prenom="${e.utilisateur.prenom}" data-num="${e.numero_etudiant || ''}" data-classe="${e.classe?.nom || 'N/A'}">
                        <strong>${e.utilisateur.prenom} ${e.utilisateur.nom}</strong> — ${e.numero_etudiant || ''} (${e.classe?.nom || 'N/A'})
                    </a>`
                ).join('') + '</div>';
                elevesResults.querySelectorAll('a').forEach(a => a.addEventListener('click', function(ev) {
                    ev.preventDefault();
                    selectEleve(this.dataset.id, this.dataset.prenom, this.dataset.nom, this.dataset.num, this.dataset.classe);
                }));
            }
            elevesResults.style.display = 'block';
        });
    }

    function selectEleve(id, prenom, nom, num, classe) {
        document.getElementById('eleve_id').value = id;
        eleveSearch.value = `${prenom} ${nom}`;
        elevesResults.style.display = 'none';
        eleveDetails.innerHTML = `<p class="mb-1"><strong>Nom :</strong> ${prenom} ${nom}</p><p class="mb-1"><strong>Matricule :</strong> ${num}</p><p class="mb-0"><strong>Classe :</strong> ${classe}</p>`;
        eleveInfo.style.display = 'block';
        lignesSection.style.display = 'block';
        loadLignes(id);
    }

    function loadLignes(eleveId) {
            lignesBody.innerHTML = '<tr><td colspan="5" class="text-center">Chargement...</td></tr>';
        fetch(`{{ url('/factures/eleve') }}/${eleveId}/lignes`, {
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
            if (!lignesCache.length) {
                lignesBody.innerHTML = '<tr><td colspan="5" class="text-muted text-center">Aucun frais mensuel disponible</td></tr>';
                submitBtn.disabled = true;
                return;
            }
            lignesBody.innerHTML = lignesCache.map(l => `
                <tr>
                    <td><input type="checkbox" name="lignes[]" value="${l.id}" class="ligne-check" data-montant="${l.montant}"></td>
                    <td>${typeLabels[l.type_frais] || l.type_frais}</td>
                    <td>${l.libelle.split('—').pop()?.trim() || l.mois}</td>
                    <td class="text-end">${formatGnf(l.montant)}</td>
                    <td><span class="badge bg-${l.source === 'tranche' ? 'primary' : 'secondary'}">${l.source === 'tranche' ? 'Tranche' : 'Tarif'}</span></td>
                </tr>
            `).join('');
            document.querySelectorAll('.ligne-check').forEach(cb => cb.addEventListener('change', updateRecap));
            updateRecap();
        });
    }

    function getSelectedLignes() {
        return [...document.querySelectorAll('.ligne-check:checked')].map(cb => cb.value);
    }

    function setMode(mode) {
        currentMode = mode;
        modeInput.value = mode;
        document.querySelectorAll('#mode_tabs .nav-link').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.mode === mode);
        });
        panelMontant.style.display = mode === 'montant' ? 'block' : 'none';
        panelMois.style.display = mode === 'mois' ? 'block' : 'none';
        if (mode === 'montant') {
            montantVerse.required = true;
            document.querySelectorAll('.ligne-check').forEach(cb => { cb.checked = false; cb.disabled = true; });
        } else {
            montantVerse.required = false;
            document.querySelectorAll('.ligne-check').forEach(cb => { cb.disabled = false; });
            repartitionPreview.style.display = 'none';
            repartitionError.style.display = 'none';
        }
        updateRecap();
    }

    function updateRepartition() {
        const eleveId = document.getElementById('eleve_id').value;
        const montant = parseFloat(montantVerse.value) || 0;
        if (!eleveId || montant <= 0) {
            repartitionPreview.style.display = 'none';
            repartitionError.style.display = 'none';
            submitBtn.disabled = true;
            return;
        }

        fetch('{{ route('factures.preview-repartition') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                eleve_id: eleveId,
                montant_verse: montant,
                type_frais_cible: typeFraisCible.value,
                remise_type: remiseType.value,
                remise_valeur: parseFloat(remiseValeur.value) || 0
            })
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            if (!ok || data.error) {
                repartitionPreview.style.display = 'none';
                repartitionError.style.display = 'block';
                repartitionError.textContent = data.error || 'Impossible de répartir ce montant.';
                submitBtn.disabled = true;
                document.getElementById('recap_sous_total').textContent = '0 GNF';
                document.getElementById('recap_remise').textContent = '0 GNF';
                document.getElementById('recap_total').textContent = '0 GNF';
                return;
            }
            repartitionError.style.display = 'none';
            repartitionPreview.style.display = 'block';
            repartitionBody.innerHTML = '<ul class="mb-0 ps-3">' + (data.lignes || []).map(l =>
                `<li>${l.libelle} : <strong>${formatGnf(l.montant)}</strong></li>`
            ).join('') + '</ul>';
            document.getElementById('recap_sous_total').textContent = formatGnf(data.sous_total || 0);
            document.getElementById('recap_remise').textContent = formatGnf(data.montant_remise || 0);
            document.getElementById('recap_total').textContent = formatGnf(data.total || 0);
            submitBtn.disabled = false;
        });
    }

    function updateRecap() {
        if (currentMode === 'montant') {
            updateRepartition();
            return;
        }

        const selected = getSelectedLignes();
        submitBtn.disabled = selected.length === 0;
        if (!selected.length) {
            document.getElementById('recap_sous_total').textContent = '0 GNF';
            document.getElementById('recap_remise').textContent = '0 GNF';
            document.getElementById('recap_total').textContent = '0 GNF';
            return;
        }

        const eleveId = document.getElementById('eleve_id').value;
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
                lignes: selected,
                remise_type: remiseType.value,
                remise_valeur: parseFloat(remiseValeur.value) || 0
            })
        })
        .then(r => r.json())
        .then(t => {
            document.getElementById('recap_sous_total').textContent = formatGnf(t.sous_total || 0);
            document.getElementById('recap_remise').textContent = formatGnf(t.montant_remise || 0);
            document.getElementById('recap_total').textContent = formatGnf(t.total || 0);
        });
    }

    document.querySelectorAll('#mode_tabs .nav-link').forEach(btn => {
        btn.addEventListener('click', () => setMode(btn.dataset.mode));
    });

    document.getElementById('search_eleve_btn').addEventListener('click', searchEleves);
    eleveSearch.addEventListener('keyup', e => { if (e.key === 'Enter') { e.preventDefault(); searchEleves(); } });
    remiseType.addEventListener('change', updateRecap);
    remiseValeur.addEventListener('input', updateRecap);
    montantVerse.addEventListener('input', updateRecap);
    typeFraisCible.addEventListener('change', updateRecap);
    document.getElementById('select_all_lignes')?.addEventListener('change', function() {
        document.querySelectorAll('.ligne-check').forEach(cb => { cb.checked = this.checked; });
        updateRecap();
    });

    setMode('mois');

    @if($eleve)
        loadLignes({{ $eleve->id }});
    @endif
});
</script>
@endsection
