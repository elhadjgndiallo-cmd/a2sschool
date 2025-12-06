@extends('layouts.app')

@section('title', 'Modifier Tests Mensuels - ' . $classe->nom)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-edit me-2"></i>
        Modifier Tests Mensuels - {{ $classe->nom }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('notes.mensuel.classe', $classe->id) }}?mois={{ $mois }}&annee={{ $annee }}" 
           class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-filter me-2"></i>
            Période
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('notes.mensuel.modifier', $classe->id) }}">
            <div class="row">
                <div class="col-md-4">
                    <label for="mois" class="form-label">Mois</label>
                    <select name="mois" id="mois" class="form-select">
                        @foreach($moisListe as $num => $nom)
                        <option value="{{ $num }}" {{ $mois == $num ? 'selected' : '' }}>
                            {{ $nom }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="annee" class="form-label">Année</label>
                    <select name="annee" id="annee" class="form-select">
                        @for($i = date('Y') - 2; $i <= date('Y') + 2; $i++)
                        <option value="{{ $i }}" {{ $annee == $i ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>
                        Changer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tests à modifier -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-edit me-2"></i>
            Tests du {{ $moisListe[$mois] }} {{ $annee }} - Modifications
        </h5>
        <span class="badge bg-primary">{{ $tests->count() }} tests</span>
    </div>
    <div class="card-body p-0">
        @if($tests->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Matricule</th>
                        <th scope="col">Nom</th>
                        <th scope="col">Prénom</th>
                        <th scope="col">Matière</th>
                        <th scope="col">Enseignant</th>
                        <th scope="col">Note</th>
                        <th scope="col">Coefficient</th>
                        <th scope="col">Date</th>
                        <th scope="col" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tests as $test)
                    <tr>
                        <td class="fw-bold">{{ $test->eleve->numero_etudiant ?? 'N/A' }}</td>
                        <td>{{ $test->eleve->utilisateur->nom ?? 'N/A' }}</td>
                        <td>{{ $test->eleve->utilisateur->prenom ?? 'N/A' }}</td>
                        <td>{{ $test->matiere->nom }}</td>
                        <td>
                            @if($test->enseignant)
                                {{ $test->enseignant->utilisateur->nom }} {{ $test->enseignant->utilisateur->prenom }}
                            @else
                                <span class="text-muted">Non assigné</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $test->note >= 10 ? 'success' : ($test->note >= 5 ? 'warning' : 'danger') }}">
                                {{ number_format($test->note, 2) }}/20
                            </span>
                        </td>
                        <td>{{ $test->coefficient }}</td>
                        <td>{{ $test->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('notes.mensuel.edit', $test->id) }}" 
                                   class="btn btn-outline-primary" 
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-outline-danger" 
                                        onclick="confirmDelete({{ $test->id }})"
                                        title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Aucun test à modifier</h5>
            <p class="text-muted">Aucun test mensuel n'a été enregistré pour {{ $moisListe[$mois] }} {{ $annee }}.</p>
            <a href="{{ route('notes.mensuel.saisir', $classe->id) }}?mois={{ $mois }}&annee={{ $annee }}" 
               class="btn btn-success">
                <i class="fas fa-plus me-1"></i>
                Saisir des tests
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Formulaire de suppression caché -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(testId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette note ? Cette action est irréversible.')) {
        // Récupérer le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            alert('Erreur : Token CSRF non trouvé. Veuillez recharger la page.');
            return;
        }
        
        // Créer un formulaire dynamique et le soumettre
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("notes.mensuel.destroy", ":id") }}'.replace(':id', testId);
        form.style.display = 'none';
        
        // Ajouter le token CSRF
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        // Ajouter la méthode DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        // Ajouter le formulaire au body et le soumettre
        document.body.appendChild(form);
        
        console.log('Soumission de la suppression pour le test ID:', testId);
        console.log('Action:', form.action);
        
        // Soumettre le formulaire
        form.submit();
    }
}
</script>
@endsection
