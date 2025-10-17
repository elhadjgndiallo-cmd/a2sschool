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
                        <td class="fw-bold">{{ $test->eleve->matricule }}</td>
                        <td>{{ $test->eleve->nom }}</td>
                        <td>{{ $test->eleve->prenom }}</td>
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
                                <button type="button" 
                                        class="btn btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editModal{{ $test->id }}"
                                        title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
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

<!-- Modales de modification -->
@foreach($tests as $test)
<div class="modal fade" id="editModal{{ $test->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $test->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel{{ $test->id }}">
                    Modifier la note - {{ $test->eleve->nom }} {{ $test->eleve->prenom }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('notes.mensuel.update', $test->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Élève</label>
                        <input type="text" class="form-control" value="{{ $test->eleve->matricule }} - {{ $test->eleve->nom }} {{ $test->eleve->prenom }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Matière</label>
                        <input type="text" class="form-control" value="{{ $test->matiere->nom }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="note{{ $test->id }}" class="form-label">Note <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control" 
                               id="note{{ $test->id }}" 
                               name="note" 
                               value="{{ $test->note }}" 
                               min="0" 
                               max="20" 
                               step="0.01" 
                               required>
                    </div>
                    <div class="mb-3">
                        <label for="coefficient{{ $test->id }}" class="form-label">Coefficient <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control" 
                               id="coefficient{{ $test->id }}" 
                               name="coefficient" 
                               value="{{ $test->coefficient }}" 
                               min="1" 
                               max="10" 
                               required>
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
@endforeach

<!-- Formulaire de suppression caché -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(testId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette note ? Cette action est irréversible.')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ route("notes.mensuel.destroy", ":id") }}'.replace(':id', testId);
        form.submit();
    }
}
</script>
@endsection
