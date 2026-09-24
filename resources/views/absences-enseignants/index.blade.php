@extends('layouts.app')

@section('title', 'Absences des enseignants')

@push('styles')
<style>
    .ae-row-enseignant { cursor: pointer; }
    .ae-row-enseignant:hover { background: #f8fafc; }
    .ae-avatar {
        width: 40px; height: 40px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        background: #e7f1ff; color: #0d6efd; font-weight: 700; overflow: hidden; flex-shrink: 0;
    }
    .ae-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .ae-stats-dropdown { position: relative; z-index: 30; }
    .ae-stats-dropdown .dropdown-menu {
        position: absolute !important;
        inset: auto 0 auto auto !important;
        display: none;
        min-width: 220px;
        margin-top: .35rem;
        z-index: 1050 !important;
    }
    .ae-stats-dropdown .dropdown-menu.show {
        display: block;
    }
</style>
@endpush

@section('content')
@php use Illuminate\Support\Facades\Storage; @endphp

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom flex-wrap gap-3">
    <h1 class="h4 mb-0">
        <i class="fas fa-user-clock me-2"></i>
        Absences des enseignants
        <small class="text-muted fw-normal">— {{ $moisLabel }}</small>
    </h1>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <form method="GET" action="{{ route('absences-enseignants.index') }}" class="d-flex align-items-center gap-2">
            @if($datePrecedente)
                <a href="{{ route('absences-enseignants.index', ['date' => $datePrecedente]) }}" class="btn btn-outline-secondary" title="Jour précédent">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @else
                <button type="button" class="btn btn-outline-secondary" disabled><i class="fas fa-chevron-left"></i></button>
            @endif
            <label class="form-label mb-0 small text-muted" for="filtre_date">Date</label>
            <input type="date" name="date" id="filtre_date" class="form-control" style="width:auto"
                   value="{{ $dateSelectionnee }}" min="{{ $dateMin }}" max="{{ $aujourdhui }}"
                   onchange="this.form.submit()">
            @if($dateSuivante)
                <a href="{{ route('absences-enseignants.index', ['date' => $dateSuivante]) }}" class="btn btn-outline-secondary" title="Jour suivant">
                    <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <button type="button" class="btn btn-outline-secondary" disabled><i class="fas fa-chevron-right"></i></button>
            @endif
            @if($dateSelectionnee !== $aujourdhui)
                <a href="{{ route('absences-enseignants.index') }}" class="btn btn-outline-primary">Aujourd'hui</a>
            @endif
        </form>
        <div class="dropdown ae-stats-dropdown">
        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
            <i class="fas fa-chart-bar me-1"></i>
            Statistiques
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <a class="dropdown-item" href="{{ route('absences-enseignants.statistiques', ['vue' => 'mois']) }}">
                    Mois en cours
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('absences-enseignants.statistiques', ['vue' => 'mois', 'mois' => now()->copy()->subMonth()->format('Y-m')]) }}">
                    Mois précédent
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item" href="{{ route('absences-enseignants.statistiques', ['vue' => 'annee']) }}">
                    Année
                </a>
            </li>
        </ul>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Enseignant</th>
                        <th>Spécialité</th>
                        <th>Heures du mois <small class="fw-normal text-muted">(emploi du temps)</small></th>
                        <th>Heures d'absence</th>
                        <th>Statut @if($dateSelectionnee !== $aujourdhui) du {{ $dateLabel }} @else du jour @endif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enseignants as $enseignant)
                        @php
                            $user = $enseignant->utilisateur;
                            $photo = $user->photo_profil ?? null;
                            $initiales = $user ? strtoupper(substr($user->prenom ?? '', 0, 1) . substr($user->nom ?? '', 0, 1)) : '?';
                            $absencesJour = $absencesDuJour->get($enseignant->id, collect());
                            $absencesMois = $absencesDuMois->get($enseignant->id, collect());
                            $minutesAbsence = $absencesMois->sum(fn ($absence) => $absence->dureeMinutes());
                            $minutesPrevues = $heuresPrevuesMois[$enseignant->id] ?? $heuresPrevuesMois[(int) $enseignant->id] ?? 0;
                            $minutesRestantes = max(0, $minutesPrevues - $minutesAbsence);
                            $coursJour = $coursDuJour[$enseignant->id] ?? $coursDuJour[(string) $enseignant->id] ?? [];
                            $absentJournee = count($coursJour) > 0 && $absencesJour->count() >= count($coursJour);
                        @endphp
                        <tr class="ae-row-enseignant"
                            data-id="{{ $enseignant->id }}"
                            data-nom="{{ $enseignant->nom_complet }}"
                            role="button"
                            tabindex="0">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="ae-avatar">
                                        @if($photo && Storage::disk('public')->exists($photo))
                                            <img src="{{ asset('storage/' . $photo) }}" alt="">
                                        @else
                                            {{ $initiales }}
                                        @endif
                                    </div>
                                    <strong>{{ $enseignant->nom_complet }}</strong>
                                </div>
                            </td>
                            <td class="text-muted">{{ $enseignant->specialite ?: '—' }}</td>
                            <td>
                                @if($minutesPrevues <= 0)
                                    <span class="text-muted">0h</span>
                                @else
                                    <span class="fw-semibold">{{ \App\Models\AbsenceEnseignant::formatDureeMinutes($minutesRestantes) }}</span>
                                    @if($minutesAbsence > 0)
                                        <small class="text-muted">/ {{ \App\Models\AbsenceEnseignant::formatDureeMinutes($minutesPrevues) }}</small>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($absencesMois->isEmpty())
                                    <span class="text-muted">0h</span>
                                @else
                                    <span class="badge bg-danger">{{ \App\Models\AbsenceEnseignant::formatDureeMinutes($minutesAbsence) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($absencesJour->isEmpty())
                                    <span class="badge bg-success">Présent</span>
                                @elseif($absentJournee)
                                    <span class="badge bg-danger">Absent</span>
                                @else
                                    <span class="badge bg-warning text-dark">Absence partielle</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Aucun enseignant.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('modals')
<div class="modal fade" id="modalAbsenceEnseignant" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('absences-enseignants.store') }}" id="formAbsenceEnseignant">
                @csrf
                <input type="hidden" name="enseignant_id" id="absence_enseignant_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="absence_enseignant_nom">Enseignant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="absence_date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="absence_date" class="form-control"
                               value="{{ $dateSelectionnee }}" min="{{ $dateMin }}" max="{{ $aujourdhui }}" required>
                        <div class="form-text">Choisissez un jour passé ou aujourd’hui pour saisir l’absence.</div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="absence" value="1" id="absence_check">
                            <label class="form-check-label" for="absence_check">Absence</label>
                        </div>
                        @if(auth()->user()->hasPermission('absences-enseignants.create'))
                        <button type="button" class="btn btn-outline-danger btn-sm" id="absence_journee_btn">
                            Toute la journée
                        </button>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="absence_classe_id" class="form-label">Classe <span class="text-danger">*</span></label>
                        <select name="classe_id" id="absence_classe_id" class="form-select" required>
                            <option value="">Choisir la classe…</option>
                        </select>
                        <div class="form-text" id="absence_cours_aide">Selon l’emploi du temps de la date choisie.</div>
                    </div>
                    <div class="table-responsive mb-3" id="absence_cours_wrap">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Classe</th>
                                    <th>De</th>
                                    <th>À</th>
                                </tr>
                            </thead>
                            <tbody id="absence_cours_tbody"></tbody>
                        </table>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="absence_heure_debut" class="form-label">De <span class="text-danger">*</span></label>
                            <input type="time" name="heure_debut" id="absence_heure_debut" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label for="absence_heure_fin" class="form-label">À <span class="text-danger">*</span></label>
                            <input type="time" name="heure_fin" id="absence_heure_fin" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label for="absence_motif" class="form-label">Motif <span class="text-muted">(optionnel)</span></label>
                        <textarea name="motif" id="absence_motif" class="form-control" rows="2" placeholder="Ex. maladie, rendez-vous…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    @if(auth()->user()->hasPermission('absences-enseignants.create'))
                    <button type="submit" class="btn btn-primary" id="absence_submit">Enregistrer</button>
                    @endif
                </div>
            </form>
            <form method="POST" action="{{ route('absences-enseignants.journee') }}" id="formAbsenceJournee" class="d-none">
                @csrf
                <input type="hidden" name="enseignant_id" id="absence_journee_enseignant_id">
                <input type="hidden" name="date" id="absence_journee_date" value="{{ $dateSelectionnee }}">
                <input type="hidden" name="motif" id="absence_journee_motif">
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
const coursParEnseignant = @json($coursDuJour);
const datePage = @json($dateSelectionnee);
const dateAujourdhui = @json($aujourdhui);
const urlCours = @json(route('absences-enseignants.cours'));

