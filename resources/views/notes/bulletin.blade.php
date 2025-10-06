@extends('layouts.app')

@section('title', 'Bulletin de Notes - ' . $eleve->nom_complet)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/print-bulletin.css') }}">
@endsection

@section('content')
<div class="bulletin-container">
    <!-- Boutons d'action (masqués à l'impression) -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom no-print">
        <h1 class="h2">
            <i class="fas fa-file-alt me-2"></i>
            Bulletin de Notes - {{ $eleve->nom_complet }}
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('notes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
            <button class="btn btn-primary ms-2" onclick="window.print()">
                <i class="fas fa-print me-1"></i>
                Imprimer
            </button>
        </div>
    </div>

    <!-- En-tête du bulletin pour l'impression -->
    <div class="bulletin-header d-none d-print-block">
        <div class="row">
            <div class="col-6">
                <h1>BULLETIN DE NOTES</h1>
                <h2>{{ $eleve->classe->nom }}</h2>
            </div>
            <div class="col-6 text-end">
                <h1>Année Scolaire {{ date('Y') }}-{{ date('Y') + 1 }}</h1>
                <h2>{{ ucfirst(str_replace('trimestre', 'Trimestre ', $periode)) }}</h2>
            </div>
        </div>
    </div>

    <!-- Informations de l'élève pour l'impression -->
    <div class="student-info d-none d-print-block">
        <div class="row">
            <div class="col-6">
                <p><strong>{{ $eleve->nom_complet }}</strong></p>
                <p><strong>Numéro:</strong> {{ $eleve->numero_etudiant }}</p>
                <p><strong>Date de naissance:</strong> {{ $eleve->utilisateur->date_naissance ? \Carbon\Carbon::parse($eleve->utilisateur->date_naissance)->format('d/m/Y') : 'Non renseignée' }}</p>
            </div>
            <div class="col-6">
                <div class="summary-bar">
                    <div class="rank">Rang: {{ $rang }}/{{ $eleve->classe->eleves->count() }}</div>
                    <div class="average">Moyenne générale: {{ number_format($moyenneGenerale, 2) }}/20</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des notes (version ultra-compacte pour A4) -->
    <div class="d-none d-print-block">
        @if(count($moyennesParMatiere) > 0)
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th style="width: 30%;">Matière</th>
                    <th style="width: 8%;">Coef</th>
                    <th style="width: 10%;">Cours</th>
                    <th style="width: 10%;">Comp</th>
                    <th style="width: 12%;">Finale</th>
                    <th style="width: 8%;">Pts</th>
                    <th style="width: 22%;">Appréciation</th>
                </tr>
            </thead>
            <tbody>
                @foreach($moyennesParMatiere as $matiereId => $data)
                <tr>
                    <td><strong>{{ $data['matiere']->nom }}</strong></td>
                    <td class="text-center">{{ $data['coefficient'] }}</td>
                    <td class="text-center">
                        @if($data['notes']->where('note_cours', '!=', null)->count() > 0)
                            {{ number_format($data['notes']->where('note_cours', '!=', null)->avg('note_cours'), 1) }}/20
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @if($data['notes']->where('note_composition', '!=', null)->count() > 0)
                            {{ number_format($data['notes']->where('note_composition', '!=', null)->avg('note_composition'), 1) }}/20
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @php
                            $appreciation = $eleve->classe->getAppreciation($data['moyenne']);
                        @endphp
                        <span class="badge bg-{{ $appreciation['color'] }}">
                            {{ number_format($data['moyenne'], 1) }}/{{ $eleve->classe->note_max }}
                        </span>
                    </td>
                    <td class="text-center">{{ number_format($data['points'], 1) }}</td>
                    <td>
                        <span class="text-{{ $appreciation['color'] }}">
                            @if($appreciation['label'] == 'Excellent')
                                <i class="fas fa-star me-1"></i>
                            @elseif($appreciation['label'] == 'Très bien')
                                <i class="fas fa-thumbs-up me-1"></i>
                            @elseif($appreciation['label'] == 'Bien')
                                <i class="fas fa-check me-1"></i>
                            @elseif($appreciation['label'] == 'Assez bien')
                                <i class="fas fa-exclamation me-1"></i>
                            @elseif($appreciation['label'] == 'Passable')
                                <i class="fas fa-minus me-1"></i>
                            @else
                                <i class="fas fa-times me-1"></i>
                            @endif
                            {{ $appreciation['label'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="4"><strong>MOYENNE GÉNÉRALE</strong></th>
                    <th class="text-center">
                        @php
                            $appreciationGenerale = $eleve->classe->getAppreciation($moyenneGenerale);
                        @endphp
                        <span class="final-grade">{{ number_format($moyenneGenerale, 1) }}/{{ $eleve->classe->note_max }}</span>
                    </th>
                    <th class="text-center">
                        <strong>{{ number_format(collect($moyennesParMatiere)->sum('points'), 1) }}</strong>
                    </th>
                    <th class="text-center">
                        <span class="appreciation text-{{ $appreciationGenerale['color'] }}">
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
                </tr>
            </tfoot>
        </table>
        @endif
    </div>

    <!-- Observations et signatures ultra-compactes -->
    <div class="d-none d-print-block">
        <div class="row">
            <div class="col-6">
                <div class="observations">
                    <h6><strong>Observations:</strong></h6>
                    <p class="text-danger">
                        @if($moyenneGenerale < 10)
                            Résultats insuffisants.
                        @elseif($moyenneGenerale < 12)
                            Résultats moyens.
                        @else
                            Résultats satisfaisants.
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-6">
                <div class="signatures">
                    <h6><strong>Signatures:</strong></h6>
                    <div class="row">
                        <div class="col-6">
                            <p><strong>Directeur</strong></p>
                            <div class="signature-line"></div>
                            <p>Date: ___</p>
                        </div>
                        <div class="col-6">
                            <p><strong>Parent</strong></p>
                            <div class="signature-line"></div>
                            <p>Date: ___</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Version écran (masquée à l'impression) -->
    <div class="d-print-none">
        <!-- Informations de l'élève -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informations de l'élève</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nom complet :</strong> {{ $eleve->nom_complet }}</p>
                        <p><strong>Numéro étudiant :</strong> {{ $eleve->numero_etudiant }}</p>
                        <p><strong>Classe :</strong> {{ $eleve->classe->nom }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Période :</strong> {{ ucfirst(str_replace('trimestre', 'Trimestre ', $periode)) }}</p>
                        <p><strong>Année scolaire :</strong> {{ date('Y') }}-{{ date('Y') + 1 }}</p>
                        <p><strong>Rang :</strong> {{ $rang }}ème sur {{ $eleve->classe->eleves->count() }} élèves</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes par matière -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Notes par matière</h5>
            </div>
            <div class="card-body">
                @if(count($moyennesParMatiere) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Matière</th>
                                <th>Coefficient</th>
                                <th>Note Cours</th>
                                <th>Note Composition</th>
                                <th>Note Finale</th>
                                <th>Points</th>
                                <th>Appréciation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($moyennesParMatiere as $matiereId => $data)
                            <tr>
                                <td><strong>{{ $data['matiere']->nom }}</strong></td>
                                <td class="text-center">{{ $data['coefficient'] }}</td>
                                <td class="text-center">
                                    @if($data['notes']->where('note_cours', '!=', null)->count() > 0)
                                        {{ number_format($data['notes']->where('note_cours', '!=', null)->avg('note_cours'), 2) }}/20
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($data['notes']->where('note_composition', '!=', null)->count() > 0)
                                        {{ number_format($data['notes']->where('note_composition', '!=', null)->avg('note_composition'), 2) }}/20
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $appreciationMatiere = $eleve->classe->getAppreciation($data['moyenne']);
                                    @endphp
                                    <span class="badge bg-{{ $appreciationMatiere['color'] }} fs-6">
                                        {{ number_format($data['moyenne'], 2) }}/{{ $eleve->classe->note_max }}
                                    </span>
                                </td>
                                <td class="text-center">{{ number_format($data['points'], 2) }}</td>
                                <td>
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
                                        @if($data['notes']->count() > 0)
                                            @php
                                                $premiereNote = $data['notes']->first();
                                            @endphp
                                            <a href="{{ route('notes.edit', $premiereNote->id) }}" 
                                               class="btn btn-outline-primary btn-sm" 
                                               title="Modifier les notes de {{ $data['matiere']->nom }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        <button type="button" 
                                                class="btn btn-outline-info btn-sm" 
                                                title="Voir les détails des notes"
                                                onclick="showNoteDetails({{ $matiereId }}, '{{ $data['matiere']->nom }}')">
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
                                    <span class="badge bg-{{ $appreciationGenerale['color'] }} fs-5">
                                        {{ number_format($moyenneGenerale, 2) }}/{{ $eleve->classe->note_max }}
                                    </span>
                                </th>
                                <th class="text-center">
                                    <strong>{{ number_format(collect($moyennesParMatiere)->sum('points'), 2) }}</strong>
                                </th>
                                <th>
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
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                    <h5>Aucune note trouvée</h5>
                    <p>Il n'y a pas encore de notes saisies pour cette période.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Détails des notes par matière -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Détails des notes par matière</h5>
            </div>
            <div class="card-body">
                @if(count($moyennesParMatiere) > 0)
                    @foreach($moyennesParMatiere as $matiereId => $data)
                        <div class="card mb-3" id="matiere-{{ $matiereId }}">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-book me-2"></i>
                                    {{ $data['matiere']->nom }}
                                    <span class="badge bg-primary ms-2">Coeff: {{ $data['coefficient'] }}</span>
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($data['notes']->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Type</th>
                                                    <th>Note Cours</th>
                                                    <th>Note Composition</th>
                                                    <th>Note Finale</th>
                                                    <th>Enseignant</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($data['notes'] as $note)
                                                <tr>
                                                    <td>{{ $note->date_evaluation ? $note->date_evaluation->format('d/m/Y') : '-' }}</td>
                                                    <td>
                                                        <span class="badge bg-info">{{ ucfirst($note->type_evaluation) }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($note->note_cours !== null)
                                                            @php
                                                                $appreciationCours = $note->eleve->classe->getAppreciation($note->note_cours);
                                                            @endphp
                                                            <span class="badge bg-{{ $appreciationCours['color'] }}">
                                                                {{ number_format($note->note_cours, 2) }}/{{ $note->eleve->classe->note_max }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($note->note_composition !== null)
                                                            @php
                                                                $appreciationComposition = $note->eleve->classe->getAppreciation($note->note_composition);
                                                            @endphp
                                                            <span class="badge bg-{{ $appreciationComposition['color'] }}">
                                                                {{ number_format($note->note_composition, 2) }}/{{ $note->eleve->classe->note_max }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @php
                                                            $noteFinale = $note->calculerNoteFinale();
                                                        @endphp
                                                        @if($noteFinale !== null)
                                                            @php
                                                                $appreciationFinale = $note->eleve->classe->getAppreciation($noteFinale);
                                                            @endphp
                                                            <span class="badge bg-{{ $appreciationFinale['color'] }}">
                                                                {{ number_format($noteFinale, 2) }}/{{ $note->eleve->classe->note_max }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $note->enseignant->utilisateur->name ?? 'N/A' }}
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('notes.edit', $note->id) }}" 
                                                               class="btn btn-outline-primary btn-sm" 
                                                               title="Modifier cette note">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button" 
                                                                    class="btn btn-outline-danger btn-sm" 
                                                                    title="Supprimer cette note"
                                                                    onclick="confirmDeleteNote({{ $note->id }}, '{{ $data['matiere']->nom }}')">
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
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                        <p>Aucune note trouvée pour cette matière.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                        <h5>Aucune note trouvée</h5>
                        <p>Il n'y a pas encore de notes saisies pour cette période.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .bulletin-container {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .table {
        page-break-inside: avoid !important;
    }
    
    .row {
        margin: 0 !important;
    }
    
    .col-6, .col-12 {
        padding: 0 2px !important;
    }
}

.badge.fs-6 {
    font-size: 0.875rem !important;
}

.badge.fs-5 {
    font-size: 1rem !important;
}
</style>
@endpush

@push('scripts')
<script>
// Fonction pour afficher les détails des notes d'une matière
function showNoteDetails(matiereId, matiereNom) {
    const element = document.getElementById('matiere-' + matiereId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
        element.style.border = '2px solid #007bff';
        setTimeout(() => {
            element.style.border = '';
        }, 3000);
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

// Fonction pour confirmer la suppression d'une note (depuis le formulaire d'édition)
function confirmDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette note ?\n\nCette action est irréversible.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endpush

@endsection
