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
        
        /* Lignes de tableau cliquables (listes sans colonne Actions) */
        tr.table-row-clickable {
            cursor: pointer;
        }

        tr.table-row-clickable:hover {
            background-color: rgba(13, 110, 253, 0.06) !important;
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
            background: linear-gradient(135deg, {{ $couleurs['general']['header_bg'] ?? '#667eea' }} 0%, {{ $couleurs['general']['primary_color'] ?? '#764ba2' }} 100%); /* couleur de fond du menu horizontal */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 100;
            min-height: 60px;
        }
        
        .top-navbar .nav-link {
            color: {{ $couleurs['general']['header_text'] ?? '#ffffff' }} !important;
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
            border-right: 2px solid {{ $couleurs['general']['secondary_color'] ?? '#e9ecef' }};
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
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.1rem;
            border-radius: 10px;
            margin: 4px 12px;
            transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
            border-left: 4px solid transparent;
            font-weight: 500;
            position: relative;
            line-height: 1.3;
        }
        
        .sidebar .nav-link:hover {
            background: linear-gradient(135deg, {{ $couleurs['general']['header_bg'] ?? '#667eea' }} 0%, {{ $couleurs['general']['primary_color'] ?? '#764ba2' }} 100%); /* couleur de fond du menu latéral */
            color: {{ $couleurs['general']['header_text'] ?? '#ffffff' }} !important;
            border-left: 4px solid {{ $couleurs['general']['header_text'] ?? '#ffffff' }};
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, {{ $couleurs['general']['header_bg'] ?? '#667eea' }} 0%, {{ $couleurs['general']['primary_color'] ?? '#764ba2' }} 100%);
            color: {{ $couleurs['general']['header_text'] ?? '#ffffff' }} !important;
            border-left: 4px solid {{ $couleurs['general']['header_text'] ?? '#ffffff' }};
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .sidebar .nav-link i {
            width: 1.35rem;
            min-width: 1.35rem;
            margin: 0;
            font-size: 1rem;
            line-height: 1;
            text-align: center;
            flex-shrink: 0;
            transform: none !important;
        }

        .sidebar .nav-link:hover i {
            transform: none !important;
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
            background: linear-gradient(135deg, {{ $couleurs['general']['header_bg'] ?? '#667eea' }} 0%, {{ $couleurs['general']['primary_color'] ?? '#764ba2' }} 100%) !important;
            color: {{ $couleurs['general']['header_text'] ?? '#ffffff' }} !important;
            border-left: 3px solid {{ $couleurs['general']['header_text'] ?? '#ffffff' }};
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
            background: linear-gradient(135deg, {{ $couleurs['general']['danger_color'] ?? '#dc3545' }} 0%, #c82333 100%) !important;
            color: {{ $couleurs['general']['header_text'] ?? '#ffffff' }} !important;
            border-left: 3px solid {{ $couleurs['general']['header_text'] ?? '#ffffff' }};
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
        
        /* Un seul menu hamburger sous le breakpoint lg (992px) */
        @media (max-width: 991.98px) {
            .top-navbar {
                min-height: 56px;
                padding-top: env(safe-area-inset-top);
            }
            
            .top-navbar .navbar-brand {
                font-size: 1rem;
            }

            #topNavbar {
                display: none !important;
            }

            #sidebarToggle {
                padding: 0.35rem 0.55rem;
                font-size: 1rem;
                line-height: 1.2;
                min-width: 44px;
                min-height: 44px;
            }
            
            .main-content {
                margin-left: 0 !important;
                padding: 68px 8px 8px 8px !important;
            }
            
            .main-content .container-fluid {
                padding: 8px;
            }
            
            .sidebar {
                position: fixed !important;
                top: 56px;
                top: calc(56px + env(safe-area-inset-top));
                left: 0 !important;
                right: auto !important;
                width: min(85vw, 320px) !important;
                max-width: min(85vw, 320px) !important;
                flex: 0 0 auto !important;
                height: calc(100vh - 56px);
                height: calc(100dvh - 56px - env(safe-area-inset-top));
                z-index: 1040;
                transform: translateX(-100%);
                transition: transform 0.25s ease;
                box-shadow: 2px 0 15px rgba(0, 0, 0, 0.15);
                overflow-y: auto;
                overflow-x: hidden;
                padding: 12px 0 20px !important;
                margin: 0 !important;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar .nav {
                width: 100%;
                padding: 0 8px;
            }

            .sidebar .nav-link {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.7rem 0.9rem;
                margin: 3px 0;
                border-radius: 8px;
                font-size: 0.9rem;
                min-height: 44px;
                line-height: 1.25;
                border-left: none;
                transform: none !important;
            }

            .sidebar .nav-link:hover,
            .sidebar .nav-link.active {
                transform: none !important;
            }

            .sidebar .nav-link i {
                width: 1.35rem;
                min-width: 1.35rem;
                margin: 0 !important;
                font-size: 0.95rem;
                line-height: 1;
                text-align: center;
                flex-shrink: 0;
                transform: none !important;
            }
            
            .sidebar-overlay {
                position: fixed;
                top: 56px;
                top: calc(56px + env(safe-area-inset-top));
                left: 0;
                width: 100%;
                height: calc(100vh - 56px);
                height: calc(100dvh - 56px - env(safe-area-inset-top));
                background: rgba(0, 0, 0, 0.5);
                z-index: 1035;
                display: none;
            }
            
            .sidebar-overlay.show {
                display: block;
            }

            body.sidebar-open {
                overflow: hidden;
            }

            #profileDropdownToggle .user-fullname,
            #profileDropdownToggle .badge {
                display: none;
            }

            #profileDropdownToggle {
                padding: 4px 8px !important;
            }

            #profileDropdownToggle img {
                width: 28px !important;
                height: 28px !important;
                margin-right: 0 !important;
            }

            #profileDropdownMenu {
                position: absolute !important;
                right: 0 !important;
                left: auto !important;
                min-width: 200px !important;
                max-width: 90vw !important;
                margin-top: 0.5rem !important;
            }

            #profileDropdownMenu .dropdown-item {
                padding: 8px 14px !important;
                font-size: 0.875rem;
            }
        }
        
        @media (max-width: 576px) {
            .top-navbar .navbar-brand {
                font-size: 0.9rem;
            }

            .main-content {
                padding: 64px 6px 6px 6px !important;
            }
            
            .main-content .container-fluid {
                padding: 4px;
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
            <a class="navbar-brand me-auto me-lg-3" style="color: {{ $couleurs['general']['header_text'] ?? '#ffffff' }} !important;" href="{{ route('dashboard') }}">
                <i class="fas fa-graduation-cap me-2"></i>
                A2School
            </a>

            <div class="collapse navbar-collapse d-none d-lg-flex" id="topNavbar">
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
            </div>

            <ul class="navbar-nav flex-row align-items-center ms-auto">
                    <!-- Notifications -->
                    <li class="nav-item">
                        <a class="nav-link text-white position-relative px-2" href="{{ route('notifications.index') }}" title="Notifications">
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
                            <span class="user-fullname">{{ auth()->user()->nom }} {{ auth()->user()->prenom }}</span>
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

            <button class="btn btn-outline-light d-lg-none ms-2" type="button" id="sidebarToggle" aria-label="Ouvrir le menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Overlay pour mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Menu latéral pour sous-menus -->
            <nav class="col-md-3 col-lg-2 sidebar" id="sidebar">
                <div class="position-sticky pt-2">
                    <div class="d-lg-none px-3 mb-2">
                        <p class="text-muted text-uppercase small mb-1 fw-bold px-1">Sous-menus</p>
                    </div>
                    <div class="d-none d-lg-block text-center mb-3">
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

                    <div class="d-lg-none px-3 mt-2">
                        <hr class="my-2" style="border-color: #667eea; opacity: 0.3;">
                        <p class="text-muted text-uppercase small mb-1 fw-bold px-1">Navigation</p>
                        <ul class="nav flex-column" id="mobileMainMenu"></ul>
                    </div>
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

    @stack('modals')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script pour la gestion des menus -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Charger le compteur de notifications
            loadNotificationCounter();
            
            // Gestion du sidebar mobile (un seul hamburger)
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const mobileMainMenu = document.getElementById('mobileMainMenu');

            function closeSidebar() {
                if (!sidebar) return;
                sidebar.classList.remove('show');
                if (sidebarOverlay) sidebarOverlay.classList.remove('show');
                document.body.classList.remove('sidebar-open');
                if (sidebarToggle) {
                    sidebarToggle.setAttribute('aria-expanded', 'false');
                    sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
                }
            }

            function openSidebar() {
                if (!sidebar) return;
                sidebar.classList.add('show');
                if (sidebarOverlay) sidebarOverlay.classList.add('show');
                document.body.classList.add('sidebar-open');
                if (sidebarToggle) {
                    sidebarToggle.setAttribute('aria-expanded', 'true');
                    sidebarToggle.innerHTML = '<i class="fas fa-times"></i>';
                }
            }

            function toggleSidebar() {
                if (sidebar && sidebar.classList.contains('show')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            }

            if (mobileMainMenu) {
                document.querySelectorAll('#topNavbar [data-menu]').forEach(function(link) {
                    const li = document.createElement('li');
                    li.className = 'nav-item';
                    const clone = link.cloneNode(true);
                    clone.classList.add('nav-link');
                    li.appendChild(clone);
                    mobileMainMenu.appendChild(li);
                });
            }
            
            if (sidebarToggle && sidebar && sidebarOverlay) {
                sidebarToggle.addEventListener('click', function() {
                    toggleSidebar();
                });
                
                sidebarOverlay.addEventListener('click', closeSidebar);
                
                sidebar.addEventListener('click', function(e) {
                    const link = e.target.closest('a');
                    if (link && link.getAttribute('href') && link.getAttribute('href') !== '#') {
                        closeSidebar();
                    }
                });

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeSidebar();
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
                    { href: '{{ route("factures.index") }}', icon: 'fas fa-file-invoice', text: 'Facturation' },
                    { href: '{{ route("comptabilite.impayes-mensuels") }}', icon: 'fas fa-user-times', text: 'Impayés mensuels' },
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
                        document.querySelectorAll('[data-menu]').forEach(l => l.classList.remove('active'));
                        
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
                    document.querySelectorAll('[data-menu]').forEach(l => l.classList.remove('active'));
                    
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
                        document.querySelectorAll('[data-menu]').forEach(l => l.classList.remove('active'));
                        
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

        document.addEventListener('DOMContentLoaded', function() {
            const logoutBtn = document.getElementById('logoutBtn');
            const logoutForm = document.getElementById('logoutForm');
            
            if (logoutBtn && logoutForm) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    logoutForm.submit();
                });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('tr.table-row-clickable[data-href]').forEach(function(row) {
                row.addEventListener('click', function(e) {
                    if (e.target.closest('a, button, form, input, select, label, textarea')) {
                        return;
                    }
                    const href = row.getAttribute('data-href');
                    if (href) {
                        window.location.href = href;
                    }
                });

                row.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        const href = row.getAttribute('data-href');
                        if (href) {
                            window.location.href = href;
                        }
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
