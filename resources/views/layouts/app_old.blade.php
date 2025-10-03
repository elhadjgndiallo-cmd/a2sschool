<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gestion Scolaire') - A2school</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* Menu horizontal en haut */
        .top-navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .top-navbar .nav-link {
            color: white !important;
            font-weight: 500;
            padding: 1rem 1.5rem;
            transition: all 0.3s ease;
            border-radius: 5px;
            margin: 0 2px;
        }
        
        .top-navbar .nav-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .top-navbar .nav-link.active {
            background: rgba(255, 255, 255, 0.3);
            font-weight: 600;
        }
        
        /* Menu latéral pour sous-menus */
        .sidebar {
            min-height: calc(100vh - 70px);
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-right: 1px solid #dee2e6;
            box-shadow: 2px 0 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .sidebar.collapsed {
            width: 0;
            overflow: hidden;
        }
        
        .sidebar .nav-link {
            color: #495057 !important;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover {
            background: #e9ecef;
            color: #212529 !important;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: #667eea;
            color: white !important;
        }
        
        /* Contenu principal */
        .main-content {
            margin-top: 70px;
            transition: all 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
        /* Styles généraux */
        .navbar-brand {
            font-weight: bold;
        }
        
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        
        .role-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        /* Animation pour les icônes */
        .nav-link i {
            transition: transform 0.3s ease;
        }
        
        .nav-link:hover i {
            transform: scale(1.1);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .top-navbar .nav-link {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            .sidebar {
                position: fixed;
                top: 70px;
                left: 0;
                width: 250px;
                z-index: 999;
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    @auth
    <!-- Menu horizontal en haut -->
    <nav class="navbar navbar-expand-lg top-navbar fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="{{ route('dashboard') }}">
                <i class="fas fa-graduation-cap me-2"></i>
                A2School
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="topNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}" data-menu="dashboard">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    
                    @if(auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-menu="enseignants">
                                <i class="fas fa-chalkboard-teacher me-1"></i>Enseignants
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-menu="eleves">
                                <i class="fas fa-user-graduate me-1"></i>Élèves
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('matieres.index') }}" data-menu="matieres">
                                <i class="fas fa-book me-1"></i>Matières
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('emplois-temps.index') }}" data-menu="emplois-temps">
                                <i class="fas fa-calendar-alt me-1"></i>Emplois du Temps
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-menu="notes">
                                <i class="fas fa-edit me-1"></i>Gestion Notes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('absences.index') }}" data-menu="absences">
                                <i class="fas fa-user-times me-1"></i>Absences
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-menu="comptabilite">
                                <i class="fas fa-calculator me-1"></i>Comptabilité
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-menu="parametres">
                                <i class="fas fa-cog me-1"></i>Paramètres
                            </a>
                        </li>
                    @endif
                    
                    @if(auth()->user()->isTeacher())
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-menu="mes-classes">
                                <i class="fas fa-clipboard-list me-1"></i>Mes Classes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('notes.index') }}" data-menu="saisir-notes">
                                <i class="fas fa-edit me-1"></i>Saisir Notes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('absences.index') }}" data-menu="saisir-absences">
                                <i class="fas fa-user-times me-1"></i>Saisir Absences
                            </a>
                        </li>
                    @endif
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            {{ auth()->user()->nom }} {{ auth()->user()->prenom }}
                            <span class="badge bg-light text-dark ms-2">{{ ucfirst(auth()->user()->role) }}</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profil</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Paramètres</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Menu latéral pour sous-menus -->
            <nav class="col-md-3 col-lg-2 sidebar" id="sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h6 class="text-muted">Sous-menus</h6>
                    </div>
                    
                    <!-- Sous-menus dynamiques -->
                    <ul class="nav flex-column" id="submenuContainer">
                        <!-- Le contenu sera chargé dynamiquement via JavaScript -->
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                @yield('content')
            </main>
        </div>
    </div>
                                <a class="nav-link text-white d-flex align-items-center justify-content-between" href="#" id="enseignantsToggle" role="button">
                                    <span>
                                        <i class="fas fa-chalkboard-teacher me-2"></i>
                                        Enseignants
                                    </span>
                                    <div class="hamburger-menu" id="enseignantsHamburger">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </a>
                                <ul class="nav flex-column ms-3 collapse" id="enseignantsSubmenu">
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('enseignants.create') }}">
                                            <i class="fas fa-user-plus me-2"></i>Inscription
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('enseignants.index') }}">
                                            <i class="fas fa-list me-2"></i>Liste
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('salaires.index') }}">
                                            <i class="fas fa-coins me-2"></i>Salaire
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white d-flex align-items-center justify-content-between" href="#" id="elevesToggle" role="button">
                                    <span>
                                    <i class="fas fa-user-graduate me-2"></i>
                                    Élèves
                                    </span>
                                    <div class="hamburger-menu" id="elevesHamburger">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </a>
                                <ul class="nav flex-column ms-3 collapse" id="elevesSubmenu">
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('eleves.create') }}">
                                            <i class="fas fa-user-plus me-2"></i>Inscription
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('eleves.reinscription') }}">
                                            <i class="fas fa-user-edit me-2"></i>Réinscription
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('eleves.index') }}">
                                            <i class="fas fa-list me-2"></i>Liste
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('cartes-scolaires.index') }}">
                                            <i class="fas fa-id-card me-2"></i>Cartes Scolaires
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <hr class="border-white-50 my-2">
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('notes.index') }}">
                                            <i class="fas fa-clipboard-list me-2"></i>Gestion Notes
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('paiements.index') }}">
                                            <i class="fas fa-credit-card me-2"></i>Gestion Paiements
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- Menu Paramètres -->
                            <li class="nav-item">
                                <a class="nav-link text-white d-flex align-items-center justify-content-between" href="#" id="parametresToggle" role="button">
                                    <span>
                                        <i class="fas fa-cogs me-2"></i>
                                        Paramètres
                                    </span>
                                    <div class="hamburger-menu" id="parametresHamburger">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </a>
                                <ul class="nav flex-column ms-3 collapse" id="parametresSubmenu">
                                    <!-- Établissement -->
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50 d-flex align-items-center justify-content-between" href="#" id="etablissementToggle" role="button">
                                            <span>
                                                <i class="fas fa-school me-2"></i>Établissement
                                            </span>
                                            <i class="fas fa-chevron-down ms-2" style="font-size: 0.8rem;"></i>
                                        </a>
                                        <ul class="nav flex-column ms-3 collapse" id="etablissementSubmenu">
                                            <li class="nav-item">
                                                <a class="nav-link text-white-50" href="{{ route('etablissement.informations') }}">
                                                    <i class="fas fa-info-circle me-2"></i>Informations
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link text-white-50" href="{{ route('etablissement.responsabilites') }}">
                                                    <i class="fas fa-users-cog me-2"></i>Responsabilités
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <!-- Année Scolaire -->
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('annees-scolaires.index') }}">
                                            <i class="fas fa-calendar-alt me-2"></i>Année Scolaire
                                        </a>
                                    </li>
                                    <!-- Classes -->
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('classes.index') }}">
                                            <i class="fas fa-chalkboard-teacher me-2"></i>Classes
                                        </a>
                                    </li>
                                    <!-- Tableau des Tarifs -->
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('tarifs.tableau') }}">
                                            <i class="fas fa-chart-line me-2"></i>Tableau des Tarifs
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('matieres.index') }}">
                                    <i class="fas fa-book me-2"></i>
                                    Matières
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('emplois-temps.index') }}">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Emplois du Temps
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('absences.index') }}">
                                    <i class="fas fa-user-times me-2"></i>
                                    Gestion Absences
                                </a>
                            </li>
                            <!-- Menu Comptabilité -->
                            <li class="nav-item">
                                <a class="nav-link text-white d-flex align-items-center justify-content-between" href="#" id="comptabiliteToggle" role="button">
                                    <span>
                                        <i class="fas fa-calculator me-2"></i>
                                        Comptabilité
                                    </span>
                                    <div class="hamburger-menu" id="comptabiliteHamburger">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </a>
                                <ul class="nav flex-column ms-3 collapse" id="comptabiliteSubmenu">
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('entrees.index') }}">
                                            <i class="fas fa-arrow-down me-2"></i>Entrée
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('depenses.index') }}">
                                            <i class="fas fa-arrow-up me-2"></i>Sortie
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('rapports.index') }}">
                                            <i class="fas fa-chart-line me-2"></i>Rapport
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('tarifs.index') }}">
                                    <i class="fas fa-table me-2"></i>
                                    Tarifs par Classe
                                </a>
                            </li>
                        @endif
                        
                        @if(auth()->user()->isTeacher())
                            <li class="nav-item">
                                <a class="nav-link text-white" href="#">
                                    <i class="fas fa-clipboard-list me-2"></i>
                                    Mes Classes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('notes.index') }}">
                                    <i class="fas fa-edit me-2"></i>
                                    Saisir Notes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('absences.index') }}">
                                    <i class="fas fa-user-times me-2"></i>
                                    Saisir Absences
                                </a>
                            </li>
                        @endif
                        
                        @if(auth()->user()->isStudent())
                            <li class="nav-item">
                                <a class="nav-link text-white" href="#">
                                    <i class="fas fa-calendar me-2"></i>
                                    Emploi du Temps
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="#">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Mes Notes
                                </a>
                            </li>
                        @endif
                        
                        @if(auth()->user()->isParent())
                            <li class="nav-item">
                                <a class="nav-link text-white" href="#">
                                    <i class="fas fa-child me-2"></i>
                                    Mes Enfants
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('parent.paiements.index') }}">
                                    <i class="fas fa-credit-card me-2"></i>
                                    Paiements
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('parent.paiements.historique') }}">
                                    <i class="fas fa-history me-2"></i>
                                    Historique
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('parent.echeances') }}">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Échéances
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('parent.recapitulatif') }}">
                                    <i class="fas fa-chart-pie me-2"></i>
                                    Récapitulatif
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Top navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-4">
                    <div class="container-fluid">
                        <span class="navbar-text">
                            Bonjour, <strong>{{ auth()->user()->name }}</strong>
                        </span>
                        
                        <div class="navbar-nav ms-auto">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-sign-out-alt me-1"></i>
                                    Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </nav>

                @yield('content')
            </main>
        </div>
    </div>
    @else
        @yield('content')
    @endauth

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script pour les menus hamburger -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Menu Enseignants
            const enseignantsToggle = document.getElementById('enseignantsToggle');
            const enseignantsHamburger = document.getElementById('enseignantsHamburger');
            const enseignantsSubmenu = document.getElementById('enseignantsSubmenu');
            
            if (enseignantsToggle && enseignantsHamburger && enseignantsSubmenu) {
                enseignantsToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    enseignantsHamburger.classList.toggle('active');
                    if (enseignantsSubmenu.classList.contains('show')) {
                        enseignantsSubmenu.classList.remove('show');
                    } else {
                        enseignantsSubmenu.classList.add('show');
                    }
                });
            }

            // Menu Élèves
            const elevesToggle = document.getElementById('elevesToggle');
            const elevesHamburger = document.getElementById('elevesHamburger');
            const elevesSubmenu = document.getElementById('elevesSubmenu');
            
            if (elevesToggle && elevesHamburger && elevesSubmenu) {
                elevesToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    elevesHamburger.classList.toggle('active');
                    if (elevesSubmenu.classList.contains('show')) {
                        elevesSubmenu.classList.remove('show');
                    } else {
                        elevesSubmenu.classList.add('show');
                    }
                });
            }

            // Menu Comptabilité
            const comptabiliteToggle = document.getElementById('comptabiliteToggle');
            const comptabiliteHamburger = document.getElementById('comptabiliteHamburger');
            const comptabiliteSubmenu = document.getElementById('comptabiliteSubmenu');
            
            if (comptabiliteToggle && comptabiliteHamburger && comptabiliteSubmenu) {
                comptabiliteToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    comptabiliteHamburger.classList.toggle('active');
                    if (comptabiliteSubmenu.classList.contains('show')) {
                        comptabiliteSubmenu.classList.remove('show');
                    } else {
                        comptabiliteSubmenu.classList.add('show');
                    }
                });
            }

            // Menu Paramètres
            const parametresToggle = document.getElementById('parametresToggle');
            const parametresHamburger = document.getElementById('parametresHamburger');
            const parametresSubmenu = document.getElementById('parametresSubmenu');
            
            if (parametresToggle && parametresHamburger && parametresSubmenu) {
                parametresToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    parametresHamburger.classList.toggle('active');
                    if (parametresSubmenu.classList.contains('show')) {
                        parametresSubmenu.classList.remove('show');
                    } else {
                        parametresSubmenu.classList.add('show');
                    }
                });
            }

            // Sous-menu Établissement
            const etablissementToggle = document.getElementById('etablissementToggle');
            const etablissementSubmenu = document.getElementById('etablissementSubmenu');
            
            if (etablissementToggle && etablissementSubmenu) {
                etablissementToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const chevron = this.querySelector('.fa-chevron-down');
                    if (etablissementSubmenu.classList.contains('show')) {
                        etablissementSubmenu.classList.remove('show');
                        chevron.style.transform = 'rotate(0deg)';
                    } else {
                        etablissementSubmenu.classList.add('show');
                        chevron.style.transform = 'rotate(180deg)';
                    }
                });
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
