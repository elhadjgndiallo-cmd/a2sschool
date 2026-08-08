@php
    $modalId = $modalId ?? 'modalPayerReste';
    $factureModal = $facture ?? null;
    $resteModal = $factureModal ? $factureModal->resteAPayer() : 0;
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true"
     data-bs-backdrop="false" data-bs-keyboard="true"
     @if($factureModal) data-facture-numero="{{ $factureModal->numero_facture }}" @endif>
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST"
                  action="{{ $factureModal ? route('factures.payer-reste', $factureModal) : '#' }}"
                  class="form-payer-reste">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $modalId }}Label">
                        <i class="fas fa-money-bill-wave me-2"></i>Payer le reste
                        @if($factureModal)
                            — {{ $factureModal->numero_facture }}
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Encaissement du solde restant sans modifier la facture.
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Montant à encaisser</label>
                        <div class="input-group">
                            <input type="text" class="form-control bg-light montant-reste-display" readonly
                                   value="{{ number_format($resteModal, 0, ',', ' ') }}">
                            <span class="input-group-text">GNF</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date du paiement</label>
                        <input type="date" name="date_paiement" class="form-control"
                               value="{{ old('date_paiement', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                        <select name="mode_paiement" class="form-select" required>
                            @foreach(['especes' => 'Espèces', 'cheque' => 'Chèque', 'virement' => 'Virement', 'carte' => 'Carte', 'mobile_money' => 'Mobile Money'] as $val => $label)
                                <option value="{{ $val }}" {{ old('mode_paiement', 'especes') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Référence (optionnel)</label>
                        <input type="text" name="reference_paiement" class="form-control" placeholder="N° chèque, réf. virement..."
                               value="{{ old('reference_paiement') }}">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Note (optionnel)</label>
                        <textarea name="observations" class="form-control" rows="2" placeholder="Commentaire sur ce paiement">{{ old('observations') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Encaisser le solde
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
