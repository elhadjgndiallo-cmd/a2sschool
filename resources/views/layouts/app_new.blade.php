<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gestion Scolaire') - A2school</title>
    
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
                A2school
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
                    
                    @if(auth()->user()->canAccessAdmin())
                        @if(auth()->user()->hasPermission('enseignants.view') || auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-menu="enseignants">
                                <i class="fas fa-chalkboard-teacher me-1"></i>Enseignants
                            </a>
                        </li>
                        @endif
                        
                        @if(auth()->user()->hasPermission('eleves.view') || auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-menu="eleves">
                                <i class="fas fa-user-graduate me-1"></i>Élèves
                            </a>
                        </li>
                        @endif
                        
                        @if(auth()->user()->hasPermission('matieres.view') || auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('matieres.index') }}" data-menu="matieres">
                                <i class="fas fa-book me-1"></i>Matières
                            </a>
                        </li>
                        @endif
                        
                        @if(auth()->user()->hasPermission('emplois-temps.view') || auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('emplois-temps.index') }}" data-menu="emplois-temps">
                                <i class="fas fa-calendar-alt me-1"></i>Emplois du Temps
                            </a>
                        </li>
                        @endif
                        
                        @if(auth()->user()->hasPermission('notes.view') || auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-menu="notes">
                                <i class="fas fa-edit me-1"></i>Gestion Notes
                            </a>
                        </li>
                        @endif
                        
                        @if(auth()->user()->hasPermission('absences.view') || auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('absences.index') }}" data-menu="absences">
                                <i class="fas fa-user-times me-1"></i>Absences
                            </a>
                        </li>
                        @endif
                        
                        @if(auth()->user()->hasPermission('paiements.view') || auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-menu="comptabilite">
                                <i class="fas fa-calculator me-1"></i>Comptabilité
                            </a>
                        </li>
                        @endif
                        
                        @if(auth()->user()->hasPermission('classes.view') || auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-menu="parametres">
                                <i class="fas fa-cog me-1"></i>Paramètres
                            </a>
                        </li>
                        @endif
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

    @else
    <!-- Contenu pour les utilisateurs non connectés -->
    <div class="container">
        @yield('content')
    </div>
    @endauth

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script pour la gestion des menus -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Définition des sous-menus avec permissions
            const submenus = {
                'enseignants': @json(\App\Helpers\PermissionHelper::getFilteredSubmenus('enseignants')),
                'eleves': @json(\App\Helpers\PermissionHelper::getFilteredSubmenus('eleves')),
                'notes': @json(\App\Helpers\PermissionHelper::getFilteredSubmenus('notes')),
                'comptabilite': @json(\App\Helpers\PermissionHelper::getFilteredSubmenus('comptabilite')),
                'parametres': @json(\App\Helpers\PermissionHelper::getFilteredSubmenus('parametres'))
            };

            // Gestion des clics sur les menus principaux
            document.querySelectorAll('[data-menu]').forEach(link => {
                link.addEventListener('click', function(e) {
                    const menuType = this.getAttribute('data-menu');
                    
                    // Si c'est un lien direct (pas de sous-menu), ne pas empêcher le comportement par défaut
                    if (this.getAttribute('href') && this.getAttribute('href') !== '#') {
                        return;
                    }
                    
                    e.preventDefault();
                    
                    // Retirer la classe active de tous les liens
                    document.querySelectorAll('.top-navbar .nav-link').forEach(l => l.classList.remove('active'));
                    
                    // Ajouter la classe active au lien cliqué
                    this.classList.add('active');
                    
                    // Charger les sous-menus
                    loadSubmenus(menuType);
                });
            });

            // Fonction pour charger les sous-menus
            function loadSubmenus(menuType) {
                const container = document.getElementById('submenuContainer');
                
                if (submenus[menuType]) {
                    container.innerHTML = submenus[menuType].map(item => `
                        <li class="nav-item">
                            <a class="nav-link" href="${item.href}">
                                <i class="${item.icon} me-2"></i>${item.text}
                            </a>
                        </li>
                    `).join('');
                } else {
                    container.innerHTML = '<li class="nav-item"><span class="nav-link text-muted">Aucun sous-menu disponible</span></li>';
                }
            }

            // Charger le menu par défaut (Dashboard)
            loadSubmenus('dashboard');
        });
    </script>

    @stack('scripts')
</body>
</html>






