@extends('layouts.app')

@php use Illuminate\Support\Facades\Storage; @endphp

@section('title', 'Mon profil')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-edit me-2"></i>
        Mon profil
    </h1>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informations personnelles</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profil.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="text-center mb-4">
                        <div id="photo-preview" class="mb-3">
                            @if($utilisateur->photo_profil && Storage::disk('public')->exists($utilisateur->photo_profil))
                                <img src="{{ asset('storage/' . $utilisateur->photo_profil) }}"
                                     alt="Photo de profil"
                                     class="rounded-circle"
                                     id="preview-image"
                                     style="width: 120px; height: 120px; object-fit: cover;">
                            @else
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white mx-auto"
                                     id="preview-initials"
                                     style="width: 120px; height: 120px; font-size: 2rem;">
                                    {{ strtoupper(substr($utilisateur->prenom, 0, 1) . substr($utilisateur->nom, 0, 1)) }}
                                </div>
                                <img src="" alt="Aperçu" class="rounded-circle d-none" id="preview-image"
                                     style="width: 120px; height: 120px; object-fit: cover;">
                            @endif
                        </div>
                        <label for="photo_profil" class="form-label">Photo de profil</label>
                        <input type="file" class="form-control @error('photo_profil') is-invalid @enderror"
                               id="photo_profil" name="photo_profil" accept="image/*">
                        <div class="form-text">JPEG, PNG ou GIF. Taille max : 2 Mo.</div>
                        @error('photo_profil')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nom') is-invalid @enderror"
                                       id="nom" name="nom" value="{{ old('nom', $utilisateur->nom) }}" required>
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('prenom') is-invalid @enderror"
                                       id="prenom" name="prenom" value="{{ old('prenom', $utilisateur->prenom) }}" required>
                                @error('prenom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email', $utilisateur->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control @error('telephone') is-invalid @enderror"
                                       id="telephone" name="telephone" value="{{ old('telephone', $utilisateur->telephone) }}">
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <textarea class="form-control @error('adresse') is-invalid @enderror"
                                          id="adresse" name="adresse" rows="2">{{ old('adresse', $utilisateur->adresse) }}</textarea>
                                @error('adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('password.change.form') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-key me-1"></i>
                            Changer le mot de passe
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Compte</h5>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Rôle :</strong>
                    <span class="badge bg-primary">{{ ucfirst($utilisateur->role) }}</span>
                </p>
                <p class="mb-0 text-muted">
                    Le nom affiché en haut à droite sera mis à jour après l'enregistrement.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('photo_profil')?.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (e) {
        const img = document.getElementById('preview-image');
        const initials = document.getElementById('preview-initials');
        if (img) {
            img.src = e.target.result;
            img.classList.remove('d-none');
        }
        if (initials) {
            initials.classList.add('d-none');
        }
    };
    reader.readAsDataURL(file);
});
</script>
@endpush
