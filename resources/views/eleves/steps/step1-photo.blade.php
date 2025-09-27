{{-- Étape 1: Photo de l'élève --}}
<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-camera me-2"></i>
            Étape 1/4 - Photo de l'Élève
        </h5>
    </div>
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('eleves.store-step') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="step" value="1">

            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="text-center mb-4">
                        <div class="photo-preview-container mb-3">
                            <div id="photo-preview" class="photo-preview">
                                @if(isset($studentData['photo_profil_preview']))
                                    <img src="{{ $studentData['photo_profil_preview'] }}" alt="Photo élève" class="preview-image">
                                @else
                                    <div class="placeholder-photo">
                                        <i class="fas fa-user fa-4x text-muted mb-3"></i>
                                        <p class="text-muted">Photo de l'élève</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="photo_profil" class="form-label">
                                <i class="fas fa-camera me-1"></i>Choisir une photo
                            </label>
                            <input type="file" class="form-control @error('photo_profil') is-invalid @enderror" 
                                   id="photo_profil" name="photo_profil" accept="image/*" onchange="previewPhoto(this)">
                            <small class="text-muted">Formats acceptés: JPG, PNG, GIF (Max: 2MB)</small>
                            @error('photo_profil')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Optionnel:</strong> Vous pouvez ajouter la photo maintenant ou la télécharger plus tard depuis la fiche de l'élève.
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="{{ route('eleves.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-arrow-right me-2"></i>Étape Suivante
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.photo-preview-container {
    display: flex;
    justify-content: center;
}

.photo-preview {
    width: 200px;
    height: 200px;
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    position: relative;
    overflow: hidden;
}

.photo-preview.has-image {
    border: 2px solid #0d6efd;
    background-color: white;
}

.placeholder-photo {
    text-align: center;
}

.preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}
</style>

<script>
function previewPhoto(input) {
    const preview = document.getElementById('photo-preview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Photo élève" class="preview-image">`;
            preview.classList.add('has-image');
        };
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.innerHTML = `
            <div class="placeholder-photo">
                <i class="fas fa-user fa-4x text-muted mb-3"></i>
                <p class="text-muted">Photo de l'élève</p>
            </div>
        `;
        preview.classList.remove('has-image');
    }
}
</script>









































