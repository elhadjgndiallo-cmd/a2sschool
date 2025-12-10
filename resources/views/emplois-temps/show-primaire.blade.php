@extends('layouts.app')

@section('title', 'Emploi du temps - ' . $classe->nom)

@section('content')
<div class="container-fluid">
    <!-- Messages de session -->
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Emploi du temps - {{ $classe->nom }} (Primaire)
                    </h5>
                    <div>
                        <a href="{{ route('classes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour aux Classes
                        </a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCreneauModal">
                            <i class="fas fa-plus me-1"></i>
                            Ajouter un créneau
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th style="background-color: #6c757d; color: white; font-weight: bold;">Heure</th>
                                    @foreach($jours as $jour)
                                        <th class="text-center" style="background-color: #343a40; color: white; font-weight: bold;">{{ ucfirst($jour) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($heures as $heure)
                                    <tr style="height: 100px;">
                                        <td class="fw-bold" style="background-color: #f8f9fa; vertical-align: middle; height: 100px; font-size: 1rem;">{{ $heure }}</td>
                                        @foreach($jours as $jour)
                                            <td class="text-center position-relative" style="min-height: 100px; height: 100px; vertical-align: middle; padding: 8px;">
                                                @php
                                                    // Chercher les créneaux qui commencent à cette heure exacte
                                                    $creneaux = $emploisTemps->where('jour_semaine', $jour)
                                                        ->filter(function($emploi) use ($heure) {
                                                            $heureDebut = \Carbon\Carbon::parse($emploi->heure_debut)->format('H:i');
                                                            $heureFin = \Carbon\Carbon::parse($emploi->heure_fin)->format('H:i');
                                                            return $heureDebut == $heure || ($heureDebut <= $heure && $heureFin > $heure);
                                                        });
                                                @endphp
                                                
                                                @if($heure == '10:00')
                                                    {{-- Afficher RÉCRÉATION pour le créneau 10:00-10:15 --}}
                                                    <div class="text-center p-2" style="font-size: 0.85rem; font-weight: 600; color: #6c757d; font-style: italic;">
                                                        RÉCRÉATION
                                                    </div>
                                                @elseif($creneaux->isNotEmpty())
                                                    @foreach($creneaux as $creneau)
                                                        <div class="creneau-item bg-primary text-white p-2 rounded mb-1" 
                                                             style="font-size: 0.85rem; position: relative; background-color: #007bff !important;">
                                                            <div class="fw-bold mb-1">{{ $creneau->matiere->nom }}</div>
                                                            <div class="small mb-2">{{ strtoupper($creneau->enseignant->utilisateur->nom ?? 'N/A') }} {{ $creneau->enseignant->utilisateur->prenom ?? '' }}</div>
                                                            <div class="creneau-actions d-flex gap-1 justify-content-center">
                                                                <button class="btn btn-sm btn-outline-light" 
                                                                        onclick="event.stopPropagation(); editCreneau({{ $creneau->id }})"
                                                                        style="padding: 2px 6px; font-size: 0.7rem;">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-danger" 
                                                                        onclick="event.stopPropagation(); deleteCreneau({{ $creneau->id }})"
                                                                        style="padding: 2px 6px; font-size: 0.7rem;">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <button class="btn btn-outline-secondary btn-sm w-100 h-100 d-flex align-items-center justify-content-center"
                                                            onclick="addCreneau('{{ $jour }}', '{{ $heure }}')"
                                                            style="min-height: 90px; height: 90px; border: 1px dashed #dee2e6; background-color: transparent;">
                                                        <span style="font-size: 1.5rem; color: #6c757d;">+</span>
                                                    </button>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter un créneau -->
<div class="modal fade" id="addCreneauModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Créneau</h5>
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
                            @foreach(\App\Models\Enseignant::with('utilisateur')->get() as $enseignant)
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="heure_debut" class="form-label">Heure Début <span class="text-danger">*</span></label>
                                <input type="time" name="heure_debut" id="heure_debut" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="heure_fin" class="form-label">Heure Fin <span class="text-danger">*</span></label>
                                <input type="time" name="heure_fin" id="heure_fin" class="form-control" required>
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
$(document).ready(function() {
    // Gestion du formulaire d'ajout de créneau
    $('#addCreneauForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: '{{ route("emplois-temps.store") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    $('#addCreneauModal').modal('hide');
                    location.reload();
                } else {
                    showAlert('error', response.message || 'Erreur lors de l\'ajout du créneau');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                if (response && response.message) {
                    showAlert('error', response.message);
                } else if (response && response.errors) {
                    let errorMessage = 'Erreurs de validation:\n';
                    for (const field in response.errors) {
                        errorMessage += '- ' + response.errors[field][0] + '\n';
                    }
                    showAlert('error', errorMessage);
                } else {
                    showAlert('error', 'Erreur lors de l\'ajout du créneau');
                }
            }
        });
    });
});

// Fonction pour ajouter un créneau à une position spécifique
function addCreneau(jour, heure, heureFin = null) {
    $('#jour').val(jour);
    $('#heure_debut').val(heure);
    
    if (heureFin) {
        $('#heure_fin').val(heureFin);
    } else {
        // Pour le primaire, calculer l'heure de fin (30 minutes plus tard)
        const heureDebut = heure.split(':');
        let heureFinCalc = parseInt(heureDebut[0]);
        let minuteFinCalc = parseInt(heureDebut[1]) + 30;
        
        if (minuteFinCalc >= 60) {
            heureFinCalc += 1;
            minuteFinCalc -= 60;
        }
        
        const heureFinStr = heureFinCalc.toString().padStart(2, '0') + ':' + minuteFinCalc.toString().padStart(2, '0');
        $('#heure_fin').val(heureFinStr);
    }
    
    $('#addCreneauModal').modal('show');
}

// Fonction pour supprimer un créneau
function deleteCreneau(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce créneau ?')) {
        $.ajax({
            url: `/emplois-temps/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    location.reload();
                } else {
                    showAlert('error', 'Erreur lors de la suppression');
                }
            },
            error: function() {
                showAlert('error', 'Erreur lors de la suppression');
            }
        });
    }
}

// Fonction pour afficher les alertes
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('.container-fluid').prepend(alertHtml);
    
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}
</script>
@endsection



