@php use Illuminate\Support\Facades\Storage; @endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>@yield('title', 'Gestion Scolaire') - A2school</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- CSS Responsive personnalisé -->
    <link href="{{ asset('css/responsive.css') }}" rel="stylesheet">
    <!-- CSS pour l'affichage des images -->
    <link href="{{ asset('css/image-display.css') }}" rel="stylesheet">
    <!-- CSS pour la pagination -->
    <link href="{{ asset('css/pagination.css') }}" rel="stylesheet">
    <!-- CSS pour la pagination simple -->
    <link href="{{ asset('css/pagination-simple.css') }}" rel="stylesheet">
    
    <style>
        /* Réduction de la taille des boutons */
        .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
        }
        
        .btn-sm {
            padding: 0.2rem 0.4rem;
            font-size: 0.75rem;
            line-height: 1.4;
        }
        
        .btn-group-sm > .btn,
        .btn-group-sm .btn {
            padding: 0.2rem 0.4rem;
            font-size: 0.75rem;
            line-height: 1.4;
        }
        
        .btn i {
            font-size: 0.875rem;
        }
        
        .btn-sm i {
            font-size: 0.75rem;
        }
        
        /* Boutons dans les tableaux */
        .table .btn {
            padding: 0.2rem 0.4rem;
            font-size: 0.75rem;
        }
        
        .table .btn i {
            font-size: 0.75rem;
        }
        
        /* Règles globales pour empêcher les gros chevrons */
        .pagination i,
        .pagination .fas,
        .pagination .fa,
        .pagination .fa-chevron-left,
        .pagination .fa-chevron-right {
            font-size: 0.75rem !important;
            max-width: 0.75rem !important;
            max-height: 0.75rem !important;
            width: 0.75rem !important;
            height: 0.75rem !important;
            line-height: 1 !important;
            display: inline-block !important;
            vertical-align: middle !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            background: none !important;
            box-shadow: none !important;
            transform: none !important;
        }
        
        /* Empêcher tout élément de pagination de devenir trop grand */
        .pagination * {
            max-width: 200px !important;
            box-sizing: border-box !important;
        }
        
        /* Règles ultra-strictes pour empêcher les gros chevrons */
        .pagination i,
        .pagination .fas,
        .pagination .fa,
        .pagination .fa-chevron-left,
        .pagination .fa-chevron-right,
        .pagination [class*="fa-"],
        .pagination [class*="fas"],
        .pagination [class*="fa "] {
            font-size: 0.75rem !important;
            width: 0.75rem !important;
            height: 0.75rem !important;
            max-width: 0.75rem !important;
            max-height: 0.75rem !important;
            line-height: 1 !important;
            display: inline-block !important;
            vertical-align: middle !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            background: none !important;
            box-shadow: none !important;
            transform: none !important;
            text-decoration: none !important;
            overflow: hidden !important;
        }
        
        /* Menu horizontal en haut */
        .top-navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* couleur de fond du menu horizontal */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 100;
            min-height: 60px;
        }
        
        .top-navbar .nav-link {
            color: white !important;
            font-weight: 500;
            padding: 0.5rem 0.75rem;
            transition: all 0.3s ease;
            border-radius: 5px;
            margin: 0 1px;
            font-size: 0.9rem;
        }
        
        .top-navbar .nav-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .top-navbar .nav-link.active {
            background: rgba(255, 255, 255, 0.3);
            font-weight: 600;
        }
        
        .top-navbar .nav-link i {
            font-size: 0.85rem;
        }
        
        /* Menu latéral pour sous-menus */
        .sidebar {
            min-height: calc(100vh - 60px);
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); /* couleur de fond du menu latéral */
            border-right: 2px solid #e9ecef;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            padding: 20px 0;
            position: sticky;
            top: 60px;
            align-self: flex-start;
        }
        
        .sidebar.collapsed {
            width: 0;
            overflow: hidden;
        }
        
        .sidebar .nav-link {
            color: #495057 !important;
            padding: 1rem 2rem;
            border-radius: 10px;
            margin: 5px 15px;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            font-weight: 500;
            position: relative;
        }
        
        .sidebar .nav-link:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* couleur de fond du menu latéral */
            color: white !important;
            transform: translateX(10px);
            border-left: 4px solid #ffffff;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            border-left: 4px solid #ffffff;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            font-size: 1.1em;
        }
        
        /* Contenu principal */
        .main-content {
            margin-top: 0;
            padding-top: 70px; /* Espace pour la navbar fixe */
            transition: all 0.3s ease;
            padding: 70px 20px 20px 20px;
            background: #ffffff;
            min-height: calc(100vh - 60px);
            margin-left: 0;
            position: relative;
            z-index: 1;
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
        /* Ajustements pour le contenu */
        .main-content .container-fluid {
            padding: 20px;
        }
        
        .main-content .card {
            margin-bottom: 20px;
        }
        
        /* Styles généraux */
        .navbar-brand {
            font-weight: bold;
        }
        
        /* Z-index pour les éléments de contenu (ne pas toucher aux modals Bootstrap) */
        .card, .dropdown-menu, .tooltip, .popover {
            position: relative;
            z-index: 10;
        }
        
        /* Empêcher le dropdown du profil d'affecter le sidebar */
        .navbar-nav .nav-item.dropdown {
            position: relative;
        }
        
        /* Style amélioré pour le dropdown du profil */
        #profileDropdownMenu {
            position: absolute;
            right: 0;
            left: auto;
            margin-top: 0.5rem;
            min-width: 220px;
            background: #ffffff !important;
            border: 2px solid #667eea !important;
            border-radius: 10px !important;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3) !important;
            padding: 8px 0 !important;
            z-index: 1050 !important;
            animation: dropdownFadeIn 0.3s ease-out;
        }
        
        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Style des items du dropdown */
        #profileDropdownMenu .dropdown-item {
            padding: 12px 20px !important;
            color: #495057 !important;
            font-weight: 500;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            margin: 2px 8px;
            border-radius: 6px;
        }
        
        #profileDropdownMenu .dropdown-item:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: #ffffff !important;
            border-left: 3px solid #ffffff;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        #profileDropdownMenu .dropdown-item i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
            font-size: 1rem;
        }
        
        /* Style du séparateur */
        #profileDropdownMenu .dropdown-divider {
            margin: 8px 12px;
            border-top: 1px solid #e9ecef;
            opacity: 0.5;
        }
        
        /* Style spécial pour le bouton de déconnexion */
        #profileDropdownMenu #logoutBtn,
        #profileDropdownMenu #logoutForm button {
            color: #dc3545 !important;
            font-weight: 600;
        }
        
        #profileDropdownMenu #logoutBtn:hover,
        #profileDropdownMenu #logoutForm button:hover {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
            color: #ffffff !important;
            border-left: 3px solid #ffffff;
        }
        
        /* Style du toggle du dropdown */
        #profileDropdownToggle {
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 8px 12px !important;
        }
        
        #profileDropdownToggle:hover {
            background: rgba(255, 255, 255, 0.2) !important;
            transform: translateY(-2px);
        }
        
        /* Empêcher que le dropdown déclenche le collapse de la navbar */
        #profileDropdown,
        #profileDropdown * {
            pointer-events: auto;
        }
        
        /* Empêcher que le collapse se déclenche quand on clique sur le dropdown */
        .navbar-collapse:has(#profileDropdown.show) {
            /* Ne pas fermer le collapse si le dropdown est ouvert */
        }
        
        /* Laisser Bootstrap gérer la position/z-index des modals */
        
        /* Désactiver l'assombrissement (gris) du fond quand un modal s'affiche */
        .modal-backdrop.show {
            opacity: 0 !important; /* pas d'opacité -> pas de gris */
            background: transparent !important;
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
            .top-navbar {
                min-height: 60px;
            }
            
            .top-navbar .nav-link {
                padding: 0.4rem 0.5rem;
                font-size: 0.8rem;
                margin: 0 1px;
            }
            
            .top-navbar .navbar-brand {
                font-size: 1rem;
            }
            
            /* Ajuster le padding-top pour la navbar sur mobile */
            .main-content {
                margin-left: 0 !important;
                padding-top: 80px !important; /* Plus d'espace pour la navbar dépliée */
                padding: 80px 10px 10px 10px;
            }
            
            .main-content .container-fluid {
                padding: 10px;
            }
            
            .sidebar {
                position: fixed;
                top: 60px;
                left: 0;
                width: 280px;
                z-index: 99;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            /* Overlay pour fermer le sidebar */
            .sidebar-overlay {
                position: fixed;
                top: 60px;
                left: 0;
                width: 100%;
                height: calc(100vh - 60px);
                background: rgba(0, 0, 0, 0.5);
                z-index: 98;
                display: none;
            }
            
            .sidebar-overlay.show {
                display: block;
            }
            
            /* Quand la navbar est dépliée, augmenter le padding */
            .navbar-collapse.show ~ * .main-content,
            .navbar-collapse.collapsing ~ * .main-content {
                padding-top: 120px !important;
            }
        }
        
        @media (max-width: 576px) {
            .top-navbar {
                min-height: 56px;
            }
            
            .top-navbar .nav-link {
                padding: 0.3rem 0.4rem;
                font-size: 0.75rem;
            }
            
            .top-navbar .navbar-brand {
                font-size: 0.9rem;
            }
            
            /* Encore plus d'espace sur très petit écran pour la navbar dépliée */
            .main-content {
                padding-top: 120px !important; /* Espace pour navbar + menu déplié */
                padding: 120px 5px 5px 5px;
            }
            
            .main-content .container-fluid {
                padding: 5px;
            }
            
            .sidebar {
                width: 100%;
                top: 56px;
            }
            
            /* Quand la navbar est dépliée sur très petit écran */
            .navbar-collapse.show ~ * .main-content,
            .navbar-collapse.collapsing ~ * .main-content {
                padding-top: 200px !important;
            }
        }
            
            /* Améliorer les dropdowns sur mobile */
            .dropdown-menu {
                position: static !important;
                transform: none !important;
                width: 100%;
                border: none;
                box-shadow: none;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
            }
            
            .dropdown-item {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
            
            /* Style spécifique pour le dropdown du profil sur mobile */
            #profileDropdownMenu {
                position: absolute !important;
                right: 0 !important;
                left: auto !important;
                min-width: 200px !important;
                max-width: 90vw !important;
                margin-top: 0.5rem !important;
                border: 2px solid #667eea !important;
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4) !important;
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
            
            <!-- Bouton pour le menu mobile -->
            <button class="btn btn-outline-light d-md-none me-2" type="button" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNavbar" id="navbarToggler">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="topNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}" data-menu="dashboard">
                            <i class="fas fa-home me-1"></i>Accueil
                        </a>
                    </li>
                    
                    @if(auth()->user()->canAccessAdmin())
                        @if(auth()->user()->hasPermission('enseignants.view'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('enseignants.index') }}" data-menu="enseignants">
                                <i class="fas fa-chalkboard-teacher me-1"></i>Enseignants
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('classes.view'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('classes.index') }}" data-menu="classes">
                                <i class="fas fa-school me-1"></i>Classes
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('eleves.view'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('eleves.index') }}" data-menu="eleves">
                                <i class="fas fa-user-graduate me-1"></i>Élèves
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('matieres.view'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('matieres.index') }}" data-menu="matieres">
                                <i class="fas fa-book me-1"></i>Matières
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('emplois-temps.view'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('emplois-temps.index') }}" data-menu="emplois-temps">
                                <i class="fas fa-calendar-alt me-1"></i>Emplois du Temps
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('notes.view'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('notes.index') }}" data-menu="notes">
                                <i class="fas fa-edit me-1"></i>Gestion Notes
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('absences.view'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('absences.index') }}" data-menu="absences">
                                <i class="fas fa-user-times me-1"></i>Absences
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('comptabilite.view'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('comptabilite.index') }}" data-menu="comptabilite">
                                <i class="fas fa-calculator me-1"></i>Comptabilité
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPermission('messages.view'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.notifications.index') }}" data-menu="admin-messages">
                                <i class="fas fa-envelope me-1"></i>Messages Parents
                            </a>
                        </li>
                        @endif
                    @endif
                    
                    
                    <!-- Menu Profil visible pour tous les utilisateurs avec permissions -->
                    @if(auth()->user()->canAccessAdmin() && auth()->user()->hasPermission('etablissement.view'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('etablissement.informations') }}" data-menu="parametres">
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
                    @endif
                    
                    @if(auth()->user()->isParent())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('parent.notes.index') }}" data-menu="parent-notes">
                                <i class="fas fa-chart-line me-1"></i>Notes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('parent.absences.index') }}" data-menu="parent-absences">
                                <i class="fas fa-exclamation-triangle me-1"></i>Absences
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('parent.paiements.index') }}" data-menu="parent-paiements">
                                <i class="fas fa-credit-card me-1"></i>Paiements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('parent.notifications.index') }}" data-menu="parent-notifications">
                                <i class="fas fa-envelope me-1"></i>Messages
                            </a>
                        </li>
                    @endif
                </ul>
                
                <ul class="navbar-nav">
                    <!-- Notifications -->
                    <li class="nav-item dropdown">
                        <a class="nav-link text-white position-relative" href="{{ route('notifications.index') }}" title="Notifications">
                            <i class="fas fa-bell"></i>
                            <span class="notification-counter badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill" style="display: none;">0</span>
                        </a>
                    </li>
                    
                    <!-- Profil utilisateur -->
                    <li class="nav-item dropdown" id="profileDropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="true" id="profileDropdownToggle">
                            @if(auth()->user()->photo_profil && Storage::disk('public')->exists(auth()->user()->photo_profil))
                                <img src="{{ asset('storage/' . auth()->user()->photo_profil) }}" 
                                     alt="Photo de profil" 
                                     class="rounded-circle me-2" 
                                     style="width: 32px; height: 32px; object-fit: cover;">
                            @else
                                <i class="fas fa-user me-1"></i>
                            @endif
                            {{ auth()->user()->nom }} {{ auth()->user()->prenom }}
                            <span class="badge bg-light text-dark ms-2">{{ ucfirst(auth()->user()->role) }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" id="profileDropdownMenu">
                            @if(auth()->user()->canAccessAdmin() && auth()->user()->hasPermission('etablissement.view'))
                            <li><a class="dropdown-item" href="{{ route('etablissement.informations') }}"><i class="fas fa-cog me-2"></i>Paramètres</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('password.change.form') }}"><i class="fas fa-key me-2"></i>Changer le mot de passe</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" id="logoutForm" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="dropdown-item" id="logoutBtn">
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

    <!-- Overlay pour mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Menu latéral pour sous-menus -->
            <nav class="col-md-3 col-lg-2 sidebar" id="sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-primary fw-bold mb-0">
                            <i class="fas fa-list-ul me-2"></i>
                            Sous-menus
                        </h5>
                        <hr class="my-3" style="border-color: #667eea; opacity: 0.3;">
                    </div>
                    
                    <!-- Sous-menus dynamiques -->
                    <ul class="nav flex-column" id="submenuContainer">
                        <!-- Le contenu sera chargé dynamiquement via JavaScript -->
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
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
            // Charger le compteur de notifications
            loadNotificationCounter();
            
            // Gestion du sidebar mobile
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            if (sidebarToggle && sidebar && sidebarOverlay) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                });
                
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                });
                
                // Fermer le sidebar lors du clic sur un lien
                sidebar.addEventListener('click', function(e) {
                    if (e.target.tagName === 'A') {
                        sidebar.classList.remove('show');
                        sidebarOverlay.classList.remove('show');
                    }
                });
            }
            
            // Définition des sous-menus avec permissions
            const submenus = {
                'dashboard': [
                    { href: '{{ route("dashboard") }}', icon: 'fas fa-home', text: 'Accueil' },
                    ...@json(\App\Helpers\PermissionHelper::getFilteredSubmenus('rapports')),
                    { href: '{{ route("notifications.index") }}', icon: 'fas fa-bell', text: 'Notifications' }
                ],
                'enseignants': @json(\App\Helpers\PermissionHelper::getFilteredSubmenus('enseignants')),
                'eleves': @json(\App\Helpers\PermissionHelper::getFilteredSubmenus('eleves')),
                'notes': @json(\App\Helpers\PermissionHelper::getFilteredSubmenus('notes')),
                'comptabilite': [
                    { href: '{{ route("comptabilite.index") }}', icon: 'fas fa-tachometer-alt', text: 'Tableau de bord' },
                    { href: '{{ route("comptabilite.entrees") }}', icon: 'fas fa-arrow-up', text: 'Entrées' },
                    { href: '{{ route("comptabilite.sorties") }}', icon: 'fas fa-arrow-down', text: 'Sorties' },
                    { href: '{{ route("recus-rappel.index") }}', icon: 'fas fa-bell', text: 'Reçus de Rappel' }
                ],
                'rapports': @json(\App\Helpers\PermissionHelper::getFilteredSubmenus('rapports')),
                'cartes': @json(\App\Helpers\PermissionHelper::getFilteredSubmenus('cartes')),
                'parametres': @json(\App\Helpers\PermissionHelper::getFilteredSubmenus('parametres')),
                'matieres': [
                    { href: '{{ route("matieres.index") }}', icon: 'fas fa-list', text: 'Liste des Matières' },
                    { href: '{{ route("matieres.create") }}', icon: 'fas fa-plus', text: 'Ajouter Matière' }
                ],
                'emplois-temps': [
                    { href: '{{ route("emplois-temps.index") }}', icon: 'fas fa-list', text: 'Liste des Emplois' },
                    { href: '{{ route("emplois-temps.index") }}', icon: 'fas fa-plus', text: 'Gérer Emplois' }
                ],
                'absences': [
                    { href: '{{ route("absences.index") }}', icon: 'fas fa-list', text: 'Liste des Absences' },
                    { href: '{{ route("absences.index") }}', icon: 'fas fa-plus', text: 'Gérer Absences' }
                ],
                'emploi-temps': [
                    { href: '{{ route("teacher.emploi-temps") }}', icon: 'fas fa-calendar-alt', text: 'Mon Emploi du Temps' }
                ],
                'mes-classes': [
                    { href: '{{ route("teacher.classes") }}', icon: 'fas fa-chalkboard-teacher', text: 'Mes Classes' },
                    { href: '{{ route("teacher.classes") }}', icon: 'fas fa-users', text: 'Mes Élèves' }
                ],
                'saisir-notes': [
                    { href: '{{ route("teacher.classes") }}', icon: 'fas fa-edit', text: 'Saisir Notes' },
                    { href: '{{ route("teacher.classes") }}', icon: 'fas fa-chart-line', text: 'Historique Notes' }
                ],
                'saisir-absences': [
                    { href: '{{ route("teacher.classes") }}', icon: 'fas fa-calendar-times', text: 'Saisir Absences' },
                    { href: '{{ route("teacher.classes") }}', icon: 'fas fa-history', text: 'Historique Absences' }
                ],
                'parent-notes': [
                    { href: '{{ route("parent.notes.index") }}', icon: 'fas fa-list', text: 'Toutes les Notes' },
                    { href: '{{ route("parent.notes.index") }}', icon: 'fas fa-chart-line', text: 'Bulletins' }
                ],
                'parent-absences': [
                    { href: '{{ route("parent.absences.index") }}', icon: 'fas fa-list', text: 'Toutes les Absences' },
                    { href: '{{ route("parent.absences.index") }}', icon: 'fas fa-exclamation-triangle', text: 'Justifications' }
                ],
                'parent-paiements': [
                    { href: '{{ route("parent.paiements.index") }}', icon: 'fas fa-list', text: 'Mes Paiements' },
                    { href: '{{ route("parent.paiements.historique") }}', icon: 'fas fa-history', text: 'Historique' },
                    { href: '{{ route("parent.echeances") }}', icon: 'fas fa-calendar-alt', text: 'Échéances' },
                    { href: '{{ route("parent.recapitulatif") }}', icon: 'fas fa-chart-pie', text: 'Récapitulatif' }
                ],
                'parent-notifications': [
                    { href: '{{ route("parent.notifications.index") }}', icon: 'fas fa-list', text: 'Mes Messages' },
                    { href: '{{ route("parent.notifications.create") }}', icon: 'fas fa-plus', text: 'Nouveau Message' }
                ],
                'admin-messages': [
                    { href: '{{ route("admin.notifications.index") }}', icon: 'fas fa-list', text: 'Messages des Parents' },
                    { href: '{{ route("admin.notifications.statistiques") }}', icon: 'fas fa-chart-bar', text: 'Statistiques' }
                ],
            };

            // Gestion des clics sur les menus principaux
            document.querySelectorAll('[data-menu]').forEach(link => {
                link.addEventListener('click', function(e) {
                    // Ne pas traiter les clics sur les dropdowns
                    if (this.closest('.dropdown') || this.classList.contains('dropdown-toggle')) {
                        return;
                    }
                    
                    const menuType = this.getAttribute('data-menu');
                    
                    // Si c'est un lien direct (pas de sous-menu), ne pas empêcher le comportement par défaut
                    if (this.getAttribute('href') && this.getAttribute('href') !== '#') {
                        // Retirer la classe active de tous les liens
                        document.querySelectorAll('.top-navbar .nav-link').forEach(l => l.classList.remove('active'));
                        
                        // Ajouter la classe active au lien cliqué
                        this.classList.add('active');
                        
                        // Sauvegarder le menu actif
                        saveActiveMenu(menuType);
                        
                        // Charger les sous-menus correspondants
                        loadSubmenus(menuType);
                        return;
                    }
                    
                    e.preventDefault();
                    
                    // Retirer la classe active de tous les liens
                    document.querySelectorAll('.top-navbar .nav-link').forEach(l => l.classList.remove('active'));
                    
                    // Ajouter la classe active au lien cliqué
                    this.classList.add('active');
                    
                    // Sauvegarder le menu actif
                    saveActiveMenu(menuType);
                    
                    // Charger le premier sous-menu au lieu d'afficher juste les sous-menus
                    loadFirstSubmenu(menuType);
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

            // Charger le menu par défaut (Accueil) au chargement de la page
            loadSubmenus('dashboard');
            
            // Marquer le menu Accueil comme actif par défaut
            const dashboardLink = document.querySelector('[data-menu="dashboard"]');
            if (dashboardLink) {
                dashboardLink.classList.add('active');
            }
            
            // Fonction pour charger le premier sous-menu d'un menu
            function loadFirstSubmenu(menuType) {
                if (submenus[menuType] && submenus[menuType].length > 0) {
                    const firstSubmenu = submenus[menuType][0];
                    if (firstSubmenu.href && firstSubmenu.href !== '#') {
                        window.location.href = firstSubmenu.href;
                        
                    }
                }
            }
            
            // Sauvegarder l'état du menu actif dans le localStorage
            function saveActiveMenu(menuType) {
                localStorage.setItem('activeMenu', menuType);
            }
            
            // Restaurer l'état du menu actif depuis le localStorage
            function restoreActiveMenu() {
                const savedMenu = localStorage.getItem('activeMenu');
                if (savedMenu && submenus[savedMenu]) {
                    const menuLink = document.querySelector(`[data-menu="${savedMenu}"]`);
                    if (menuLink) {
                        // Retirer la classe active de tous les liens
                        document.querySelectorAll('.top-navbar .nav-link').forEach(l => l.classList.remove('active'));
                        
                        // Ajouter la classe active au menu sauvegardé
                        menuLink.classList.add('active');
                        
                        // Charger les sous-menus correspondants
                        loadSubmenus(savedMenu);
                    }
                }
            }
            
            // Restaurer le menu actif au chargement de la page
            restoreActiveMenu();
        });

        // Fonction pour charger le compteur de notifications
        function loadNotificationCounter() {
            fetch('{{ route("notifications.compteur-non-lues") }}')
                .then(response => response.json())
                .then(data => {
                    const counter = document.querySelector('.notification-counter');
                    if (counter) {
                        counter.textContent = data.count;
                        counter.style.display = data.count > 0 ? 'inline' : 'none';
                    }
                })
                .catch(error => console.log('Erreur lors du chargement du compteur de notifications:', error));
        }

        // Auto-refresh du compteur toutes les 30 secondes
        setInterval(loadNotificationCounter, 30000);

        // Ajuster le padding-top du main-content selon l'état de la navbar
        document.addEventListener('DOMContentLoaded', function() {
            const navbar = document.querySelector('.top-navbar');
            const navbarCollapse = document.getElementById('topNavbar');
            const mainContent = document.querySelector('.main-content');
            
            if (navbarCollapse && mainContent) {
                function adjustMainContentPadding() {
                    if (window.innerWidth <= 768) {
                        const navbarHeight = navbar.offsetHeight;
                        // Ne considérer que le collapse de la navbar principale, pas les dropdowns
                        const isExpanded = navbarCollapse.classList.contains('show') || navbarCollapse.classList.contains('collapsing');
                        
                        if (isExpanded) {
                            // Calculer la hauteur totale de la navbar dépliée
                            const totalHeight = navbarHeight + navbarCollapse.scrollHeight;
                            mainContent.style.paddingTop = (totalHeight + 20) + 'px';
                        } else {
                            // Hauteur normale de la navbar
                            mainContent.style.paddingTop = (navbarHeight + 20) + 'px';
                        }
                    } else {
                        // Sur desktop, utiliser le padding normal
                        mainContent.style.paddingTop = '70px';
                    }
                }
                
                // Écouter uniquement les événements de collapse Bootstrap de la navbar principale
                // Mais empêcher que les clics sur le dropdown déclenchent le collapse
                navbarCollapse.addEventListener('show.bs.collapse', function(e) {
                    // Vérifier si l'événement vient du dropdown du profil
                    const profileDropdown = document.getElementById('profileDropdown');
                    if (profileDropdown && (e.target === profileDropdown || profileDropdown.contains(e.target))) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    adjustMainContentPadding();
                });
                
                navbarCollapse.addEventListener('shown.bs.collapse', adjustMainContentPadding);
                navbarCollapse.addEventListener('hide.bs.collapse', function(e) {
                    // Vérifier si l'événement vient du dropdown du profil
                    const profileDropdown = document.getElementById('profileDropdown');
                    if (profileDropdown && (e.target === profileDropdown || profileDropdown.contains(e.target))) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    adjustMainContentPadding();
                });
                navbarCollapse.addEventListener('hidden.bs.collapse', adjustMainContentPadding);
                
                // Ajuster au chargement et au redimensionnement
                adjustMainContentPadding();
                window.addEventListener('resize', adjustMainContentPadding);
                
                // Empêcher les dropdowns d'affecter le padding et le collapse
                document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
                    toggle.addEventListener('click', function(e) {
                        // Ne pas empêcher le comportement par défaut du dropdown
                        // Mais empêcher la propagation vers d'autres gestionnaires
                        e.stopPropagation();
                        
                        // Si c'est le dropdown du profil, empêcher le collapse
                        if (this.id === 'profileDropdownToggle' || this.closest('#profileDropdown')) {
                            e.stopImmediatePropagation();
                            
                            // Empêcher que le collapse se déclenche
                            if (window.innerWidth <= 992 && navbarCollapse.classList.contains('show')) {
                                // Ne pas fermer le collapse sur mobile/tablette
                                const clickEvent = new MouseEvent('click', {
                                    bubbles: false,
                                    cancelable: true
                                });
                                e.preventDefault();
                            }
                        }
                    });
                });
            }
        });

        // Gestion de la déconnexion
        document.addEventListener('DOMContentLoaded', function() {
            const logoutBtn = document.getElementById('logoutBtn');
            const logoutForm = document.getElementById('logoutForm');
            
            if (logoutBtn && logoutForm) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Tentative de déconnexion...');
                    logoutForm.submit();
                });
            }
            
            // Empêcher le clic sur le dropdown du profil d'affecter le sidebar et le collapse de la navbar
            const profileDropdown = document.getElementById('profileDropdown');
            const profileDropdownToggle = document.getElementById('profileDropdownToggle');
            const profileDropdownMenu = document.getElementById('profileDropdownMenu');
            const navbarCollapse = document.getElementById('topNavbar');
            
            if (profileDropdown && profileDropdownToggle && navbarCollapse) {
                // Empêcher spécifiquement que le toggle déclenche le collapse
                profileDropdownToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                }, true);
                
                // Permettre les clics sur les liens du menu dropdown sans empêcher la navigation
                if (profileDropdownMenu) {
                    // Permettre les clics sur les liens (pas de stopPropagation pour les liens)
                    profileDropdownMenu.addEventListener('click', function(e) {
                        // Si c'est un lien ou un bouton dans un formulaire, permettre la navigation
                        const link = e.target.closest('a.dropdown-item');
                        const button = e.target.closest('button.dropdown-item');
                        
                        if (link || button) {
                            // Laisser le lien/bouton fonctionner normalement
                            // Ne pas empêcher la propagation pour permettre la navigation
                            return true;
                        }
                        
                        // Pour les autres éléments, empêcher la propagation
                        e.stopPropagation();
                    }, false); // false = bubbling phase (après la phase de capture)
                }
                
                // Intercepter les clics sur le conteneur dropdown (mais pas sur les liens)
                profileDropdown.addEventListener('click', function(e) {
                    // Si c'est un lien ou un bouton, ne pas empêcher
                    if (e.target.closest('a.dropdown-item') || e.target.closest('button.dropdown-item')) {
                        return true;
                    }
                    
                    // Si c'est le toggle, empêcher la propagation
                    if (e.target === profileDropdownToggle || profileDropdownToggle.contains(e.target)) {
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                    }
                }, true);
                
                // Empêcher que les clics en dehors du dropdown ferment le collapse
                // mais seulement si le collapse est ouvert à cause du dropdown
                document.addEventListener('click', function(e) {
                    // Si on clique sur le dropdown ou ses enfants, ne pas fermer le collapse
                    if (profileDropdown.contains(e.target)) {
                        // Empêcher que le collapse se ferme
                        if (window.innerWidth <= 992 && navbarCollapse.classList.contains('show')) {
                            e.stopPropagation();
                        }
                    }
                }, true);
                
                // Empêcher que le collapse se ferme quand le dropdown est ouvert
                let isDropdownOpen = false;
                
                profileDropdown.addEventListener('show.bs.dropdown', function() {
                    isDropdownOpen = true;
                });
                
                profileDropdown.addEventListener('hidden.bs.dropdown', function() {
                    isDropdownOpen = false;
                });
                
                // Intercepter l'événement hide.bs.collapse pour empêcher la fermeture si le dropdown est ouvert
                navbarCollapse.addEventListener('hide.bs.collapse', function(e) {
                    if (isDropdownOpen && window.innerWidth <= 992) {
                        // Empêcher que le collapse se ferme si le dropdown est ouvert
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
