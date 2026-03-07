@extends('layouts.app')

@section('title', 'Personnalisation des couleurs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-palette"></i>
                        Personnalisation des couleurs
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('parametres.couleurs.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Général -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-cog"></i>
                                Couleurs générales
                            </h6>
                            <div class="row">
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <label for="header_bg" class="form-label">Couleur d'en-tête</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="header_bg" name="couleurs[header_bg]" value="{{ $couleurs['general']['header_bg'] ?? '#34495e' }}">
                                        <input type="text" class="form-control" value="{{ $couleurs['general']['header_bg'] ?? '#34495e' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <label for="header_text" class="form-label">Texte d'en-tête</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="header_text" name="couleurs[header_text]" value="{{ $couleurs['general']['header_text'] ?? '#ffffff' }}">
                                        <input type="text" class="form-control" value="{{ $couleurs['general']['header_text'] ?? '#ffffff' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <label for="primary_color" class="form-label">Couleur principale</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="primary_color" name="couleurs[primary_color]" value="{{ $couleurs['general']['primary_color'] ?? '#007bff' }}">
                                        <input type="text" class="form-control" value="{{ $couleurs['general']['primary_color'] ?? '#007bff' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <label for="secondary_color" class="form-label">Couleur secondaire</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="secondary_color" name="couleurs[secondary_color]" value="{{ $couleurs['general']['secondary_color'] ?? '#6c757d' }}">
                                        <input type="text" class="form-control" value="{{ $couleurs['general']['secondary_color'] ?? '#6c757d' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <label for="success_color" class="form-label">Couleur de succès</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="success_color" name="couleurs[success_color]" value="{{ $couleurs['general']['success_color'] ?? '#28a745' }}">
                                        <input type="text" class="form-control" value="{{ $couleurs['general']['success_color'] ?? '#28a745' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <label for="danger_color" class="form-label">Couleur de danger</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="danger_color" name="couleurs[danger_color]" value="{{ $couleurs['general']['danger_color'] ?? '#dc3545' }}">
                                        <input type="text" class="form-control" value="{{ $couleurs['general']['danger_color'] ?? '#dc3545' }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bulletins -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-file-alt"></i>
                                Couleurs des bulletins
                            </h6>
                            <div class="row">
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <label for="bulletin_header_bg" class="form-label">En-tête du bulletin</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="bulletin_header_bg" name="couleurs[bulletin_header_bg]" value="{{ App\Models\CouleurParametre::getCouleur('bulletin_header_bg') ?? '#34495e' }}">
                                        <input type="text" class="form-control" value="{{ App\Models\CouleurParametre::getCouleur('bulletin_header_bg') ?? '#34495e' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <label for="bulletin_table_header_bg" class="form-label">En-tête des tableaux</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="bulletin_table_header_bg" name="couleurs[bulletin_table_header_bg]" value="{{ App\Models\CouleurParametre::getCouleur('bulletin_table_header_bg') ?? '#34495e' }}">
                                        <input type="text" class="form-control" value="{{ App\Models\CouleurParametre::getCouleur('bulletin_table_header_bg') ?? '#34495e' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <label for="bulletin_table_border" class="form-label">Bordures des tableaux</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="bulletin_table_border" name="couleurs[bulletin_table_border]" value="{{ App\Models\CouleurParametre::getCouleur('bulletin_table_border') ?? '#2c3e50' }}">
                                        <input type="text" class="form-control" value="{{ App\Models\CouleurParametre::getCouleur('bulletin_table_border') ?? '#2c3e50' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <label for="bulletin_success_text" class="form-label">Texte de succès</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="bulletin_success_text" name="couleurs[bulletin_success_text]" value="{{ App\Models\CouleurParametre::getCouleur('bulletin_success_text') ?? '#28a745' }}">
                                        <input type="text" class="form-control" value="{{ App\Models\CouleurParametre::getCouleur('bulletin_success_text') ?? '#28a745' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <label for="bulletin_danger_text" class="form-label">Texte de danger</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="bulletin_danger_text" name="couleurs[bulletin_danger_text]" value="{{ App\Models\CouleurParametre::getCouleur('bulletin_danger_text') ?? '#dc3545' }}">
                                        <input type="text" class="form-control" value="{{ App\Models\CouleurParametre::getCouleur('bulletin_danger_text') ?? '#dc3545' }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Résultats -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-chart-bar"></i>
                                Couleurs des résultats
                            </h6>
                            <div class="row">
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <label for="resultat_header_bg" class="form-label">En-tête des résultats</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="resultat_header_bg" name="couleurs[resultat_header_bg]" value="{{ App\Models\CouleurParametre::getCouleur('resultat_header_bg') ?? '#34495e' }}">
                                        <input type="text" class="form-control" value="{{ App\Models\CouleurParametre::getCouleur('resultat_header_bg') ?? '#34495e' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <label for="resultat_moyenne_text" class="form-label">Texte des moyennes</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="resultat_moyenne_text" name="couleurs[resultat_moyenne_text]" value="{{ App\Models\CouleurParametre::getCouleur('resultat_moyenne_text') ?? '#28a745' }}">
                                        <input type="text" class="form-control" value="{{ App\Models\CouleurParametre::getCouleur('resultat_moyenne_text') ?? '#28a745' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <label for="resultat_rang_text" class="form-label">Texte des rangs</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="resultat_rang_text" name="couleurs[resultat_rang_text]" value="{{ App\Models\CouleurParametre::getCouleur('resultat_rang_text') ?? '#007bff' }}">
                                        <input type="text" class="form-control" value="{{ App\Models\CouleurParametre::getCouleur('resultat_rang_text') ?? '#007bff' }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Documents -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-file"></i>
                                Couleurs des documents
                            </h6>
                            <div class="row">
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <label for="document_header_bg" class="form-label">En-tête des documents</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="document_header_bg" name="couleurs[document_header_bg]" value="{{ App\Models\CouleurParametre::getCouleur('document_header_bg') ?? '#34495e' }}">
                                        <input type="text" class="form-control" value="{{ App\Models\CouleurParametre::getCouleur('document_header_bg') ?? '#34495e' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <label for="document_title_bg" class="form-label">Titres des documents</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="document_title_bg" name="couleurs[document_title_bg]" value="{{ App\Models\CouleurParametre::getCouleur('document_title_bg') ?? '#6c757d' }}">
                                        <input type="text" class="form-control" value="{{ App\Models\CouleurParametre::getCouleur('document_title_bg') ?? '#6c757d' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <label for="document_border" class="form-label">Bordures des documents</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="document_border" name="couleurs[document_border]" value="{{ App\Models\CouleurParametre::getCouleur('document_border') ?? '#2c3e50' }}">
                                        <input type="text" class="form-control" value="{{ App\Models\CouleurParametre::getCouleur('document_border') ?? '#2c3e50' }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('parametres.couleurs.reset') }}" class="btn btn-warning" onclick="return confirm('Êtes-vous sûr de vouloir réinitialiser toutes les couleurs aux valeurs par défaut ?')">
                                <i class="fas fa-undo"></i>
                                Réinitialiser par défaut
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Enregistrer les couleurs
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.input-group .form-control-color {
    width: 50px;
    height: 38px;
}

.input-group .form-control:not(.form-control-color) {
    font-family: monospace;
    background-color: #f8f9fa;
}

.card-header {
    background: linear-gradient(135deg, {{ $couleurs['general']['primary_color'] ?? '#007bff' }} 0%, {{ $couleurs['general']['secondary_color'] ?? '#6c757d' }} 100%);
}

.text-primary {
    color: {{ $couleurs['general']['primary_color'] ?? '#007bff' }} !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Synchroniser les inputs color avec les inputs text
    document.querySelectorAll('input[type="color"]').forEach(function(colorInput) {
        colorInput.addEventListener('input', function() {
            const textInput = this.parentElement.querySelector('input[type="text"]');
            if (textInput) {
                textInput.value = this.value.toUpperCase();
            }
        });
    });
});
</script>
@endpush
