@extends('layouts.app')

@section('title', 'Notes de ' . $eleve->utilisateur->nom . ' ' . $eleve->utilisateur->prenom)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Notes de {{ $eleve->utilisateur->nom }} {{ $eleve->utilisateur->prenom }}
                    </h1>
                    <p class="text-muted mb-0">{{ $eleve->classe->nom ?? 'Classe non assignée' }}</p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="{{ route('parent.notes.export', $eleve) }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-download me-1"></i>
                        <span class="d-none d-sm-inline">Exporter</span>
                    </a>
                    <a href="{{ route('parent.notes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        <span class="d-none d-sm-inline">Retour</span>
                    </a>
                </div>
            </div>

            <!-- Statistiques de l'enfant -->
            <div class="row mb-4 g-3">
                <div class="col-12 col-sm-6 col-md-2">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['total_notes'] }}</h4>
                            <p class="mb-0">Total Notes</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4>{{ number_format($stats['moyenne_generale'], 2) }}</h4>
                            <p class="mb-0">Moyenne</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['notes_sup_10'] }}</h4>
                            <p class="mb-0">Notes ≥ 10</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['notes_inf_10'] }}</h4>
                            <p class="mb-0">Notes < 10</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4>{{ number_format($stats['meilleure_note'], 2) }}</h4>
                            <p class="mb-0">Meilleure</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h4>{{ number_format($stats['moins_bonne_note'], 2) }}</h4>
                            <p class="mb-0">Moins bonne</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes par matière -->
            <div class="row g-3">
                @foreach($notesParMatiere as $nomMatiere => $notesMatiere)
                    <div class="col-12 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-book me-2"></i>
                                    {{ $nomMatiere }}
                                    <span class="badge bg-primary ms-2">
                                        Moyenne: {{ number_format($notesMatiere->avg('note_sur'), 2) }}/20
                                    </span>
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Note</th>
                                                <th>Enseignant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($notesMatiere as $note)
                                                <tr>
                                                    <td>{{ $note->date_evaluation->format('d/m') }}</td>
                                                    <td>
                                                        <span class="badge bg-info">{{ ucfirst($note->type_evaluation) }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $note->note_sur >= 10 ? 'bg-success' : 'bg-danger' }}">
                                                            {{ number_format($note->note_sur, 2) }}/20
                                                        </span>
                                                    </td>
                                                    <td>{{ $note->enseignant->utilisateur->nom ?? 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Actions rapides -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Actions Rapides</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 col-sm-6 col-md-3">
                                    <a href="{{ route('parent.notes.bulletin', ['eleve' => $eleve, 'periode' => 'trimestre_1']) }}" class="btn btn-primary w-100">
                                        <i class="fas fa-file-alt me-2"></i>
                                        Bulletin T1
                                    </a>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <a href="{{ route('parent.notes.bulletin', ['eleve' => $eleve, 'periode' => 'trimestre_2']) }}" class="btn btn-primary w-100">
                                        <i class="fas fa-file-alt me-2"></i>
                                        Bulletin T2
                                    </a>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <a href="{{ route('parent.notes.bulletin', ['eleve' => $eleve, 'periode' => 'trimestre_3']) }}" class="btn btn-primary w-100">
                                        <i class="fas fa-file-alt me-2"></i>
                                        Bulletin T3
                                    </a>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <a href="{{ route('parent.notes.export', $eleve) }}" class="btn btn-success w-100">
                                        <i class="fas fa-download me-2"></i>
                                        Exporter
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
