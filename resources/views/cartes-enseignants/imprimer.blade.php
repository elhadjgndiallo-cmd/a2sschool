<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carte Enseignant - {{ $cartes_enseignant->enseignant->utilisateur->nom }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    @php
    use Illuminate\Support\Facades\Storage;
    @endphp
    <style>
        @media print {
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
            font-size: 6px;
            font-weight: bold;
            text-align: center;
        }
        
        .card-body {
            padding: 6px;
            height: calc(100% - 12px);
            display: flex;
            flex-direction: column;
        }
        
        .enseignant-info {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .photo-container {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #d4af37;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
            flex-shrink: 0;
        }
        
        .enseignant-details {
            flex: 1;
            font-size: 9px;
        }
        
        .enseignant-name {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 2px;
        }
        
        .enseignant-meta {
            color: #6c757d;
            font-size: 8px;
        }
        
        .qr-container {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }
        
        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8px;
            color: #6c757d;
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px solid #dee2e6;
        }
        
        .numero-carte {
            font-weight: bold;
            color: #d4af37;
        }
        
        .date-expiration {
            color: #dc3545;
        }
        
        .status-badge {
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        
        .status-active {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .status-expiree {
            background: #f8d7da;
            color: #842029;
        }
        
        .status-suspendue {
            background: #fff3cd;
            color: #664d03;
        }
        
        .status-annulee {
            background: #d1d3d4;
            color: #495057;
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
                    <a href="{{ route('cartes-enseignants.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>

                <!-- Carte enseignant style Guinée -->
                <div class="card-container">
                    <!-- En-tête avec drapeau et devise -->
                    <div class="card-header text-center">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <!-- Logo de l'école à gauche -->
                            <div style="width: 20px; height: 20px; background: #d4af37; border-radius: 3px; display: flex; align-items: center; justify-content: center; color: white; font-size: 8px; font-weight: bold;">
                                GSHFD
                            </div>
                            
                            <!-- Drapeau au centre -->
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="bg-danger me-1" style="width: 15px; height: 8px;"></div>
                                <div class="bg-warning me-1" style="width: 15px; height: 8px;"></div>
                                <div class="bg-success" style="width: 15px; height: 8px;"></div>
                            </div>
                            
                            <!-- Espace à droite pour équilibrer -->
                            <div style="width: 20px;"></div>
                        </div>
                        <div style="font-size: 5px; font-weight: bold;">RÉPUBLIQUE DE GUINÉE</div>
                        <div style="font-size: 3px; font-weight: bold;">TRAVAIL - JUSTICE - SOLIDARITÉ</div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Nom de l'école -->
                        <div class="text-left mb-1">
                            <div style="font-size: 6px; font-weight: bold; color: #d4af37;">ÉCOLE GSHFD</div>
                            <div style="font-size: 4px; color: #6c757d;">CARTE D'ENSEIGNANT</div>
                        </div>
                        
                        <!-- Contenu principal -->
                        <div class="d-flex" style="height: 100%; gap: 8px;">
                            <!-- Photo de l'enseignant - Gauche -->
                            <div style="width: 40px; height: 50px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border: 2px solid #d4af37; border-radius: 4px; overflow: hidden;">
                                @if($cartes_enseignant->enseignant->utilisateur->photo_profil)
                                    <img src="{{ asset('images/profile_images/' . basename($cartes_enseignant->enseignant->utilisateur->photo_profi)) }}" 
                                         alt="Photo enseignant" 
                                         style="width: 100%; height: 100%; object-fit: cover;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%); display: none; align-items: center; justify-content: center; color: white; font-size: 14px; font-weight: bold;">
                                        {{ substr($cartes_enseignant->enseignant->utilisateur->nom, 0, 1) }}{{ substr($cartes_enseignant->enseignant->utilisateur->prenom, 0, 1) }}
                                    </div>
                                @else
                                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 14px; font-weight: bold;">
                                        {{ substr($cartes_enseignant->enseignant->utilisateur->nom, 0, 1) }}{{ substr($cartes_enseignant->enseignant->utilisateur->prenom, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Informations de l'enseignant - Droite -->
                            <div style="flex: 1; font-size: 6px; line-height: 1.4; display: flex; flex-direction: column; justify-content: center;">
                                <div class="mb-1">
                                    <span style="font-weight: bold;">Nom:</span>
                                    <span>{{ $cartes_enseignant->enseignant->utilisateur->nom }}</span>
                                </div>
                                <div class="mb-1">
                                    <span style="font-weight: bold;">Prénom:</span>
                                    <span>{{ $cartes_enseignant->enseignant->utilisateur->prenom }}</span>
                                </div>
                                <div class="mb-1">
                                    <span style="font-weight: bold;">Spécialité:</span>
                                    <span>{{ $cartes_enseignant->enseignant->specialite ?? 'Non renseigné' }}</span>
                                </div>
                                <div class="mb-1">
                                    <span style="font-weight: bold;">N° Employé:</span>
                                    <span>{{ $cartes_enseignant->enseignant->numero_employe }}</span>
                                </div>
                                <div class="mb-1">
                                    <span style="font-weight: bold;">Téléphone:</span>
                                    <span>{{ $cartes_enseignant->enseignant->utilisateur->telephone ?? 'Non renseigné' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pied de carte -->
                        <div style="margin-top: 4px; padding: 4px; border-top: 1px solid #d4af37; font-size: 6px; line-height: 1.2; height: 30%; display: flex; flex-direction: column; justify-content: center; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <div class="d-flex justify-content-between align-items-center">
                                <div style="background: #d4af37; color: white; padding: 2px 4px; border-radius: 3px; font-weight: bold;">
                                    N°: {{ $cartes_enseignant->numero_carte }}
                                </div>
                                <div style="color: #6c757d; font-size: 5px;">
                                    Exp: {{ $cartes_enseignant->date_expiration->format('m/Y') }}
                                </div>
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
                                    <p><strong>Numéro de carte:</strong> {{ $cartes_enseignant->numero_carte }}</p>
                                    <p><strong>Type:</strong> {{ $cartes_enseignant->type_carte_libelle }}</p>
                                    <p><strong>Date d'émission:</strong> {{ $cartes_enseignant->date_emission->format('d/m/Y') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Date d'expiration:</strong> {{ $cartes_enseignant->date_expiration->format('d/m/Y') }}</p>
                                    <p><strong>Statut:</strong> 
                                        <span class="status-badge status-{{ $cartes_enseignant->statut }}">
                                            {{ $cartes_enseignant->statut_libelle }}
                                        </span>
                                    </p>
                                    <p><strong>Émise par:</strong> {{ $cartes_enseignant->emisePar->nom ?? 'Non défini' }}</p>
                                </div>
                            </div>
                            @if($cartes_enseignant->observations)
                                <div class="mt-3">
                                    <p><strong>Observations:</strong></p>
                                    <p class="text-muted">{{ $cartes_enseignant->observations }}</p>
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


