@extends('layouts.app')

@section('title', 'Bulletin de Notes - ' . $eleve->nom_complet)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
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
                            <span class="badge 
                                @if($data['moyenne'] >= 16) bg-success
                                @elseif($data['moyenne'] >= 14) bg-primary
                                @elseif($data['moyenne'] >= 12) bg-info
                                @elseif($data['moyenne'] >= 10) bg-warning
                                @else bg-danger
                                @endif fs-6">
                                {{ number_format($data['moyenne'], 2) }}/20
                            </span>
                        </td>
                        <td class="text-center">{{ number_format($data['points'], 2) }}</td>
                        <td>
                            @if($data['moyenne'] >= 16)
                                <span class="text-success"><i class="fas fa-star me-1"></i>Excellent</span>
                            @elseif($data['moyenne'] >= 14)
                                <span class="text-primary"><i class="fas fa-thumbs-up me-1"></i>Très bien</span>
                            @elseif($data['moyenne'] >= 12)
                                <span class="text-info"><i class="fas fa-check me-1"></i>Bien</span>
                            @elseif($data['moyenne'] >= 10)
                                <span class="text-warning"><i class="fas fa-exclamation me-1"></i>Assez bien</span>
                            @else
                                <span class="text-danger"><i class="fas fa-times me-1"></i>Insuffisant</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="4"><strong>MOYENNE GÉNÉRALE</strong></th>
                        <th class="text-center">
                            <span class="badge 
                                @if($moyenneGenerale >= 16) bg-success
                                @elseif($moyenneGenerale >= 14) bg-primary
                                @elseif($moyenneGenerale >= 12) bg-info
                                @elseif($moyenneGenerale >= 10) bg-warning
                                @else bg-danger
                                @endif fs-5">
                                {{ number_format($moyenneGenerale, 2) }}/20
                            </span>
                        </th>
                        <th class="text-center">
                            <strong>{{ number_format(collect($moyennesParMatiere)->sum('points'), 2) }}</strong>
                        </th>
                        <th>
                            @if($moyenneGenerale >= 16)
                                <span class="text-success"><i class="fas fa-star me-1"></i>Excellent</span>
                            @elseif($moyenneGenerale >= 14)
                                <span class="text-primary"><i class="fas fa-thumbs-up me-1"></i>Très bien</span>
                            @elseif($moyenneGenerale >= 12)
                                <span class="text-info"><i class="fas fa-check me-1"></i>Bien</span>
                            @elseif($moyenneGenerale >= 10)
                                <span class="text-warning"><i class="fas fa-exclamation me-1"></i>Assez bien</span>
                            @else
                                <span class="text-danger"><i class="fas fa-times me-1"></i>Insuffisant</span>
                            @endif
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

<!-- Détail des notes -->
@if(count($moyennesParMatiere) > 0)
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Détail des notes</h5>
    </div>
    <div class="card-body">
        @foreach($moyennesParMatiere as $matiereId => $data)
        <div class="mb-4">
            <h6 class="text-primary">{{ $data['matiere']->nom }}</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Note Cours</th>
                            <th>Note Composition</th>
                            <th>Note Finale</th>
                            <th>Coefficient</th>
                            <th>Commentaire</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['notes'] as $note)
                        <tr>
                            <td>{{ $note->date_evaluation->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($note->type_evaluation) }}</span>
                            </td>
                            <td class="text-center">
                                @if($note->note_cours)
                                    {{ number_format($note->note_cours, 2) }}/20
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($note->note_composition)
                                    {{ number_format($note->note_composition, 2) }}/20
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($note->note_finale)
                                    <strong>{{ number_format($note->note_finale, 2) }}/20</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $note->coefficient }}</td>
                            <td>
                                @if($note->commentaire)
                                    <small>{{ $note->commentaire }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('notes.edit', $note->id) }}" 
                                       class="btn btn-outline-primary btn-sm" 
                                       title="Modifier la note">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-danger btn-sm" 
                                            onclick="confirmDelete({{ $note->id }})"
                                            title="Supprimer la note">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@push('styles')
<style>
@media print {
    .btn-toolbar, .border-bottom {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        page-break-inside: avoid;
    }
    
    .table {
        font-size: 0.875rem;
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
function confirmDelete(noteId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette note ? Cette action est irréversible.')) {
        // Créer un formulaire de suppression
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/notes/${noteId}`;
        
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
</script>
@endpush
@endsection