function formatDateFr(date) {
    if (!date) return '';
    if (date === dateAujourdhui) return 'aujourd’hui';
    const parts = date.split('-');
    if (parts.length !== 3) return date;
    return parts[2] + '/' + parts[1] + '/' + parts[0];
}

function heureLabel(h) {
    return (h || '').replace(':', 'h');
}

function coursDe(enseignantId) {
    if (!coursParEnseignant) return [];
    return coursParEnseignant[enseignantId]
        || coursParEnseignant[String(enseignantId)]
        || [];
}

function appliquerCreneau(option) {
    if (option && option.dataset.debut) {
        document.getElementById('absence_heure_debut').value = option.dataset.debut;
        document.getElementById('absence_heure_fin').value = option.dataset.fin;
    } else {
        document.getElementById('absence_heure_debut').value = '';
        document.getElementById('absence_heure_fin').value = '';
    }
}

function remplirCoursDuJour(cours, date) {
    const select = document.getElementById('absence_classe_id');
    const tbody = document.getElementById('absence_cours_tbody');
    const wrap = document.getElementById('absence_cours_wrap');
    const aide = document.getElementById('absence_cours_aide');
    const submit = document.getElementById('absence_submit');
    const journeeBtn = document.getElementById('absence_journee_btn');
    const libelle = formatDateFr(date || document.getElementById('absence_date').value || datePage);

    select.innerHTML = '<option value="">Choisir la classe…</option>';
    tbody.innerHTML = '';

    if (!cours.length) {
        wrap.style.display = 'none';
        select.disabled = true;
        select.required = false;
        aide.textContent = 'Aucun cours prévu ' + (libelle === 'aujourd’hui' ? 'aujourd’hui' : 'le ' + libelle) + ' pour cet enseignant.';
        if (submit) submit.disabled = true;
        if (journeeBtn) journeeBtn.disabled = true;
        appliquerCreneau(null);
        return;
    }

    wrap.style.display = '';
    select.disabled = false;
    select.required = true;
    aide.textContent = 'Classes de l’emploi du temps du ' + (libelle === 'aujourd’hui' ? 'jour' : libelle) + '.';
    if (submit) submit.disabled = false;
    if (journeeBtn) journeeBtn.disabled = false;

    cours.forEach(function (item) {
        const opt = document.createElement('option');
        opt.value = item.classe_id;
        opt.dataset.debut = item.heure_debut || '';
        opt.dataset.fin = item.heure_fin || '';
        let libelleOpt = item.classe || 'Classe';
        if (item.matiere) libelleOpt += ' — ' + item.matiere;
        if (item.heure_debut) libelleOpt += ' (' + heureLabel(item.heure_debut) + '-' + heureLabel(item.heure_fin) + ')';
        opt.textContent = libelleOpt;
        select.appendChild(opt);

        const tr = document.createElement('tr');
        tr.innerHTML = '<td>' + (item.classe || '—')
            + (item.matiere ? ' <span class="text-muted">(' + item.matiere + ')</span>' : '')
            + '</td><td>' + heureLabel(item.heure_debut) + '</td><td>' + heureLabel(item.heure_fin) + '</td>';
        tbody.appendChild(tr);
    });

    if (select.options.length > 1) {
        select.selectedIndex = 1;
        appliquerCreneau(select.options[1]);
    }
}

function chargerCoursPourDate(enseignantId, date) {
    const aide = document.getElementById('absence_cours_aide');
    aide.textContent = 'Chargement de l’emploi du temps…';

    const params = new URLSearchParams({
        enseignant_id: enseignantId,
        date: date
    });

    fetch(urlCours + '?' + params.toString(), {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            remplirCoursDuJour(data.cours || [], data.date || date);
        })
        .catch(function () {
            aide.textContent = 'Impossible de charger les cours pour cette date.';
            remplirCoursDuJour([], date);
        });
}

document.getElementById('absence_classe_id').addEventListener('change', function () {
    appliquerCreneau(this.options[this.selectedIndex]);
});

document.getElementById('absence_date').addEventListener('change', function () {
    const date = this.value;
    document.getElementById('absence_journee_date').value = date;
    const enseignantId = document.getElementById('absence_enseignant_id').value;
    if (!enseignantId || !date) return;

    if (date === datePage) {
        remplirCoursDuJour(coursDe(enseignantId), date);
        return;
    }

    chargerCoursPourDate(enseignantId, date);
});

document.getElementById('formAbsenceJournee').addEventListener('submit', function () {
    document.getElementById('absence_journee_motif').value = document.getElementById('absence_motif').value;
    document.getElementById('absence_journee_date').value = document.getElementById('absence_date').value;
});

const journeeBtnEl = document.getElementById('absence_journee_btn');
if (journeeBtnEl) {
    journeeBtnEl.addEventListener('click', function () {
        if (this.disabled) return;
        document.getElementById('absence_journee_motif').value = document.getElementById('absence_motif').value;
        document.getElementById('absence_journee_date').value = document.getElementById('absence_date').value;
        document.getElementById('formAbsenceJournee').submit();
    });
}

document.querySelectorAll('.ae-row-enseignant').forEach(function (row) {
    row.addEventListener('click', function () {
        document.getElementById('absence_enseignant_id').value = this.dataset.id;
        document.getElementById('absence_enseignant_nom').textContent = this.dataset.nom;
        document.getElementById('absence_check').checked = false;
        document.getElementById('absence_motif').value = '';
        document.getElementById('absence_date').value = datePage;
        document.getElementById('absence_journee_enseignant_id').value = this.dataset.id;
        document.getElementById('absence_journee_date').value = datePage;
        remplirCoursDuJour(coursDe(this.dataset.id), datePage);

        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAbsenceEnseignant')).show();
    });
});
</script>
@endpush
