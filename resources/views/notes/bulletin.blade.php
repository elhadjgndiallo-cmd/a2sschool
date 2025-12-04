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
            @if(auth()->user()->hasPermission('notes.edit'))
                <a href="{{ route('notes.saisir', $eleve->classe_id) }}" class="btn btn-primary me-2">
                    <i class="fas fa-edit me-1"></i>
                    Modifier les notes
                </a>
            @endif
            <a href="{{ route('notes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
        </div>

    </div>

    @php
        $schoolInfo = \App\Helpers\SchoolHelper::getSchoolInfo();
        $logoUrl = $schoolInfo && $schoolInfo->logo ? asset('storage/' . $schoolInfo->logo) : null;
        $schoolName = $schoolInfo && isset($schoolInfo->nom) ? $schoolInfo->nom : 'École';
        $schoolSlogan = $schoolInfo && isset($schoolInfo->slogan) ? $schoolInfo->slogan : '';
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();

    @endphp

    <!-- En-tête avec logo et nom de l'école pour l'impression -->
    <div class="bulletin-page">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #1a5490 0%, #2c3e50 100%); color: white; border: none; padding: 8px 15px; position: relative;">
                <!-- Logo aux angles -->
                <div style="position: absolute; top: 8px; left: 15px;">
                    @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo de l'école" style="max-width: 50px; max-height: 50px; object-fit: contain; background: white; padding: 4px; border-radius: 5px; display: block;">
                    @endif
                </div>
                <div style="position: absolute; top: 8px; right: 15px;">
                    @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo de l'école" style="max-width: 50px; max-height: 50px; object-fit: contain; background: white; padding: 4px; border-radius: 5px; display: block;">
                    @endif
                </div>
                
                <!-- Nom de l'école et slogan au centre -->
                <div class="text-center" style="padding: 0 70px; margin-bottom: 6px !important; margin-top: 8px;">
                    <h4 class="mb-1" style="font-weight: 800; font-size: 1.2rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2; margin-bottom: 4px !important;">
                        {{ $schoolName }}
                    </h4>
                    @if($schoolSlogan)
                    <div style="font-size: 0.75rem; font-weight: 500; opacity: 0.95; line-height: 1.2; font-style: italic; margin-top: 2px;">
                        {{ $schoolSlogan }}
                    </div>
                    @endif
                </div>
                
                <div class="border-top border-white border-2 pt-2" style="border-top: 2px solid rgba(255,255,255,0.3) !important; padding-top: 6px !important; margin-top: 8px; padding-left: 0; padding-right: 0;">
                    <div class="row align-items-center" style="margin-left: 0; margin-right: 0;">
                        <div class="col-md-6" style="padding-left: 0; padding-right: 5px;">
                            <h3 class="mb-0" style="font-weight: 800; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); font-size: 1.05rem; letter-spacing: 0.5px; line-height: 1.2;">BULLETIN DE NOTES</h3>
                            <div style="font-size: 0.85rem; font-weight: 500; opacity: 0.95; line-height: 1.2; margin-top: 2px;">{{ $eleve->classe->nom }} - {{ $eleve->classe->niveau }}</div>
                        </div>
                        <div class="col-md-6 text-end" style="padding-left: 5px; padding-right: 0;">
                            <h4 style="font-weight: 700; font-size: 0.95rem; margin-bottom: 2px; text-shadow: 1px 1px 2px rgba(0,0,0,0.5); line-height: 1.2;">Année Scolaire {{ $anneeScolaireActive ? $anneeScolaireActive->nom : (date('Y') . '-' . (date('Y')+1)) }}</h4>
                            <div style="font-size: 0.85rem; font-weight: 500; opacity: 0.95; line-height: 1.2;">
                                @if($periode == 'trimestre1')
                                    Trimestre 1
                                @elseif($periode == 'trimestre2')
                                    Trimestre 2
                                @elseif($periode == 'trimestre3')
                                    Trimestre 3
                                @else
                                    {{ ucfirst($periode) }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body" style="display: flex; flex-direction: column; flex: 1;">
                <!-- Informations élève -->
                <div class="row mb-2" style="margin-bottom: 8px !important;">
                    <div class="col-md-6">
                        <h5 style="font-size: 1.05rem; margin-bottom: 4px; font-weight: 800; color: #2c3e50; line-height: 1.2;"><strong>{{ $eleve->nom_complet }}</strong></h5>
                        <p class="mb-1" style="font-size: 0.9rem; margin-bottom: 3px; font-weight: 600; line-height: 1.2;"><strong>Numéro:</strong> <span style="font-weight: 500;">{{ $eleve->numero_etudiant }}</span></p>
                        <p class="mb-1" style="font-size: 0.9rem; margin-bottom: 3px; font-weight: 600; line-height: 1.2;"><strong>Date de naissance:</strong> <span style="font-weight: 500;">{{ $eleve->utilisateur->date_naissance ? \Carbon\Carbon::parse($eleve->utilisateur->date_naissance)->format('d/m/Y') : 'Non renseignée' }}</span></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <div style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; padding: 8px 12px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); display: inline-block;">
                            <h5 class="mb-0" style="font-weight: 800; font-size: 0.95rem; margin-bottom: 3px; text-shadow: 1px 1px 2px rgba(0,0,0,0.3); line-height: 1.2;">Rang: {{ $rang }}/{{ $eleve->classe->eleves->count() }}</h5>
                            <p class="mb-0" style="font-size: 0.95rem; font-weight: 600; line-height: 1.2;">Moyenne: <strong>{{ number_format($moyenneGenerale, 2) }}/{{ $eleve->classe->note_max }}</strong></p>
                        </div>
                    </div>
                </div>

                <!-- Tableau des notes par matière -->
                <div class="table-responsive">
                    <table class="table table-bordered" style="margin-bottom: 8px;">
                        <thead style="background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%); color: white;">
                            <tr>
                                <th style="font-weight: 700; border: 1px solid #2c3e50; font-size: 0.85rem; padding: 5px 4px;">Matière</th>
                                <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.85rem; padding: 5px 4px;">Coef.</th>
                                @if(!$eleve->classe->isPrimaire())
                                <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.85rem; padding: 5px 4px;">Cours</th>
                                @endif
                                <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.85rem; padding: 5px 4px;">Comp.</th>
                                <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.85rem; padding: 5px 4px;">Finale</th>
                                @if(!$eleve->classe->isPrimaire())
                                <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.85rem; padding: 5px 4px;">Points</th>
                                @endif
                                <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.85rem; padding: 5px 4px;">Mention</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalPoints = 0; $totalCoeff = 0; @endphp
                            @foreach($moyennesParMatiere as $matiereId => $data)
                                @php 
                                    $noteCours = $data['notes']->where('note_cours', '!=', null)->avg('note_cours') ?? 0;
                                    $noteComposition = $data['notes']->where('note_composition', '!=', null)->avg('note_composition') ?? 0;
                                    $noteFinale = $data['moyenne'];
                                    $totalPoints += $data['points'];
                                    $totalCoeff += $data['coefficient'];
                                @endphp
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="font-weight: 600; padding: 5px 4px; background-color: #f8f9fa; font-size: 0.8rem;"><strong>{{ $data['matiere']->nom }}</strong></td>
                                    <td class="text-center" style="padding: 5px 4px; font-weight: 600; background-color: #e9ecef; font-size: 0.8rem;">{{ $data['coefficient'] }}</td>
                                    @if(!$eleve->classe->isPrimaire())
                                    <td class="text-center notes-cell" style="padding: 5px 4px; font-size: 0.85rem;">
                                        <span class="note-value" style="font-size: 0.85rem; font-weight: 600; color: #2c3e50;">
                                            {{ $noteCours > 0 ? number_format($noteCours, 2) : '-' }}/{{ $eleve->classe->note_max }}
                                        </span>
                                    </td>
                                    @endif
                                    <td class="text-center notes-cell" style="padding: 5px 4px; font-size: 0.85rem;">
                                        <span class="note-value" style="font-size: 0.85rem; font-weight: 600; color: #2c3e50;">
                                            {{ $noteComposition > 0 ? number_format($noteComposition, 2) : '-' }}/{{ $eleve->classe->note_max }}
                                        </span>
                                    </td>
                                    <td class="text-center notes-cell" style="padding: 5px 4px; font-size: 0.85rem;">
                                        <span class="note-value" style="font-size: 0.85rem; font-weight: 700; color: #2c3e50;">
                                            {{ number_format($noteFinale, 2) }}/{{ $eleve->classe->note_max }}
                                        </span>
                                    </td>
                                    @if(!$eleve->classe->isPrimaire())
                                    <td class="text-center" style="padding: 5px 4px; font-weight: 600; background-color: #e9ecef; font-size: 0.8rem;">{{ $data['points'] }}</td>
                                    @endif
                                    <td style="padding: 5px 4px; font-size: 0.75rem;">
                                        @php
                                            $appreciation = $eleve->classe->getAppreciation($noteFinale);
                                        @endphp
                                        <span class="badge bg-{{ $appreciation['color'] }}" style="font-size: 0.7rem; padding: 2px 6px;">{{ $appreciation['label'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-top: 3px solid #2c3e50;">
                                <th style="font-weight: 700; padding: 6px 4px; font-size: 0.9rem; color: #2c3e50;">MOYENNE GÉNÉRALE</th>
                                <th class="text-center" style="font-weight: 700; padding: 6px 4px; font-size: 0.9rem; color: #2c3e50;">{{ $totalCoeff }}</th>
                                @if(!$eleve->classe->isPrimaire())
                                <th class="text-center" style="font-weight: 700; padding: 6px 4px; font-size: 0.9rem; color: #6c757d;">-</th>
                                @endif
                                <th class="text-center" style="font-weight: 700; padding: 6px 4px; font-size: 0.9rem; color: #6c757d;">-</th>
                                <th class="text-center" style="font-weight: 700; padding: 6px 4px;">
                                    @php
                                        $appreciationGeneraleBadge = $eleve->classe->getAppreciation($moyenneGenerale);
                                    @endphp
                                    <span class="badge bg-{{ $appreciationGeneraleBadge['color'] }}" style="font-size: 0.85rem; padding: 4px 10px; font-weight: 700;">
                                        {{ number_format($moyenneGenerale, 2) }}/{{ $eleve->classe->note_max }}
                                    </span>
                                </th>
                                @if(!$eleve->classe->isPrimaire())
                                <th class="text-center" style="font-weight: 700; padding: 6px 4px; font-size: 0.9rem; color: #2c3e50;">{{ round($totalPoints, 2) }}</th>
                                @endif
                                <th style="font-weight: 700; padding: 6px 4px;">
                                    @php $moy = $moyenneGenerale; @endphp
                                    @if($moy >= 16)
                                        <span class="badge bg-success" style="font-size: 0.75rem; padding: 4px 8px; font-weight: 700;">Excellent</span>
                                    @elseif($moy >= 14)
                                        <span class="badge bg-info" style="font-size: 0.75rem; padding: 4px 8px; font-weight: 700;">Très bien</span>
                                    @elseif($moy >= 12)
                                        <span class="badge bg-warning text-dark" style="font-size: 0.75rem; padding: 4px 8px; font-weight: 700;">Bien</span>
                                    @elseif($moy >= 10)
                                        <span class="badge bg-secondary" style="font-size: 0.75rem; padding: 4px 8px; font-weight: 700;">Assez bien</span>
                                    @else
                                        <span class="badge bg-danger" style="font-size: 0.75rem; padding: 4px 8px; font-weight: 700;">Insuffisant</span>
                                    @endif
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Footer avec Observations et Signatures -->
            <div class="bulletin-footer">
                <div class="row" style="margin: 0; display: flex; flex-direction: row; flex-wrap: nowrap;">
                    <div class="col-md-6" style="padding-right: 5px; padding-left: 0; width: 50%; flex: 0 0 50%; display: inline-block; vertical-align: top;">
                        <div style="border: 1px solid #2c3e50; border-radius: 2px; padding: 0px 2px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); height: 50px; overflow: hidden;">
                            <h6 style="color: #2c3e50; font-weight: 700; margin-bottom: 0px; border-bottom: 1px solid #2c3e50; padding-bottom: 0px; font-size: 0.45rem; line-height: 0.8;"><strong>Observations:</strong></h6>
                            <div style="min-height: 6px; line-height: 0.8; font-size: 0.42rem; height: 38px; overflow: hidden;">
                                &nbsp;
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6" style="padding-left: 5px; padding-right: 0; width: 50%; flex: 0 0 50%; display: inline-block; vertical-align: top;">
                        <div style="border: 1px solid #2c3e50; border-radius: 2px; padding: 0px 2px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); height: 50px; overflow: hidden;">
                            <h6 style="color: #2c3e50; font-weight: 700; margin-bottom: 0px; border-bottom: 1px solid #2c3e50; padding-bottom: 0px; font-size: 0.45rem; line-height: 0.8;"><strong>Signatures:</strong></h6>
                            <div class="row" style="margin-top: 0px !important; margin-left: 0; margin-right: 0;">
                                <div class="col-6" style="padding-left: 0; padding-right: 2px;">
                                    <p class="text-center" style="font-weight: 700; color: #2c3e50; margin-bottom: 0px; font-size: 0.4rem; line-height: 0.8;">Directeur</p>
                                    <div style="height: 8px; border-bottom: 1px solid #2c3e50; margin-bottom: 0px;"></div>
                                    <div class="text-center" style="color: #6c757d; font-size: 0.4rem; line-height: 0.8;">Date: _____</div>
                                </div>
                                <div class="col-6" style="padding-left: 2px; padding-right: 0;">
                                    <p class="text-center" style="font-weight: 700; color: #2c3e50; margin-bottom: 0px; font-size: 0.4rem; line-height: 0.8;">Parent</p>
                                    <div style="height: 8px; border-bottom: 1px solid #2c3e50; margin-bottom: 0px;"></div>
                                    <div class="text-center" style="color: #6c757d; font-size: 0.4rem; line-height: 0.8;">Date: _____</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Version écran (masquée à l'impression et à l'écran pour correspondre au design) -->
    <div class="d-print-none" style="display: none !important;">
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
                                <th>Mention</th>
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
                                        {{ number_format($data['notes']->where('note_cours', '!=', null)->avg('note_cours'), 2) }}/{{ $eleve->classe->note_max }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($data['notes']->where('note_composition', '!=', null)->count() > 0)
                                        {{ number_format($data['notes']->where('note_composition', '!=', null)->avg('note_composition'), 2) }}/{{ $eleve->classe->note_max }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center notes-cell">
                                    <span class="note-value" style="font-size: 0.85rem; font-weight: 700; color: #2c3e50;">
                                        {{ number_format($data['moyenne'], 2) }}/{{ $eleve->classe->note_max }}
                                    </span>
                                </td>
                                <td class="text-center">{{ number_format($data['points'], 2) }}</td>
                                <td>
                                    @php
                                        $appreciationMatiere = $eleve->classe->getAppreciation($data['moyenne']);
                                    @endphp
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
                                        @if($appreciationGenerale['label'] == 'Excellent' || $appreciationGenerale['label'] == 'Très bien')
                                            <i class="fas fa-star me-1"></i>
                                        @elseif($appreciationGenerale['label'] == 'Bien')
                                            <i class="fas fa-thumbs-up me-1"></i>
                                        @elseif($appreciationGenerale['label'] == 'Assez bien')
                                            <i class="fas fa-check me-1"></i>
                                        @elseif($appreciationGenerale['label'] == 'Passable')
                                            <i class="fas fa-exclamation me-1"></i>
                                        @elseif($appreciationGenerale['label'] == 'Insuffisant')
                                            <i class="fas fa-minus me-1"></i>
                                        @elseif($appreciationGenerale['label'] == 'Mal' || $appreciationGenerale['label'] == 'Médiocre')
                                            <i class="fas fa-times me-1"></i>
                                        @else
                                            <i class="fas fa-question me-1"></i>
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
                                                            @if(auth()->user()->hasPermission('notes.edit'))
                                                                <a href="{{ route('notes.edit', $note->id) }}" 
                                                                   class="btn btn-outline-primary btn-sm" 
                                                                   title="Modifier cette note">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            @endif
                                                            @if(auth()->user()->hasPermission('notes.delete'))
                                                                <button type="button" 
                                                                        class="btn btn-outline-danger btn-sm" 
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
/* Styles pour l'impression A4 */
@media print {
    @page {
        size: A4 portrait;
        margin: 1cm 1.5cm !important;
    }
    
    * {
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    html, body {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        font-size: 11px;
        line-height: 1.3;
        background: white !important;
    }
    
    /* Masquer les éléments non nécessaires */
    .no-print,
    nav,
    .navbar,
    header,
    footer,
    .btn-toolbar,
    .btn,
    .d-print-none {
        display: none !important;
    }
    
    /* Afficher les éléments pour impression */
    .d-print-block,
    .d-none.d-print-block {
        display: block !important;
    }
    
    .container-fluid,
    .container {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 auto !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }
    
    .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
    }
    
    .col-md-12,
    .col-md-6 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    
    .col-md-6 {
        display: block !important;
        float: none !important;
        width: 50% !important;
        flex: 0 0 50% !important;
    }
    
    .bulletin-page {
        page-break-inside: avoid;
        page-break-after: auto;
        page-break-before: auto;
        margin: 0 auto !important;
        margin-left: auto !important;
        margin-right: auto !important;
        padding: 0 !important;
        margin-bottom: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        box-shadow: none !important;
        border: none !important;
        background: white !important;
        min-height: auto !important;
        max-height: none !important;
        overflow: visible !important;
        display: flex !important;
        flex-direction: column !important;
        position: relative !important;
        box-sizing: border-box !important;
    }
    
    .bulletin-page:first-child {
        page-break-before: auto;
    }
    
    .bulletin-page:last-child {
        page-break-after: avoid;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
        margin: 0 auto !important;
        margin-left: auto !important;
        margin-right: auto !important;
        padding: 0 !important;
        min-height: auto !important;
        max-height: none !important;
        display: flex !important;
        flex-direction: column !important;
        page-break-inside: auto !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    
    .card-header {
        background: linear-gradient(135deg, #1a5490 0%, #2c3e50 100%) !important;
        color: white !important;
        border: none !important;
        padding: 2px 12px !important;
        padding-left: 12px !important;
        padding-right: 12px !important;
        margin: 0 auto !important;
        margin-left: auto !important;
        margin-right: auto !important;
        page-break-inside: avoid;
        page-break-after: avoid;
        flex-shrink: 0 !important;
        position: relative !important;
        width: 100% !important;
        max-width: 100% !important;
        max-height: 100px !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }
    
    .card-header > div[style*="position: absolute"][style*="left"] {
        left: 15px !important;
    }
    
    .card-header > div[style*="position: absolute"][style*="right"] {
        right: 15px !important;
    }
    
    .card-header img {
        max-width: 50px !important;
        max-height: 50px !important;
        object-fit: contain !important;
        background: white !important;
        padding: 4px !important;
        border-radius: 5px !important;
    }
    
    .card-header h4 {
        font-size: 1.2rem !important;
        margin-bottom: 4px !important;
        font-weight: 800 !important;
        line-height: 1.2 !important;
    }
    
    .card-header h3 {
        font-size: 1.05rem !important;
        margin-bottom: 3px !important;
        font-weight: 800 !important;
        line-height: 1.2 !important;
    }
    
    .card-header h4 {
        font-size: 1rem !important;
        margin-bottom: 2px !important;
        font-weight: 800 !important;
        line-height: 1.2 !important;
    }
    
    .card-header h5 {
        font-size: 0.95rem !important;
        margin-bottom: 2px !important;
        font-weight: 700 !important;
        line-height: 1.2 !important;
    }
    
    .card-header div {
        font-size: 0.85rem !important;
        line-height: 1.2 !important;
    }
    
    .card-header .d-flex {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        margin-bottom: 6px !important;
    }
    
    .card-header .border-top {
        padding-top: 6px !important;
        margin-top: 6px !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    
    .card-header .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    
    .card-header .row .col-md-6:first-child {
        padding-left: 0 !important;
        padding-right: 5px !important;
    }
    
    .card-header .row .col-md-6.text-end,
    .card-header .row .col-md-6:last-child {
        padding-left: 5px !important;
        padding-right: 0 !important;
    }
    
    .card-body {
        padding: 0px 12px 60px 12px !important;
        page-break-inside: avoid !important;
        font-size: 0.75rem !important;
        flex: 1 1 auto !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        min-height: 0 !important;
        flex-shrink: 1 !important;
        position: relative !important;
        margin-bottom: 0 !important;
    }
    
    .bulletin-footer {
        position: absolute !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        padding: 0px 12px 0px 12px !important;
        flex-shrink: 0 !important;
        margin-bottom: 0 !important;
        margin-top: auto !important;
        max-height: 55px !important;
        height: 55px !important;
        overflow: hidden !important;
        background: white !important;
        z-index: 100 !important;
        box-sizing: border-box !important;
        page-break-inside: avoid !important;
        page-break-after: avoid !important;
        border-top: 1px solid #dee2e6 !important;
    }
    
    .bulletin-footer .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
    }
    
    .bulletin-footer .col-md-6 {
        padding-left: 0 !important;
        padding-right: 0 !important;
        display: inline-block !important;
        float: none !important;
        width: 50% !important;
        max-width: 50% !important;
        flex: 0 0 50% !important;
        vertical-align: top !important;
    }
    
    .bulletin-footer .col-md-6:first-child {
        padding-right: 5px !important;
    }
    
    .bulletin-footer .col-md-6:last-child {
        padding-left: 5px !important;
    }
    
    .bulletin-footer .col-6 {
        padding-left: 0 !important;
        padding-right: 0 !important;
        display: block !important;
        float: none !important;
        width: 50% !important;
        flex: 0 0 50% !important;
    }
    
    .bulletin-footer .col-6:first-child {
        padding-right: 3px !important;
    }
    
    .bulletin-footer .col-6:last-child {
        padding-left: 3px !important;
    }
    
    .card-body h5 {
        font-size: 1rem !important;
        margin-bottom: 2px !important;
        line-height: 1.2 !important;
    }
    
    .card-body h6 {
        font-size: 0.95rem !important;
        margin-bottom: 2px !important;
        line-height: 1.2 !important;
    }
    
    .card-body p {
        font-size: 0.85rem !important;
        margin-bottom: 2px !important;
        line-height: 1.2 !important;
    }
    
    .table {
        font-size: 9px !important;
        margin-bottom: 1px !important;
        width: 100% !important;
        border-collapse: collapse !important;
        page-break-inside: avoid;
        flex-shrink: 0;
    }
    
    .table th, .table td {
        padding: 2px 3px !important;
        border: 1px solid #333 !important;
        text-align: left !important;
        font-size: 0.85rem !important;
        line-height: 1.0 !important;
    }
    
    .table th {
        font-size: 0.9rem !important;
        font-weight: 700 !important;
        padding: 3px 3px !important;
    }
    
    .table td span {
        font-size: 0.95rem !important;
    }
    
    .notes-cell .note-value {
        font-size: 0.95rem !important;
    }
    
    .notes-cell {
        font-size: 0.95rem !important;
    }
    
    .table thead {
        background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%) !important;
        color: white !important;
    }
    
    .table thead th {
        background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%) !important;
        color: white !important;
        font-weight: 700 !important;
        border: 1px solid #2c3e50 !important;
    }
    
    .table tbody tr {
        page-break-inside: avoid;
    }
    
    .table tfoot {
        background: #f8f9fa !important;
        font-weight: 700 !important;
        flex-shrink: 0;
    }
    
    .table-responsive {
        overflow-x: auto !important;
        overflow-y: hidden !important;
        page-break-inside: avoid !important;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .table-responsive table {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .table-responsive thead, .table-responsive tfoot {
        flex-shrink: 0;
    }
    
    .table-responsive tbody {
        flex-grow: 1;
        overflow-y: auto;
    }
    
    .badge {
        padding: 3px 6px !important;
        font-size: 0.75rem !important;
        border: 1px solid #333 !important;
        line-height: 1.2 !important;
    }
    
    .row {
        margin: 0 !important;
    }
    
    .row > * {
        padding: 4px 6px !important;
    }
    
    .mb-1, .mb-2, .mb-3, .mb-4, .mb-5 {
        margin-bottom: 6px !important;
    }
    
    .mt-1, .mt-2, .mt-3 {
        margin-top: 6px !important;
    }
    
    /* Assurer que les couleurs s'affichent */
    .bg-success {
        background-color: #28a745 !important;
        color: white !important;
    }
    
    .bg-danger {
        background-color: #dc3545 !important;
        color: white !important;
    }
    
    .bg-info {
        background-color: #17a2b8 !important;
        color: white !important;
    }
    
    .bg-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
    }
    
    .bg-secondary {
        background-color: #6c757d !important;
        color: white !important;
    }
}

/* Styles pour l'écran */
.bulletin-page {
    background: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    margin-bottom: 20px;
    border: 2px solid #2c3e50;
    border-radius: 12px;
    overflow: hidden;
}

.bulletin-page .card {
    border: none;
    box-shadow: none;
    border-radius: 12px;
}

.bulletin-page .table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.bulletin-page .table th {
    background: #f8f9fa;
    font-weight: 600;
    border: 1px solid #dee2e6;
}

.bulletin-page .table td {
    border: 1px solid #dee2e6;
    vertical-align: middle;
}

.bulletin-page .table tbody tr:hover {
    background-color: #f8f9fa;
}

.bulletin-page .badge {
    font-weight: 600;
    border-radius: 6px;
}

.bulletin-page .badge.bg-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
}

.bulletin-page .badge.bg-danger {
    background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%) !important;
}

.bulletin-page .badge.bg-info {
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%) !important;
}

.bulletin-page .badge.bg-warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
}

.bulletin-page .badge.bg-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
}

/* Optimisation pour A4 */
.bulletin-page {
    width: 21cm;
    min-height: auto;
    margin: 0 auto 20px auto;
    padding: 0;
}

@media screen and (max-width: 768px) {
    .bulletin-page {
        width: 100%;
        margin: 0 0 20px 0;
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
