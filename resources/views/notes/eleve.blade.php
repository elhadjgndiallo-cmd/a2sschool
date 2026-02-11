@extends('layouts.app')

@section('title', 'Notes de ' . $eleve->nom_complet)

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-graduation-cap me-2"></i>
            Notes de {{ $eleve->nom_complet }}
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            @if(auth()->user()->hasPermission('notes.create'))
                <a href="{{ route('notes.eleve.create', $eleve->id) }}" class="btn btn-success me-2">
                    <i class="fas fa-plus me-1"></i>
                    Ajouter une note
                </a>
            @endif
            @if(auth()->user()->hasPermission('notes.edit'))
                <a href="{{ route('notes.saisir', $eleve->classe_id) }}" class="btn btn-primary me-2">
                    <i class="fas fa-edit me-1"></i>
                    Modifier les notes
                </a>
            @endif
            <a href="{{ route('notes.bulletin.eleve', ['eleve' => $eleve->id, 'periode' => $periode]) }}" class="btn btn-outline-info me-2" target="_blank">
                <i class="fas fa-print me-1"></i>
                Imprimer
            </a>
            <a href="{{ route('notes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
    @endphp

    <!-- Informations de l'élève -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Nom complet:</strong> {{ $eleve->nom_complet }}</p>
                            <p class="mb-1"><strong>Numéro étudiant:</strong> {{ $eleve->numero_etudiant }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Classe:</strong> {{ $eleve->classe->nom }}</p>
                            <p class="mb-1"><strong>Période:</strong> 
                                @if($periode == 'trimestre1')
                                    Trimestre 1
                                @elseif($periode == 'trimestre2')
                                    Trimestre 2
                                @elseif($periode == 'trimestre3')
                                    Trimestre 3
                                @else
                                    {{ ucfirst($periode) }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Année scolaire:</strong> {{ $anneeScolaireActive ? $anneeScolaireActive->nom : (date('Y') . '-' . (date('Y')+1)) }}</p>
                            <p class="mb-1"><strong>Rang:</strong> {{ $rang }}ème sur {{ $eleve->classe->eleves->count() }} élèves</p>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h5 class="mb-0 text-primary">Moyenne Générale</h5>
                                <h3 class="mb-0 text-primary">{{ number_format($moyenneGenerale, 2) }}/{{ $eleve->classe->note_max }}</h3>
                                <span class="badge bg-{{ $appreciationGenerale['color'] }}">
                                    {{ $appreciationGenerale['label'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sélection de la période -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('notes.eleve', $eleve->id) }}" class="row g-3">
                <div class="col-md-4">
                    <label for="periode" class="form-label">Période</label>
                    <select class="form-select" id="periode" name="periode" onchange="this.form.submit()">
                        <option value="trimestre1" {{ $periode == 'trimestre1' ? 'selected' : '' }}>Trimestre 1</option>
                        <option value="trimestre2" {{ $periode == 'trimestre2' ? 'selected' : '' }}>Trimestre 2</option>
                        <option value="trimestre3" {{ $periode == 'trimestre3' ? 'selected' : '' }}>Trimestre 3</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau récapitulatif des notes par matière -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>
                Récapitulatif des notes par matière
            </h5>
        </div>
        <div class="card-body">
            @if(count($moyennesParMatiere) > 0)
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Matière</th>
                            <th class="text-center">Coefficient</th>
                            @if(!$eleve->classe->isPrimaire())
                            <th class="text-center">Note Cours</th>
                            @endif
                            <th class="text-center">Note Composition</th>
                            <th class="text-center">Note Finale</th>
                            @if(!$eleve->classe->isPrimaire())
                            <th class="text-center">Points</th>
                            @endif
                            <th class="text-center">Mention</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($moyennesParMatiere as $matiereId => $data)
                        @php
                            $noteCours = $data['notes']->where('note_cours', '!=', null)->avg('note_cours') ?? 0;
                            $noteComposition = $data['notes']->where('note_composition', '!=', null)->avg('note_composition') ?? 0;
                            $appreciationMatiere = $eleve->classe->getAppreciation($data['moyenne']);
                        @endphp
                        <tr id="matiere-{{ $matiereId }}">
                            <td><strong>{{ $data['matiere']->nom }}</strong></td>
                            <td class="text-center">{{ $data['coefficient'] }}</td>
                            @if(!$eleve->classe->isPrimaire())
                            <td class="text-center">
                                @if($noteCours > 0)
                                    <span class="badge bg-info">{{ number_format($noteCours, 2) }}/{{ $eleve->classe->note_max }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            @endif
                            <td class="text-center">
                                @if($noteComposition > 0)
                                    <span class="badge bg-info">{{ number_format($noteComposition, 2) }}/{{ $eleve->classe->note_max }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary fs-6">{{ number_format($data['moyenne'], 2) }}/{{ $eleve->classe->note_max }}</span>
                            </td>
                            @if(!$eleve->classe->isPrimaire())
                            <td class="text-center"><strong>{{ number_format($data['points'], 2) }}</strong></td>
                            @endif
                            <td class="text-center">
                                <span class="text-{{ $appreciationMatiere['color'] }}">
                                    @if($appreciationMatiere['label'] == 'Excellent')
                                        <i class="fas fa-star me-1"></i>
                                    @elseif($appreciationMatiere['label'] == 'Très bien')
                                        <i class="fas fa-thumbs-up me-1"></i>
                                    @elseif($appreciationMatiere['label'] == 'Bien')
                                        <i class="fas fa-check me-1"></i>
                                    @elseif($appreciationMatiere['label'] == 'Assez bien')
                                        <i class="fas fa-exclamation me-1"></i>
                                    @elseif($appreciationMatiere['label'] == 'Passable')
                                        <i class="fas fa-minus me-1"></i>
                                    @else
                                        <i class="fas fa-times me-1"></i>
                                    @endif
                                    {{ $appreciationMatiere['label'] }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if($data['notes']->count() > 0 && auth()->user()->hasPermission('notes.edit'))
                                        @php
                                            $premiereNote = $data['notes']->first();
                                        @endphp
                                        <a href="{{ route('notes.edit', $premiereNote->id) }}" 
                                           class="btn btn-outline-primary" 
                                           title="Modifier les notes de {{ $data['matiere']->nom }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    @if($data['notes']->count() > 0 && auth()->user()->hasPermission('notes.delete'))
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                title="Supprimer toutes les notes de {{ $data['matiere']->nom }}"
                                                onclick="confirmDeleteMatiere({{ $eleve->id }}, {{ $matiereId }}, '{{ $data['matiere']->nom }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                    <button type="button" 
                                            class="btn btn-outline-info" 
                                            title="Voir les détails des notes"
                                            onclick="toggleMatiereDetails({{ $matiereId }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4"><strong>MOYENNE GÉNÉRALE</strong></th>
                            <th class="text-center">
                                <span class="badge bg-primary fs-5">
                                    {{ number_format($moyenneGenerale, 2) }}/{{ $eleve->classe->note_max }}
                                </span>
                            </th>
                            <th class="text-center">
                                <strong>{{ number_format(collect($moyennesParMatiere)->sum('points'), 2) }}</strong>
                            </th>
                            <th class="text-center">
                                <span class="text-{{ $appreciationGenerale['color'] }}">
                                    @if($appreciationGenerale['label'] == 'Excellent')
                                        <i class="fas fa-star me-1"></i>
                                    @elseif($appreciationGenerale['label'] == 'Très bien')
                                        <i class="fas fa-thumbs-up me-1"></i>
                                    @elseif($appreciationGenerale['label'] == 'Bien')
                                        <i class="fas fa-check me-1"></i>
                                    @elseif($appreciationGenerale['label'] == 'Assez bien')
                                        <i class="fas fa-exclamation me-1"></i>
                                    @elseif($appreciationGenerale['label'] == 'Passable')
                                        <i class="fas fa-minus me-1"></i>
                                    @else
                                        <i class="fas fa-times me-1"></i>
                                    @endif
                                    {{ $appreciationGenerale['label'] }}
                                </span>
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="text-center text-muted py-5">
                <i class="fas fa-graduation-cap fa-3x mb-3"></i>
                <h5>Aucune note trouvée</h5>
                <p>Il n'y a pas encore de notes saisies pour cette période.</p>
                @if(auth()->user()->hasPermission('notes.create'))
                    <a href="{{ route('notes.saisir', $eleve->classe_id) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Saisir des notes
                    </a>
                @endif
            </div>
            @endif
        </div>
    </div>

    <!-- Détails des notes par matière -->
    @foreach($moyennesParMatiere as $matiereId => $data)
    <div class="card mb-4 matiere-details" id="details-matiere-{{ $matiereId }}" style="display: none;">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-book me-2"></i>
                {{ $data['matiere']->nom }} - Coeff: {{ $data['coefficient'] }}
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-secondary">
                        <tr>
                            <th>Date</th>
                            <th class="text-center">Type</th>
                            <th class="text-center">Coeff.</th>
                            @if(!$eleve->classe->isPrimaire())
                            <th class="text-center">Note Cours</th>
                            @endif
                            <th class="text-center">Note Composition</th>
                            <th class="text-center">Note Finale</th>
                            <th class="text-center">Enseignant</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['notes'] as $note)
                        @php
                            $noteFinale = $note->calculerNoteFinale();
                            $appreciationFinale = $noteFinale ? $eleve->classe->getAppreciation($noteFinale) : null;
                        @endphp
                        <tr>
                            <td>{{ $note->date_evaluation ? \Carbon\Carbon::parse($note->date_evaluation)->format('d/m/Y') : '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ ucfirst($note->type_evaluation ?? 'N/A') }}</span>
                            </td>
                            <td class="text-center"><strong>{{ $note->coefficient ?? $data['matiere']->coefficient ?? 1 }}</strong></td>
                            @if(!$eleve->classe->isPrimaire())
                            <td class="text-center">
                                @if($note->note_cours)
                                    <span class="badge bg-info">{{ number_format($note->note_cours, 2) }}/{{ $eleve->classe->note_max }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            @endif
                            <td class="text-center">
                                @if($note->note_composition)
                                    <span class="badge bg-info">{{ number_format($note->note_composition, 2) }}/{{ $eleve->classe->note_max }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($noteFinale)
                                    <span class="badge bg-primary">{{ number_format($noteFinale, 2) }}/{{ $eleve->classe->note_max }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $note->enseignant->utilisateur->name ?? 'N/A' }}
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if(auth()->user()->hasPermission('notes.edit'))
                                        <a href="{{ route('notes.edit', $note->id) }}" 
                                           class="btn btn-outline-primary" 
                                           title="Modifier cette note">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('notes.delete'))
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                title="Supprimer cette note"
                                                onclick="confirmDeleteNote({{ $note->id }}, '{{ $data['matiere']->nom }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
</div>

@push('scripts')
<script>
// Fonction pour afficher/masquer les détails d'une matière
function toggleMatiereDetails(matiereId) {
    const detailsElement = document.getElementById('details-matiere-' + matiereId);
    if (detailsElement) {
        if (detailsElement.style.display === 'none') {
            detailsElement.style.display = 'block';
            // Scroll vers les détails
            detailsElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            detailsElement.style.display = 'none';
        }
    }
}

// Fonction pour confirmer la suppression d'une note
function confirmDeleteNote(noteId, matiereNom) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette note de ' + matiereNom + ' ?\n\nCette action est irréversible.')) {
        // Créer un formulaire de suppression
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("notes.destroy", ":noteId") }}'.replace(':noteId', noteId);
        
        // Ajouter le token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Ajouter la méthode DELETE
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        // Soumettre le formulaire
        document.body.appendChild(form);
        form.submit();
    }
}

// Fonction pour confirmer la suppression de toutes les notes d'une matière
function confirmDeleteMatiere(eleveId, matiereId, matiereNom) {
    if (confirm('Êtes-vous sûr de vouloir supprimer TOUTES les notes de la matière ' + matiereNom + ' ?\n\nCette action supprimera toutes les notes de cette matière pour cette période et est irréversible.')) {
        // Créer un formulaire de suppression
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("notes.supprimer.matiere", [":eleveId", ":matiereId"]) }}'.replace(':eleveId', eleveId).replace(':matiereId', matiereId);
        
        // Ajouter le token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Ajouter la période actuelle
        const periodeField = document.createElement('input');
        periodeField.type = 'hidden';
        periodeField.name = 'periode';
        periodeField.value = '{{ $periode }}';
        form.appendChild(periodeField);
        
        // Ajouter la méthode DELETE
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        // Soumettre le formulaire
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection

