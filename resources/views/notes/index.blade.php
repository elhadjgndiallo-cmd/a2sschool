@extends('layouts.app')

@section('title', 'Gestion des Notes')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-clipboard-list me-2"></i>
        @if(auth()->user()->isAdmin())
            Gestion des Notes
        @else
            Mes Classes - Saisie des Notes
        @endif
    </h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    @if(auth()->user()->isAdmin())
                        Sélectionner une classe pour saisir les notes
                    @else
                        Mes classes assignées
                    @endif
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($classes as $classe)
                    <div class="col-md-4 mb-3">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x text-primary mb-3"></i>
                                <h5>{{ $classe->nom }}</h5>
                                <p class="text-muted">
                                    {{ $classe->eleves->count() }} élèves
                                    <br>
                                    <small>Niveau: {{ $classe->niveau }}</small>
                                </p>
                                <div class="btn-group-vertical w-100">
                                    <a href="{{ route('notes.saisir', $classe->id) }}" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit me-1"></i>
                                        Saisir Notes
                                    </a>
                                    <a href="{{ route('notes.statistiques', $classe->id) }}" 
                                       class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Statistiques
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                @if($classes->isEmpty())
                <div class="text-center text-muted">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <p>Aucune classe disponible. Veuillez d'abord créer des classes.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Actions rapides -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Actions Rapides</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <button type="button" class="btn btn-success btn-block" onclick="genererBulletins()">
                            <i class="fas fa-file-alt me-2"></i>
                            Générer Bulletins
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('notes.rapport-global') }}" class="btn btn-info btn-block">
                            <i class="fas fa-chart-line me-2"></i>
                            Rapport Global
                        </a>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-warning btn-block" onclick="exporterNotes()">
                            <i class="fas fa-download me-2"></i>
                            Exporter Notes
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('notes.parametres') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-cog me-2"></i>
                            Paramètres
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour sélection de classe (Bulletins) -->
<div class="modal fade" id="bulletinsModal" tabindex="-1" data-bs-backdrop="false" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Générer Bulletins</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulletinsForm">
                    <div class="mb-3">
                        <label for="classe_bulletins" class="form-label">Sélectionner une classe</label>
                        <select class="form-select" id="classe_bulletins" required>
                            <option value="">Choisir une classe</option>
                            @foreach($classes as $classe)
                            <option value="{{ $classe->id }}">{{ $classe->nom }} - {{ $classe->niveau }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" onclick="confirmerBulletins()">Générer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour export des notes -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exporter Notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm">
                    <div class="mb-3">
                        <label for="classe_export" class="form-label">Classe (optionnel)</label>
                        <select class="form-select" id="classe_export">
                            <option value="">Toutes les classes</option>
                            @foreach($classes as $classe)
                            <option value="{{ $classe->id }}">{{ $classe->nom }} - {{ $classe->niveau }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="periode_export" class="form-label">Période (optionnel)</label>
                        <select class="form-select" id="periode_export">
                            <option value="">Toutes les périodes</option>
                            <option value="trimestre1">Trimestre 1</option>
                            <option value="trimestre2">Trimestre 2</option>
                            <option value="trimestre3">Trimestre 3</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" onclick="confirmerExport()">Exporter</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function genererBulletins() {
    new bootstrap.Modal(document.getElementById('bulletinsModal')).show();
}

function confirmerBulletins() {
    const classeId = document.getElementById('classe_bulletins').value;
    if (!classeId) {
        alert('Veuillez sélectionner une classe');
        return;
    }
    
    window.location.href = `/notes/bulletins/${classeId}`;
    bootstrap.Modal.getInstance(document.getElementById('bulletinsModal')).hide();
}

function exporterNotes() {
    new bootstrap.Modal(document.getElementById('exportModal')).show();
}

function confirmerExport() {
    const classeId = document.getElementById('classe_export').value;
    const periode = document.getElementById('periode_export').value;
    
    let url = '/notes/export?';
    const params = new URLSearchParams();
    
    if (classeId) params.append('classe_id', classeId);
    if (periode) params.append('periode', periode);
    
    url += params.toString();
    
    // Créer un lien temporaire pour télécharger
    const link = document.createElement('a');
    link.href = url;
    link.download = 'notes_export.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
}
</script>
@endpush

@push('styles')
<style>
.btn-block { width: 100%; }
</style>
@endpush
@endsection
