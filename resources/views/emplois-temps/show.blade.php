@extends('layouts.app')

@section('title', 'Emploi du temps - ' . $classe->nom)

@section('content')
@php
    $dureeSecondaire = (int) ($dureeDefautSecondaire ?? 120);
@endphp
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Emploi du temps - {{ $classe->nom }}
                        <span class="badge bg-secondary ms-1">Secondaire · {{ (int) ($dureeSecondaire / 60) }} h / cours</span>
                        @if(!empty($anneeScolaireActive))
                            <small class="text-muted">— {{ $anneeScolaireActive->nom }}</small>
                        @endif
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('emplois-temps.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour
                        </a>
                        <a href="{{ route('emplois-temps.export', $classe) }}?format=pdf" class="btn btn-danger" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i>
                            Télécharger PDF
                        </a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCreneauModal" onclick="resetCreneauForm()">
                            <i class="fas fa-plus me-1"></i>
                            Ajouter un créneau
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Grille <strong>dynamique</strong> : une nouvelle ligne apparaît à chaque créneau ajouté.
                        Chaque cours dure <strong>{{ (int) ($dureeSecondaire / 60) }} heures</strong> (heure de fin automatique).
                    </p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th style="min-width: 110px;">Heure</th>
                                    @foreach($jours as $jour)
                                        <th class="text-center">{{ ucfirst($jour) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($heures as $heure)
                                    @php
                                        $parts = explode(':', $heure);
                                        $startMin = ((int) $parts[0]) * 60 + ((int) ($parts[1] ?? 0));
                                        $endMin = $startMin + $dureeSecondaire;
                                        $heureFinAffiche = sprintf('%02d:%02d', intdiv($endMin, 60) % 24, $endMin % 60);
                                    @endphp
                                    <tr style="height: 100px;">
                                        <td class="fw-bold align-middle bg-light">
                                            {{ $heure }} – {{ $heureFinAffiche }}
                                            <div class="small text-muted fw-normal">{{ (int) ($dureeSecondaire / 60) }} h</div>
                                        </td>
                                        @foreach($jours as $jour)
                                            <td class="text-center position-relative align-middle p-2" style="min-height: 100px;">
                                                @php
                                                    $creneaux = $emploisTemps->where('jour_semaine', $jour)
                                                        ->filter(function ($emploi) use ($heure) {
                                                            return \Carbon\Carbon::parse($emploi->heure_debut)->format('H:i') === $heure;
                                                        });
                                                @endphp

                                                @if($creneaux->isNotEmpty())
                                                    @foreach($creneaux as $creneau)
                                                        <div class="bg-primary text-white p-2 rounded mb-1" style="font-size: 0.85rem;">
                                                            <div class="fw-bold mb-1">{{ $creneau->matiere->nom }}</div>
                                                            <div class="small mb-2">
                                                                {{ strtoupper($creneau->enseignant->utilisateur->nom ?? 'N/A') }}
                                                                {{ $creneau->enseignant->utilisateur->prenom ?? '' }}
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-outline-light"
                                                                    onclick="event.stopPropagation(); deleteCreneau({{ $creneau->id }})"
                                                                    style="padding: 2px 6px; font-size: 0.7rem;">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <button type="button"
                                                            class="btn btn-outline-secondary btn-sm w-100 d-flex align-items-center justify-content-center"
                                                            onclick="addCreneau('{{ $jour }}', '{{ $heure }}')"
                                                            style="min-height: 90px; border: 1px dashed #dee2e6;">
                                                        <span style="font-size: 1.5rem; color: #6c757d;">+</span>
                                                    </button>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($jours) + 1 }}" class="text-center text-muted py-5">
                                            <i class="fas fa-calendar-plus fa-2x mb-2 d-block"></i>
                                            Aucun créneau pour le moment.<br>
                                            Cliquez sur <strong>« Ajouter un créneau »</strong> : la grille s’agrandira automatiquement.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCreneauModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Créneau (2 h)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCreneauForm">
                <div class="modal-body">
                    <input type="hidden" name="classe_id" value="{{ $classe->id }}">

                    <div class="mb-3">
                        <label for="matiere_id" class="form-label">Matière <span class="text-danger">*</span></label>
                        <select name="matiere_id" id="matiere_id" class="form-control" required>
                            <option value="">Sélectionner une matière</option>
                            @foreach(\App\Models\Matiere::actif()->orderBy('nom')->get() as $matiere)
                                <option value="{{ $matiere->id }}">{{ $matiere->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="enseignant_id" class="form-label">Enseignant <span class="text-danger">*</span></label>
                        <select name="enseignant_id" id="enseignant_id" class="form-control" required>
                            <option value="">Sélectionner un enseignant</option>
                            @foreach($enseignants as $enseignant)
                                <option value="{{ $enseignant->id }}">
                                    {{ $enseignant->utilisateur->nom ?? 'N/A' }} {{ $enseignant->utilisateur->prenom ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="jour" class="form-label">Jour <span class="text-danger">*</span></label>
                        <select name="jour" id="jour" class="form-control" required>
                            <option value="">Sélectionner un jour</option>
                            @foreach($jours as $jour)
                                <option value="{{ $jour }}">{{ ucfirst($jour) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="heure_debut" class="form-label">Heure début <span class="text-danger">*</span></label>
                                <input type="time" name="heure_debut" id="heure_debut" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Durée</label>
                                <input type="text" class="form-control" value="2 heures" readonly tabindex="-1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="heure_fin_affiche" class="form-label">Heure fin</label>
                                <input type="time" id="heure_fin_affiche" class="form-control" readonly tabindex="-1">
                                <input type="hidden" name="heure_fin" id="heure_fin" value="">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="salle" class="form-label">Salle</label>
                        <input type="text" name="salle" id="salle" class="form-control" placeholder="Ex: A3">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const DUREE_SECONDAIRE = {{ $dureeSecondaire }};

function parseTimeToMinutes(timeStr) {
    const parts = (timeStr || '').split(':');
    if (parts.length < 2) return null;
    return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
}

function minutesToTime(totalMinutes) {
    const h = Math.floor(totalMinutes / 60) % 24;
    const m = totalMinutes % 60;
    return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
}

function normalizeTime(val) {
    if (!val) return '';
    const parts = String(val).split(':');
    return String(parseInt(parts[0], 10)).padStart(2, '0') + ':' + String(parseInt(parts[1] || '0', 10)).padStart(2, '0');
}

function recalculerHeureFin() {
    const debut = normalizeTime($('#heure_debut').val());
    const startMin = parseTimeToMinutes(debut);
    if (startMin === null) return;
    const fin = minutesToTime(startMin + DUREE_SECONDAIRE);
    $('#heure_fin').val(fin);
    $('#heure_fin_affiche').val(fin);
    if (debut) $('#heure_debut').val(debut);
}

function resetCreneauForm() {
    $('#addCreneauForm')[0].reset();
    $('#heure_debut').val('08:00');
    recalculerHeureFin();
}

function addCreneau(jour, heure) {
    resetCreneauForm();
    $('#jour').val(jour);
    $('#heure_debut').val(normalizeTime(heure));
    recalculerHeureFin();
    $('#addCreneauModal').modal('show');
}

function deleteCreneau(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce créneau ?')) return;
    $.ajax({
        url: `/emplois-temps/${id}`,
        type: 'DELETE',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (response) {
            if (response.success) {
                showAlert('success', response.message);
                location.reload();
            } else {
                showAlert('error', 'Erreur lors de la suppression');
            }
        },
        error: function () {
            showAlert('error', 'Erreur lors de la suppression');
        }
    });
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    $('.container-fluid').prepend(`
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    setTimeout(function () { $('.alert').fadeOut(); }, 5000);
}

$(document).ready(function () {
    recalculerHeureFin();
    $('#heure_debut').on('change input', recalculerHeureFin);

    $('#addCreneauForm').on('submit', function (e) {
        e.preventDefault();
        recalculerHeureFin();

        const formData = new FormData(this);
        formData.set('heure_debut', normalizeTime($('#heure_debut').val()));
        formData.set('heure_fin', normalizeTime($('#heure_fin').val()));

        $.ajax({
            url: '{{ route("emplois-temps.store") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    showAlert('success', response.message);
                    $('#addCreneauModal').modal('hide');
                    location.reload();
                } else {
                    showAlert('error', response.message || 'Erreur lors de l\'ajout du créneau');
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;
                if (response && response.message) {
                    showAlert('error', response.message);
                } else if (response && response.errors) {
                    let errorMessage = 'Erreurs de validation:<br>';
                    for (const field in response.errors) {
                        errorMessage += '- ' + response.errors[field][0] + '<br>';
                    }
                    showAlert('error', errorMessage);
                } else {
                    showAlert('error', 'Erreur lors de l\'ajout du créneau');
                }
            }
        });
    });
});
</script>
@endsection
