@extends('layouts.app')

@section('title', 'Test Modification Enseignant')

@section('content')
<div class="container-fluid">
    <h1>Test Modification Enseignant</h1>
    
    @if($errors->any())
    <div class="alert alert-danger">
        <h5>Erreurs de validation :</h5>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Test Simple - Modification Enseignant</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('enseignants.test-update', $enseignant->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="nom" name="nom" 
                                   value="{{ old('nom', $enseignant->utilisateur->nom) }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom *</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" 
                                   value="{{ old('prenom', $enseignant->utilisateur->prenom) }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="{{ old('email', $enseignant->utilisateur->email) }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="specialite" class="form-label">Spécialité *</label>
                            <input type="text" class="form-control" id="specialite" name="specialite" 
                                   value="{{ old('specialite', $enseignant->specialite) }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Tester la Mise à Jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Informations Actuelles</h5>
                </div>
                <div class="card-body">
                    <p><strong>Nom:</strong> {{ $enseignant->utilisateur->nom }}</p>
                    <p><strong>Prénom:</strong> {{ $enseignant->utilisateur->prenom }}</p>
                    <p><strong>Email:</strong> {{ $enseignant->utilisateur->email }}</p>
                    <p><strong>Spécialité:</strong> {{ $enseignant->specialite }}</p>
                    <p><strong>Numéro employé:</strong> {{ $enseignant->numero_employe }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection





