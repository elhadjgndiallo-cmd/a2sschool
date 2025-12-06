@extends('layouts.app')

@section('title', 'Modifier Test Mensuel')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i>
                        Modifier un Test Mensuel
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('notes.mensuel.modifier', $test->classe_id) }}?mois={{ $test->mois }}&annee={{ $test->annee }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Erreur de validation :</strong>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Informations du test -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-user"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Élève</span>
                                    <span class="info-box-number">{{ $test->eleve->utilisateur->prenom ?? 'N/A' }} {{ $test->eleve->utilisateur->nom ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-book"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Matière</span>
                                    <span class="info-box-number">{{ $test->matiere->nom }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Période</span>
                                    <span class="info-box-number">
                                        @php
                                            $moisListe = [
                                                1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                                                5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                                                9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
                                            ];
                                        @endphp
                                        {{ $moisListe[$test->mois] ?? 'N/A' }} {{ $test->annee }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('notes.mensuel.update', $test) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Note -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="note">
                                        <i class="fas fa-pen"></i>
                                        Note
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('note') is-invalid @enderror" 
                                           id="note" 
                                           name="note" 
                                           value="{{ old('note', $test->note) }}"
                                           min="0" 
                                           max="20" 
                                           step="0.01"
                                           placeholder="Ex: 15.5"
                                           required>
                                    @error('note')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Note sur 20. Doit être entre 0 et 20.
                                    </small>
                                </div>
                            </div>

                            <!-- Coefficient -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="coefficient">
                                        <i class="fas fa-weight"></i>
                                        Coefficient
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('coefficient') is-invalid @enderror" 
                                           id="coefficient" 
                                           name="coefficient" 
                                           value="{{ old('coefficient', $test->coefficient) }}"
                                           min="1" 
                                           max="10" 
                                           step="1"
                                           required>
                                    @error('coefficient')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Coefficient entre 1 et 10.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Informations supplémentaires (lecture seule) -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Enseignant</label>
                                    <input type="text" 
                                           class="form-control" 
                                           value="{{ $test->enseignant ? ($test->enseignant->utilisateur->prenom ?? '') . ' ' . ($test->enseignant->utilisateur->nom ?? '') : 'Non assigné' }}" 
                                           readonly
                                           style="background-color: #f8f9fa;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date de création</label>
                                    <input type="text" 
                                           class="form-control" 
                                           value="{{ $test->created_at->format('d/m/Y H:i') }}" 
                                           readonly
                                           style="background-color: #f8f9fa;">
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="form-group">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Mettre à jour la note
                                </button>
                                <a href="{{ route('notes.mensuel.modifier', $test->classe_id) }}?mois={{ $test->mois }}&annee={{ $test->annee }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                    Annuler
                                </a>
                                @if(auth()->user()->hasPermission('notes.delete'))
                                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                        <i class="fas fa-trash"></i>
                                        Supprimer
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>

                    <!-- Formulaire de suppression caché -->
                    <form id="delete-form" action="{{ route('notes.mensuel.destroy', $test) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Confirmation de suppression
    function confirmDelete() {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette note mensuelle ? Cette action est irréversible.')) {
            document.getElementById('delete-form').submit();
        }
    }
</script>
@endsection

