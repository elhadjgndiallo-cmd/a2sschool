@extends('layouts.app')

@section('title', 'Nouveau Message')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center pt-3 pb-2 mb-3 border-bottom gap-2">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-paper-plane text-primary me-2"></i>
            Nouveau Message
        </h1>
        <p class="text-muted mb-0">Envoyez un message à l'administration</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('parent.notifications.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                <span class="d-none d-sm-inline">Retour</span>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Rédiger votre message
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('parent.notifications.store') }}" method="POST">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="destinataire_id" class="form-label">Destinataire <span class="text-danger">*</span></label>
                            <select name="destinataire_id" id="destinataire_id" class="form-select @error('destinataire_id') is-invalid @enderror" required>
                                <option value="">Sélectionner un destinataire</option>
                                @foreach($destinataires as $destinataire)
                                    <option value="{{ $destinataire->id }}" {{ old('destinataire_id') == $destinataire->id ? 'selected' : '' }}>
                                        {{ $destinataire->nom }} {{ $destinataire->prenom }} 
                                        ({{ ucfirst(str_replace('_', ' ', $destinataire->role)) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('destinataire_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="type" class="form-label">Type de message <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">Sélectionner un type</option>
                                <option value="question" {{ old('type') == 'question' ? 'selected' : '' }}>Question</option>
                                <option value="demande" {{ old('type') == 'demande' ? 'selected' : '' }}>Demande</option>
                                <option value="information" {{ old('type') == 'information' ? 'selected' : '' }}>Information</option>
                                <option value="plainte" {{ old('type') == 'plainte' ? 'selected' : '' }}>Plainte</option>
                                <option value="autre" {{ old('type') == 'autre' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="priorite" class="form-label">Priorité <span class="text-danger">*</span></label>
                            <select name="priorite" id="priorite" class="form-select @error('priorite') is-invalid @enderror" required>
                                <option value="">Sélectionner une priorité</option>
                                <option value="faible" {{ old('priorite') == 'faible' ? 'selected' : '' }}>Faible</option>
                                <option value="moyenne" {{ old('priorite') == 'moyenne' ? 'selected' : '' }}>Moyenne</option>
                                <option value="haute" {{ old('priorite') == 'haute' ? 'selected' : '' }}>Haute</option>
                                <option value="urgente" {{ old('priorite') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                            </select>
                            @error('priorite')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="titre" class="form-label">Sujet <span class="text-danger">*</span></label>
                            <input type="text" name="titre" id="titre" 
                                   class="form-control @error('titre') is-invalid @enderror" 
                                   value="{{ old('titre') }}" 
                                   placeholder="Résumé de votre message" required>
                            @error('titre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea name="message" id="message" rows="8" 
                                      class="form-control @error('message') is-invalid @enderror" 
                                      placeholder="Décrivez votre message en détail..." required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('parent.notifications.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i>
                                    Envoyer le message
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Conseils
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="fas fa-lightbulb me-1"></i>
                        Pour un message efficace :
                    </h6>
                    <ul class="mb-0">
                        <li>Soyez précis dans le sujet</li>
                        <li>Décrivez clairement votre demande</li>
                        <li>Mentionnez les informations importantes</li>
                        <li>Choisissez la bonne priorité</li>
                    </ul>
                </div>

                <div class="alert alert-warning">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Types de messages :
                    </h6>
                    <ul class="mb-0">
                        <li><strong>Question :</strong> Demande d'information</li>
                        <li><strong>Demande :</strong> Demande d'action</li>
                        <li><strong>Information :</strong> Partage d'information</li>
                        <li><strong>Plainte :</strong> Signalement d'un problème</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
