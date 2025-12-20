<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carte Personnel Administration - {{ $cartes_personnel_administration->personnelAdministration->utilisateur->nom }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    @php
    use Illuminate\Support\Facades\Storage;
    use App\Helpers\SchoolHelper;
    use App\Models\Etablissement;
    
    $schoolInfo = SchoolHelper::getDocumentInfo();
    $school = $schoolInfo['school'] ?? Etablissement::principal();
    $address = $school ? ($school->adresse ?? '') : '';
    $phone = $school ? ($school->telephone ?? '') : '';
    @endphp
    <style>
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            body { margin: 0; }
            .no-print { display: none !important; }
            .card-container { 
                width: 86mm; 
                height: 54mm; 
                margin: 0 auto;
                page-break-inside: avoid;
            }
        }
        
        .card-container {
            width: 86mm;
            height: 54mm;
            border: 2px solid #d4af37;
            border-radius: 8px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
            overflow: hidden;
            margin: 20px auto;
        }
        
        .card-header {
            background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%);
            color: white;
            padding: 1px 4px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
        }
        
        .card-body {
            padding: 6px;
            height: calc(100% - 12px);
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Boutons d'impression -->
                <div class="no-print text-center mb-4">
                    <button onclick="window.print()" class="btn btn-primary me-2">
                        <i class="fas fa-print me-2"></i>Imprimer
                    </button>
                    <a href="{{ route('cartes-personnel-administration.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>

                <!-- Carte personnel administration style Guinée -->
                <div class="card-container">
                    <!-- En-tête avec drapeau et devise -->
                    <div class="card-header text-center">
                        <div class="d-flex justify-content-center align-items-center mb-1">
                            <!-- Drapeau au centre -->
                            <div class="d-flex justify-content-center align-items-center me-2">
                                <div class="bg-danger me-1" style="width: 15px; height: 8px;"></div>
                                <div class="bg-warning me-1" style="width: 15px; height: 8px;"></div>
                                <div class="bg-success" style="width: 15px; height: 8px;"></div>
                            </div>
                        </div>
                        <div style="font-size: 8px; font-weight: bold;">RÉPUBLIQUE DE GUINÉE</div>
                        <div style="font-size: 7px; font-weight: bold;">TRAVAIL - JUSTICE - SOLIDARITÉ</div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Nom de l'école -->
                        <div class="text-center mb-1">
                            <div style="font-size: 9px; font-weight: bold; color: #d4af37; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $schoolInfo['school_name'] }}</div>
                            <div style="font-size: 7px; color: #6c757d;">CARTE PERSONNEL ADMINISTRATION</div>
                        </div>
                        
                        <!-- Contenu principal -->
                        <div class="d-flex align-items-center" style="height: 100%; gap: 4px;">
                            <!-- Photo du personnel - Gauche -->
                            <div style="width: 50px; height: 60px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border: 2px solid #d4af37; border-radius: 4px; overflow: hidden;">
                                @if($cartes_personnel_administration->personnelAdministration->utilisateur->photo_profil)
                                    <img src="{{ asset('storage/' . $cartes_personnel_administration->personnelAdministration->utilisateur->photo_profil) }}" 
                                         alt="Photo personnel" 
                                         style="width: 100%; height: 100%; object-fit: cover;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%); display: none; align-items: center; justify-content: center; color: white; font-size: 18px; font-weight: bold;">
                                        {{ substr($cartes_personnel_administration->personnelAdministration->utilisateur->nom, 0, 1) }}{{ substr($cartes_personnel_administration->personnelAdministration->utilisateur->prenom, 0, 1) }}
                                    </div>
                                @else
                                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; font-weight: bold;">
                                        {{ substr($cartes_personnel_administration->personnelAdministration->utilisateur->nom, 0, 1) }}{{ substr($cartes_personnel_administration->personnelAdministration->utilisateur->prenom, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Informations du personnel - Droite -->
                            <div style="flex: 1; font-size: 8px; line-height: 1.2; display: flex; flex-direction: column; justify-content: center;">
                                <div style="margin-bottom: 1px;">
                                    <span style="font-weight: bold;">Nom:</span>
                                    <span>{{ $cartes_personnel_administration->personnelAdministration->utilisateur->nom }}</span>
                                </div>
                                <div style="margin-bottom: 1px;">
                                    <span style="font-weight: bold;">Prénom:</span>
                                    <span>{{ $cartes_personnel_administration->personnelAdministration->utilisateur->prenom }}</span>
                                </div>
                                <div style="margin-bottom: 1px;">
                                    <span style="font-weight: bold;">Poste:</span>
                                    <span>{{ $cartes_personnel_administration->personnelAdministration->poste ?? 'Non renseigné' }}</span>
                                </div>
                                <div style="margin-bottom: 1px;">
                                    <span style="font-weight: bold;">Département:</span>
                                    <span>{{ $cartes_personnel_administration->personnelAdministration->departement ?? 'Non renseigné' }}</span>
                                </div>
                                <div style="margin-bottom: 1px;">
                                    <span style="font-weight: bold;">Téléphone:</span>
                                    <span>{{ $cartes_personnel_administration->personnelAdministration->utilisateur->telephone ?? 'Non renseigné' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pied de carte -->
                        <div style="margin-top: 2px; padding: 2px; border-top: 1px solid #d4af37; line-height: 1.1; display: flex; flex-direction: column; justify-content: center; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div style="background: #d4af37; color: white; padding: 1px 3px; border-radius: 2px; font-weight: bold; font-size: 7px;">
                                    N°: {{ $cartes_personnel_administration->numero_carte }}
                                </div>
                                <div style="color: #6c757d; font-size: 7px;">
                                    Exp: {{ $cartes_personnel_administration->date_expiration->format('m/Y') }}
                                </div>
                            </div>
                            <div class="text-center" style="font-size: 6px; color: #6c757d; margin-top: 1px;">
                                @if(!empty(trim($address)))
                                    <div style="margin-bottom: 1px;">{{ $address }}</div>
                                @endif
                                @if(!empty(trim($phone)))
                                    <div>Tél: {{ $phone }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations supplémentaires -->
                <div class="no-print mt-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Détails de la carte</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Numéro de carte:</strong> {{ $cartes_personnel_administration->numero_carte }}</p>
                                    <p><strong>Type:</strong> {{ $cartes_personnel_administration->type_carte_libelle }}</p>
                                    <p><strong>Date d'émission:</strong> {{ $cartes_personnel_administration->date_emission->format('d/m/Y') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Date d'expiration:</strong> {{ $cartes_personnel_administration->date_expiration->format('d/m/Y') }}</p>
                                    <p><strong>Statut:</strong> 
                                        <span class="badge bg-{{ $cartes_personnel_administration->statut === 'active' ? 'success' : ($cartes_personnel_administration->statut === 'expiree' ? 'danger' : 'warning') }}">
                                            {{ $cartes_personnel_administration->statut_libelle }}
                                        </span>
                                    </p>
                                    <p><strong>Émise par:</strong> {{ $cartes_personnel_administration->emisePar->nom ?? 'Non défini' }}</p>
                                </div>
                            </div>
                            @if($cartes_personnel_administration->observations)
                                <div class="mt-3">
                                    <p><strong>Observations:</strong></p>
                                    <p class="text-muted">{{ $cartes_personnel_administration->observations }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

