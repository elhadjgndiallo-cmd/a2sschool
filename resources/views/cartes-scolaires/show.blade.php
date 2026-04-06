@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-id-card text-primary me-2"></i>
                        Détails de la Carte Scolaire
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('cartes-scolaires.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Informations de la carte -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Informations de la carte</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Numéro de carte :</strong></td>
                                                    <td><span class="badge bg-info fs-6">{{ $cartes_scolaire->numero_carte }}</span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Type :</strong></td>
                                                    <td><span class="badge bg-primary">{{ $cartes_scolaire->type_carte_libelle }}</span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Statut :</strong></td>
                                                    <td>
                                                        @php
                                                            $badgeClass = match($cartes_scolaire->statut) {
                                                                'active' => 'bg-success',
                                                                'expiree' => 'bg-danger',
                                                                'suspendue' => 'bg-warning',
                                                                'annulee' => 'bg-secondary',
                                                                default => 'bg-secondary'
                                                            };
                                                        @endphp
                                                        <span class="badge {{ $badgeClass }} fs-6">{{ $cartes_scolaire->statut_libelle }}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Date d'émission :</strong></td>
                                                    <td>{{ $cartes_scolaire->date_emission ? $cartes_scolaire->date_emission->format('d/m/Y') : 'Non définie' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Date d'expiration :</strong></td>
                                                    <td>
                                                        @if($cartes_scolaire->date_expiration)
                                                            <span class="{{ $cartes_scolaire->date_expiration < now() ? 'text-danger fw-bold' : 'text-success' }}">
                                                                {{ $cartes_scolaire->date_expiration->format('d/m/Y') }}
                                                            </span>
                                                            @if($cartes_scolaire->est_valide)
                                                                <i class="fas fa-check-circle text-success ms-2"></i>
                                                            @else
                                                                <i class="fas fa-times-circle text-danger ms-2"></i>
                                                            @endif
                                                        @else
                                                            <span class="text-warning">Non définie</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Émise par :</strong></td>
                                                    <td>{{ $cartes_scolaire->emisePar->nom ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Validée par :</strong></td>
                                                    <td>{{ $cartes_scolaire->valideePar->nom ?? 'Non validée' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Créée le :</strong></td>
                                                    <td>{{ $cartes_scolaire->created_at ? $cartes_scolaire->created_at->format('d/m/Y à H:i') : 'Non définie' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Modifiée le :</strong></td>
                                                    <td>{{ $cartes_scolaire->updated_at ? $cartes_scolaire->updated_at->format('d/m/Y à H:i') : 'Non définie' }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    @if($cartes_scolaire->observations)
                                        <div class="mt-3">
                                            <h6>Observations :</h6>
                                            <div class="alert alert-light border">
                                                {{ $cartes_scolaire->observations }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Informations de l'élève -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Informations de l'élève</h5>
                                </div>
                                <div class="card-body text-center">
                                    @if($cartes_scolaire->eleve->utilisateur->photo_profil)
                                        <img src="{{ asset('storage/' . $cartes_scolaire->eleve->utilisateur->photo_profil) }}" 
                                             class="rounded-circle mb-3" 
                                             width="120" height="120" 
                                             alt="Photo de l'élève">
                                    @else
                                        <div class="bg-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" 
                                             style="width: 120px; height: 120px;">
                                            <i class="fas fa-user fa-3x text-white"></i>
                                        </div>
                                    @endif
                                    
                                    <h5>{{ $cartes_scolaire->eleve->utilisateur->nom }} {{ $cartes_scolaire->eleve->utilisateur->prenom }}</h5>
                                    <p class="text-muted mb-2">{{ $cartes_scolaire->eleve->numero_etudiant }}</p>
                                    
                                    @if($cartes_scolaire->eleve->classe)
                                        <span class="badge bg-secondary mb-2">{{ $cartes_scolaire->eleve->classe->nom }}</span>
                                    @endif
                                    
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            Né(e) le {{ $cartes_scolaire->eleve->utilisateur->date_naissance ? $cartes_scolaire->eleve->utilisateur->date_naissance->format('d/m/Y') : 'Non définie' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aperçu de la carte -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Aperçu de la carte scolaire</h5>
                                </div>
                                <div class="card-body text-center">
                                    <div class="d-inline-block" style="transform: scale(1.5); transform-origin: center;">
                                        @php
                                            $cardBorder = $couleurs['document']['document_card_border'] ?? '#d4af37';
                                            $cardAccent = $couleurs['document']['document_card_accent'] ?? '#1e3c72';
                                            $cardTitle = $couleurs['document']['document_card_title'] ?? '#d4af37';
                                            $cardBodyText = $couleurs['document']['document_card_text'] ?? '#333';
                                            $cardMutedText = $couleurs['document']['document_card_muted'] ?? '#666';
                                            $cardBg = $couleurs['document']['document_card_bg'] ?? '#ffffff';
                                            $statusActive = $couleurs['resultat']['resultat_success_bg'] ?? '#28a745';
                                        @endphp
                                        <div style="width: 86mm; height: 54mm; border: 2px solid {{ $cardBorder }}; background: linear-gradient(135deg, {{ $cardBg }} 0%, {{ $cardBg }} 100%); position: relative; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                            <!-- Header -->
                                            <div style="background: {{ $cardBg }}; color: {{ $cardBodyText }}; padding: 2mm; text-align: center; height: 12mm; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid {{ $cardBorder }};">
                                                @php
                                                    $school = \App\Helpers\SchoolHelper::getSchoolInfo();
                                                    $logoUrl = $school && $school->logo ? asset('storage/' . $school->logo) : null;
                                                @endphp
                                                <div style="width: 8mm; height: 8mm; background: {{ $cardBorder }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 4mm; color: {{ $cardAccent }}; font-weight: bold; overflow: hidden;">
                                                    @if($logoUrl)
                                                        <img src="{{ $logoUrl }}" alt="Logo de l'école" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                                    @else
                                                        🇬🇳
                                                    @endif
                                                </div>
                                                <div style="flex: 1; text-align: center;">
                                                    <div style="font-size: 2.5mm; font-weight: bold; margin: 0; color: black;">RÉPUBLIQUE DE GUINÉE</div>
                                                    <div style="font-size: 2mm; margin: 0; color: {{ $cardMutedText }};">Travail - Justice - Solidarité</div>
                                                    <div style="font-size: 3.5mm; font-weight: bold; margin: 0; color: {{ $cardBodyText }};">{{ $school->nom ?? 'ÉCOLE GANALIS' }}</div>
                                                    <div style="font-size: 3mm; font-weight: bold; margin: 0; color: {{ $cardTitle }};">CARTE D'IDENTITÉ SCOLAIRE</div>
                                                </div>
                                                <div style="width: 8mm; height: 8mm; background: {{ $cardBorder }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3mm; color: {{ $cardAccent }}; font-weight: bold; overflow: hidden;">
                                                    @if($logoUrl)
                                                        <img src="{{ $logoUrl }}" alt="Logo de l'école" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                                    @else
                                                        📚
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <!-- Body -->
                                            <div style="padding: 2mm; display: flex; height: calc(100% - 16mm);">
                                                <!-- Left side - Photo -->
                                                <div style="width: 25mm; padding-right: 2mm; border-right: 1px solid {{ $cardBorder }};">
                                                    <div style="width: 20mm; height: 25mm; border: 1px solid {{ $cardBorder }}; margin: 0 auto 2mm; background: {{ $cardBg }}; display: flex; align-items: center; justify-content: center; font-size: 2.5mm; color: {{ $cardMutedText }}; overflow: hidden;">
                                                        @if($cartes_scolaire->eleve->utilisateur->photo_profil)
                                                            <img src="{{ asset('storage/' . $cartes_scolaire->eleve->utilisateur->photo_profil) }}" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">
                                                        @else
                                                            <div style="text-align: center;">
                                                                <div style="font-size: 8mm; margin-bottom: 1mm;">👤</div>
                                                                <div>PHOTO</div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div style="font-size: 2.5mm; font-weight: bold; color: {{ $cardTitle }}; text-align: center; margin-bottom: 2mm;">
                                                        MAT. : {{ $cartes_scolaire->numero_carte }}
                                                    </div>
                                                </div>
                                                
                                                <!-- Right side - Info -->
                                                <div style="width: 55mm; padding-left: 2mm; position: relative;">
                                                    <div style="font-size: 2.2mm; line-height: 1.3;">
                                                        <div style="font-size: 2mm; color: {{ $cardBodyText }};">
                                                            <div style="margin-bottom: 0.5mm;"><strong>Année scolaire :</strong> {{ now()->year }}-{{ now()->year + 1 }}</div>
                                                            <div style="margin-bottom: 0.5mm;"><strong>Nom :</strong> {{ strtoupper($cartes_scolaire->eleve->utilisateur->nom) }}</div>
                                                            <div style="margin-bottom: 0.5mm;"><strong>Prénom :</strong> {{ strtoupper($cartes_scolaire->eleve->utilisateur->prenom) }}</div>
                                                            <div style="margin-bottom: 0.5mm;"><strong>Né(e) le :</strong> {{ $cartes_scolaire->eleve->utilisateur->date_naissance ? $cartes_scolaire->eleve->utilisateur->date_naissance->format('d-m-Y') : 'Non définie' }} A {{ $cartes_scolaire->eleve->utilisateur->lieu_naissance ?? 'CONAKRY' }}</div>
                                                            <div style="margin-bottom: 0.5mm; display: flex; justify-content: space-between;">
                                                                <span><strong>Sexe :</strong> {{ $cartes_scolaire->eleve->utilisateur->sexe == 'M' ? 'M' : 'F' }}</span>
                                                                <span><strong>Classe :</strong> {{ $cartes_scolaire->eleve->classe->nom ?? 'Non assigné' }}</span>
                                                            </div>
                                                            @if($cartes_scolaire->eleve->numero_etudiant)
                                                            <div style="margin-bottom: 0.5mm;"><strong>Numéro :</strong> {{ $cartes_scolaire->eleve->numero_etudiant }}</div>
                                                            @endif
                                                            <div style="margin-bottom: 0.5mm;"><strong>Contact :</strong> {{ $cartes_scolaire->eleve->utilisateur->telephone ?? '**********' }}</div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div style="position: absolute; top: 0; right: 0; width: 12mm; height: 12mm; border: 1px solid {{ $cardBorder }}; overflow: hidden;">
                                                        {!! $cartes_scolaire->qr_code !!}
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Footer -->
                                            <div style="position: absolute; bottom: 1mm; left: 2mm; right: 2mm; font-size: 1.8mm; color: {{ $cardMutedText }}; text-align: center; border-top: 1px solid {{ $cardBorder }}; padding-top: 1mm;">
                                                <span style="display: inline-block; padding: 0.5mm 1mm; border-radius: 1mm; font-size: 1.8mm; font-weight: bold; color: white; background: {{ $statusActive }};">{{ $cartes_scolaire->statut_libelle }}</span>
                                                | Émise le {{ $cartes_scolaire->date_emission ? $cartes_scolaire->date_emission->format('d/m/Y') : 'Non définie' }}
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-muted mt-3">
                                        <small>Dimensions : 86mm x 54mm (format carte d'identité standard)</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('cartes-scolaires.edit', $cartes_scolaire) }}" class="btn btn-warning me-2">
                                    <i class="fas fa-edit me-2"></i>Modifier
                                </a>
                                <a href="{{ route('cartes-scolaires.imprimer', $cartes_scolaire) }}" 
                                   class="btn btn-info me-2" 
                                   target="_blank">
                                    <i class="fas fa-print me-2"></i>Imprimer
                                </a>
                                @if($cartes_scolaire->statut === 'active')
                                    <a href="{{ route('cartes-scolaires.renouveler', $cartes_scolaire) }}" class="btn btn-success me-2">
                                        <i class="fas fa-sync me-2"></i>Renouveler
                                    </a>
                                @endif
                                <form action="{{ route('cartes-scolaires.destroy', $cartes_scolaire) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette carte ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash me-2"></i>Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
