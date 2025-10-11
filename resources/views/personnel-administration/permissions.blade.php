@extends('layouts.app')

@section('title', 'Gestion des Permissions')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>Gestion des Permissions
                    </h3>
                    <p class="text-muted mb-0">{{ $personnelAdministration->utilisateur->nom }} {{ $personnelAdministration->utilisateur->prenom }} - {{ $personnelAdministration->poste }}</p>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('personnel-administration.update-permissions', $personnelAdministration) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Informations du personnel -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="text-center">
                                    @php use Illuminate\Support\Facades\Storage; @endphp
                                    @if($personnelAdministration->utilisateur->photo_profil && Storage::disk('public')->exists($personnelAdministration->utilisateur->photo_profil))
                                        <img src="{{ asset('storage/' . $personnelAdministration->utilisateur->photo_profil) }}" 
                                             alt="Photo" 
                                             class="img-thumbnail rounded-circle mb-2" 
                                             style="width: 100px; height: 100px; object-fit: cover;">
                                    @else
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white mx-auto mb-2" 
                                             style="width: 100px; height: 100px;">
                                            {{ substr($personnelAdministration->utilisateur->prenom, 0, 1) }}{{ substr($personnelAdministration->utilisateur->nom, 0, 1) }}
                                        </div>
                                    @endif
                                    <h6 class="mb-0">{{ $personnelAdministration->utilisateur->nom }} {{ $personnelAdministration->utilisateur->prenom }}</h6>
                                    <small class="text-muted">{{ $personnelAdministration->poste }}</small>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Email:</strong> {{ $personnelAdministration->utilisateur->email }}</p>
                                        <p><strong>Département:</strong> {{ $personnelAdministration->departement ?? 'Non défini' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Date d'embauche:</strong> {{ $personnelAdministration->date_embauche->format('d/m/Y') }}</p>
                                        <p><strong>Statut:</strong> 
                                            @if($personnelAdministration->statut === 'actif')
                                                <span class="badge bg-success">Actif</span>
                                            @elseif($personnelAdministration->statut === 'inactif')
                                                <span class="badge bg-secondary">Inactif</span>
                                            @else
                                                <span class="badge bg-warning">Suspendu</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Debug des permissions -->
                        <div class="alert alert-info">
                            <strong>Debug:</strong> 
                            <br>Permissions actuelles: {{ json_encode($currentPermissions) }} ({{ count($currentPermissions) }} permissions)
                            <br>Type: {{ gettype($currentPermissions) }}
                            <br>Personnel ID: {{ $personnelAdministration->id }}
                            <br>Permissions brutes: {{ json_encode($personnelAdministration->permissions) }}
                        </div>

                        <!-- Permissions par catégorie -->
                        <div class="row">
                            @php
                                // Récupérer les permissions actuelles de manière robuste
                                $currentPermissions = $personnelAdministration->permissions ?? [];
                                
                                // Si c'est une chaîne, décoder le JSON
                                if (is_string($currentPermissions)) {
                                    $decoded = json_decode($currentPermissions, true);
                                    $currentPermissions = is_array($decoded) ? $decoded : [];
                                }
                                
                                // S'assurer que c'est un tableau
                                if (!is_array($currentPermissions)) {
                                    $currentPermissions = [];
                                }
                                
                                // Debug supplémentaire
                                \Log::info('Permissions dans la vue:', [
                                    'original' => $personnelAdministration->permissions,
                                    'processed' => $currentPermissions,
                                    'count' => count($currentPermissions)
                                ]);
                                
                                $permissionsByCategory = [
                                    'Élèves' => array_filter($permissions, fn($key) => str_starts_with($key, 'eleves'), ARRAY_FILTER_USE_KEY),
                                    'Enseignants' => array_filter($permissions, fn($key) => str_starts_with($key, 'enseignants'), ARRAY_FILTER_USE_KEY),
                                    'Classes' => array_filter($permissions, fn($key) => str_starts_with($key, 'classes'), ARRAY_FILTER_USE_KEY),
                                    'Matières' => array_filter($permissions, fn($key) => str_starts_with($key, 'matieres'), ARRAY_FILTER_USE_KEY),
                                    'Emplois du temps' => array_filter($permissions, fn($key) => str_starts_with($key, 'emplois_temps'), ARRAY_FILTER_USE_KEY),
                                    'Absences' => array_filter($permissions, fn($key) => str_starts_with($key, 'absences'), ARRAY_FILTER_USE_KEY),
                                    'Notes' => array_filter($permissions, fn($key) => str_starts_with($key, 'notes'), ARRAY_FILTER_USE_KEY),
                                    'Paiements' => array_filter($permissions, fn($key) => str_starts_with($key, 'paiements'), ARRAY_FILTER_USE_KEY),
                                    'Dépenses' => array_filter($permissions, fn($key) => str_starts_with($key, 'depenses'), ARRAY_FILTER_USE_KEY),
                                    'Statistiques' => array_filter($permissions, fn($key) => str_starts_with($key, 'statistiques'), ARRAY_FILTER_USE_KEY),
                                    'Notifications' => array_filter($permissions, fn($key) => str_starts_with($key, 'notifications'), ARRAY_FILTER_USE_KEY),
                                    'Rapports' => array_filter($permissions, fn($key) => str_starts_with($key, 'rapports'), ARRAY_FILTER_USE_KEY),
                                    'Cartes Enseignants' => array_filter($permissions, fn($key) => str_starts_with($key, 'cartes_enseignants'), ARRAY_FILTER_USE_KEY),
                                ];
                            @endphp

                            <!-- Test simple -->
                            <div class="alert alert-warning mb-4">
                                <strong>Test:</strong> 
                                <input type="checkbox" name="permissions[]" value="test.permission" id="test-checkbox">
                                <label for="test-checkbox">Permission de test</label>
                                <button type="button" onclick="testForm()" class="btn btn-sm btn-warning ms-2">Tester le formulaire</button>
                                <button type="button" onclick="submitTest()" class="btn btn-sm btn-info ms-2">Soumettre test</button>
                                <button type="button" onclick="checkAll()" class="btn btn-sm btn-success ms-2">Cocher tout</button>
                                <button type="button" onclick="debugForm()" class="btn btn-sm btn-danger ms-2">Debug complet</button>
                            </div>

                            @foreach($permissionsByCategory as $category => $categoryPermissions)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-folder me-2"></i>{{ $category }}
                                                <span class="badge bg-primary ms-2">{{ count($categoryPermissions) }}</span>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @foreach($categoryPermissions as $key => $label)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="permissions[]" value="{{ $key }}" 
                                                           id="permission_{{ $key }}"
                                                           {{ in_array($key, $currentPermissions) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="permission_{{ $key }}">
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Actions globales -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-tools me-2"></i>Actions Rapides
                                        </h6>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary" onclick="selectAllPermissions()">
                                                <i class="fas fa-check-double me-1"></i>Tout sélectionner
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="deselectAllPermissions()">
                                                <i class="fas fa-times me-1"></i>Tout désélectionner
                                            </button>
                                            <button type="button" class="btn btn-outline-info" onclick="selectViewOnly()">
                                                <i class="fas fa-eye me-1"></i>Lecture seule
                                            </button>
                                            <button type="button" class="btn btn-outline-success" onclick="selectFullAccess()">
                                                <i class="fas fa-user-shield me-1"></i>Accès complet
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('personnel-administration.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>Retour à la liste
                                    </a>
                                    <div>
                                        <a href="{{ route('personnel-administration.edit', $personnelAdministration) }}" class="btn btn-outline-warning me-2">
                                            <i class="fas fa-edit me-1"></i>Modifier le profil
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Sauvegarder les permissions
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectAllPermissions() {
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = true);
}

function deselectAllPermissions() {
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
}

function selectViewOnly() {
    deselectAllPermissions();
    const viewCheckboxes = document.querySelectorAll('input[name="permissions[]"][value$=".view"]');
    viewCheckboxes.forEach(checkbox => checkbox.checked = true);
}

function selectFullAccess() {
    selectAllPermissions();
}

function testForm() {
    const checkedPermissions = document.querySelectorAll('input[name="permissions[]"]:checked');
    console.log('=== TEST DU FORMULAIRE ===');
    console.log('Nombre de permissions cochées:', checkedPermissions.length);
    
    checkedPermissions.forEach((checkbox, index) => {
        console.log(`Permission ${index + 1}:`, checkbox.value);
    });
    
    // Tester la soumission
    const form = document.querySelector('form');
    const formData = new FormData(form);
    console.log('FormData permissions:', formData.getAll('permissions[]'));
    
    alert(`Test terminé! Vérifiez la console. Permissions cochées: ${checkedPermissions.length}`);
}

function submitTest() {
    // Cocher la case de test
    document.getElementById('test-checkbox').checked = true;
    
    // Soumettre le formulaire
    const form = document.querySelector('form');
    form.submit();
}

function checkAll() {
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    console.log('Toutes les permissions ont été cochées');
    alert('Toutes les permissions ont été cochées!');
}

function debugForm() {
    console.log('=== DEBUG COMPLET DU FORMULAIRE ===');
    
    // 1. Vérifier le formulaire
    const form = document.querySelector('form');
    console.log('Formulaire trouvé:', form ? 'OUI' : 'NON');
    console.log('Action du formulaire:', form ? form.action : 'N/A');
    console.log('Méthode du formulaire:', form ? form.method : 'N/A');
    
    // 2. Vérifier toutes les cases à cocher
    const allCheckboxes = document.querySelectorAll('input[type="checkbox"]');
    console.log('Total des cases à cocher:', allCheckboxes.length);
    
    // 3. Vérifier les permissions spécifiques
    const permissionCheckboxes = document.querySelectorAll('input[name="permissions[]"]');
    console.log('Cases permissions[]:', permissionCheckboxes.length);
    
    // 4. Vérifier les cases cochées
    const checkedPermissions = document.querySelectorAll('input[name="permissions[]"]:checked');
    console.log('Permissions cochées:', checkedPermissions.length);
    
    // 5. Afficher les valeurs des permissions cochées
    const checkedValues = [];
    checkedPermissions.forEach(checkbox => {
        checkedValues.push(checkbox.value);
    });
    console.log('Valeurs des permissions cochées:', checkedValues);
    
    // 6. Vérifier les champs cachés
    const hiddenInputs = document.querySelectorAll('input[type="hidden"]');
    console.log('Champs cachés:', hiddenInputs.length);
    hiddenInputs.forEach(input => {
        console.log('Champ caché:', input.name, '=', input.value);
    });
    
    // 7. Tester la création de FormData
    const formData = new FormData(form);
    console.log('FormData créé avec succès');
    console.log('Permissions dans FormData:', formData.getAll('permissions[]'));
    
    // 8. Afficher un résumé
    alert(`Debug terminé!\n\n` +
          `Total cases: ${allCheckboxes.length}\n` +
          `Permissions cases: ${permissionCheckboxes.length}\n` +
          `Cochées: ${checkedPermissions.length}\n` +
          `Valeurs: ${checkedValues.join(', ')}\n\n` +
          `Vérifiez la console pour plus de détails.`);
}

// Gérer la soumission du formulaire
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(e) {
        const checkedPermissions = document.querySelectorAll('input[name="permissions[]"]:checked');
        console.log('Permissions cochées au moment de la soumission:', checkedPermissions.length);
        
        // Afficher les permissions cochées dans la console
        checkedPermissions.forEach((checkbox, index) => {
            console.log(`Permission ${index + 1}:`, checkbox.value);
        });
        
        // Si aucune permission n'est cochée, ajouter une permission par défaut
        if (checkedPermissions.length === 0) {
            console.log('Aucune permission cochée, ajout d\'une permission par défaut');
            const defaultInput = document.createElement('input');
            defaultInput.type = 'hidden';
            defaultInput.name = 'permissions[]';
            defaultInput.value = 'eleves.view';
            form.appendChild(defaultInput);
        }
    });
});
</script>
@endsection









