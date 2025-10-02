<div class="row">
    <!-- Photo et informations de base -->
    <div class="col-md-4 text-center">
        <x-profile-image 
            :photo-path="$eleve->utilisateur->photo_profil ?? null"
            :name="($eleve->utilisateur->prenom ?? '') . ' ' . ($eleve->utilisateur->nom ?? '')"
            size="lg" 
            class="img-thumbnail mb-3" />
        
        <h4 class="mb-1">
            {{ strtoupper($eleve->utilisateur->nom ?? '') }} {{ ucfirst($eleve->utilisateur->prenom ?? '') }}
        </h4>
        <p class="text-muted mb-2">{{ $eleve->numero_etudiant ?? 'N/A' }}</p>
        <p class="text-muted mb-3">{{ $eleve->classe->nom ?? 'Classe non assignée' }}</p>
        
        <!-- Statut -->
        <span class="badge bg-{{ $eleve->utilisateur->sexe == 'M' ? 'primary' : 'pink' }} mb-2">
            {{ $eleve->utilisateur->sexe == 'M' ? 'Masculin' : 'Féminin' }}
        </span>
    </div>

    <!-- Informations détaillées -->
    <div class="col-md-8">
        <div class="row">
            <!-- Statistiques -->
            <div class="col-md-6">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-primary">
                            <i class="fas fa-chart-line me-2"></i>Statistiques
                        </h6>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="border-end">
                                    <h5 class="text-success mb-1">{{ $statistiques['notes_count'] }}</h5>
                                    <small class="text-muted">Notes</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border-end">
                                    <h5 class="text-warning mb-1">{{ $statistiques['absences_count'] }}</h5>
                                    <small class="text-muted">Absences</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <h5 class="text-info mb-1">{{ number_format($statistiques['moyenne_generale'], 2) }}</h5>
                                <small class="text-muted">Moyenne</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations de contact -->
            <div class="col-md-6">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-primary">
                            <i class="fas fa-phone me-2"></i>Contact
                        </h6>
                        <p class="mb-1">
                            <i class="fas fa-envelope me-2 text-muted"></i>
                            {{ $eleve->utilisateur->email ?? 'Non renseigné' }}
                        </p>
                        <p class="mb-1">
                            <i class="fas fa-phone me-2 text-muted"></i>
                            {{ $eleve->utilisateur->telephone ?? 'Non renseigné' }}
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                            {{ $eleve->utilisateur->adresse ?? 'Non renseignée' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dernières notes -->
        @if($eleve->notes->count() > 0)
        <div class="mt-3">
            <h6 class="text-primary">
                <i class="fas fa-graduation-cap me-2"></i>Dernières notes
            </h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Matière</th>
                            <th>Note</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($eleve->notes->sortByDesc('created_at')->take(5) as $note)
                        <tr>
                            <td>{{ $note->matiere->nom ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $note->note >= 10 ? 'success' : ($note->note >= 8 ? 'warning' : 'danger') }}">
                                    {{ $note->note }}/20
                                </span>
                            </td>
                            <td>{{ $note->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Dernières absences -->
        @if($eleve->absences->count() > 0)
        <div class="mt-3">
            <h6 class="text-primary">
                <i class="fas fa-user-times me-2"></i>Dernières absences
            </h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Motif</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($eleve->absences->sortByDesc('date_absence')->take(3) as $absence)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($absence->date_absence)->format('d/m/Y') }}</td>
                            <td>{{ $absence->motif ?? 'Non renseigné' }}</td>
                            <td>
                                <span class="badge bg-{{ $absence->justifie ? 'success' : 'warning' }}">
                                    {{ $absence->justifie ? 'Justifiée' : 'Non justifiée' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Actions rapides -->
<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex gap-2 justify-content-center">
            <a href="{{ route('notes.eleve', $eleve->id) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-graduation-cap me-1"></i>Voir toutes les notes
            </a>
            <a href="{{ route('absences.eleve', $eleve->id) }}" class="btn btn-outline-warning btn-sm">
                <i class="fas fa-user-times me-1"></i>Voir les absences
            </a>
            <button type="button" class="btn btn-outline-success btn-sm" onclick="saisirNote({{ $eleve->id }})">
                <i class="fas fa-edit me-1"></i>Saisir une note
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="marquerAbsence({{ $eleve->id }})">
                <i class="fas fa-user-times me-1"></i>Marquer absence
            </button>
        </div>
    </div>
</div>
