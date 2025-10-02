<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\EnseignantController;
use App\Http\Controllers\EleveController;
use App\Http\Controllers\MatiereController;
use App\Http\Controllers\EmploiTempsController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\EntreeController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\SalaireEnseignantController;
use App\Http\Controllers\TarifClasseController;
use App\Http\Controllers\CarteScolaireController;
use App\Http\Controllers\CarteEnseignantController;
use App\Http\Controllers\ParentPaiementController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminNotificationController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\ComptabiliteController;

// Route d'accueil - redirection vers dashboard si connecté, sinon vers login ou setup
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    
    // Vérifier si un admin existe
    $adminExists = \App\Models\Utilisateur::where('role', 'admin')
        ->orWhere('role', 'personnel_admin')
        ->exists();
    
    if (!$adminExists) {
        return redirect()->route('admin.setup');
    }
    
    return redirect()->route('login');
});

// Route de configuration initiale (sans middleware d'auth)
Route::get('/admin/setup', [\App\Http\Controllers\AdminSetupController::class, 'index'])->name('admin.setup');
Route::post('/admin/setup', [\App\Http\Controllers\AdminSetupController::class, 'store'])->name('admin.setup.store');

// Route de test pour les permissions
Route::get('/test-permissions', function() {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Non connecté']);
    }
    
    return response()->json([
        'user' => $user->nom . ' ' . $user->prenom,
        'role' => $user->role,
        'permissions' => [
            'enseignants.view' => $user->hasPermission('enseignants.view'),
            'enseignants.edit' => $user->hasPermission('enseignants.edit'),
            'eleves.view' => $user->hasPermission('eleves.view'),
            'eleves.edit' => $user->hasPermission('eleves.edit'),
        ]
    ]);
})->middleware('auth');

// Routes de diagnostic pour l'emploi du temps
Route::get('/debug/emploi-temps', function() {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Non connecté']);
    }
    
    $data = [
        'user' => $user->nom . ' ' . $user->prenom,
        'role' => $user->role,
        'routes' => [
            'student.emploi-temps' => route('student.emploi-temps'),
            'teacher.emploi-temps' => route('teacher.emploi-temps'),
            'emplois-temps.index' => route('emplois-temps.index')
        ],
        'permissions' => [
            'emplois-temps.view' => $user->hasPermission('emplois-temps.view'),
            'emplois-temps.create' => $user->hasPermission('emplois-temps.create'),
            'emplois-temps.delete' => $user->hasPermission('emplois-temps.delete')
        ]
    ];
    
    if ($user->role === 'student' && $user->eleve) {
        $data['eleve'] = [
            'id' => $user->eleve->id,
            'classe' => $user->eleve->classe ? $user->eleve->classe->nom : 'Aucune classe',
            'emplois_count' => $user->eleve->classe ? $user->eleve->classe->emploisTemps()->count() : 0
        ];
    }
    
    if ($user->role === 'teacher' && $user->enseignant) {
        $data['enseignant'] = [
            'id' => $user->enseignant->id,
            'emplois_count' => $user->enseignant->emploisTemps()->count()
        ];
    }
    
    return response()->json($data);
})->middleware('auth');

// Routes d'authentification
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Routes pour le changement de mot de passe
Route::middleware('auth')->group(function () {
    Route::get('/change-password', [\App\Http\Controllers\PasswordController::class, 'showChangePasswordForm'])->name('password.change.form');
    Route::post('/change-password', [\App\Http\Controllers\PasswordController::class, 'changePassword'])->name('password.change');
});

// Routes protégées par authentification
Route::middleware('auth')->group(function () {
    // Dashboard principal (redirection automatique selon le rôle)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Dashboards spécifiques par rôle
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])
        ->name('admin.dashboard')
        ->middleware('role:admin,personnel_admin');
        
    Route::get('/teacher/dashboard', [DashboardController::class, 'index'])
        ->name('teacher.dashboard')
        ->middleware('role:teacher');
        
    Route::get('/student/dashboard', [DashboardController::class, 'index'])
        ->name('student.dashboard')
        ->middleware('role:student');
        
    Route::get('/parent/dashboard', [DashboardController::class, 'parentDashboard'])
        ->name('parent.dashboard')
        ->middleware('role:parent');
        
    Route::get('/personnel-admin/dashboard', [DashboardController::class, 'adminDashboard'])
        ->name('personnel-admin.dashboard')
        ->middleware('role:personnel_admin');
    
    // Routes pour les notifications (tous les utilisateurs authentifiés)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::put('/notifications/{id}/marquer-lue', [NotificationController::class, 'marquerCommeLue'])->name('notifications.marquer-lue');
    Route::put('/notifications/marquer-toutes-lues', [NotificationController::class, 'marquerToutesCommeLues'])->name('notifications.marquer-toutes-lues');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications/supprimer-toutes-lues', [NotificationController::class, 'supprimerToutesLues'])->name('notifications.supprimer-toutes-lues');
    Route::get('/api/notifications/compteur-non-lues', [NotificationController::class, 'compteurNonLues'])->name('notifications.compteur-non-lues');
    Route::get('/api/notifications/dernieres', [NotificationController::class, 'dernieres'])->name('notifications.dernieres');
        
    // Routes pour la gestion des notes (Admin, Enseignants et Personnel Admin)
    Route::middleware('role:admin,teacher,personnel_admin')->group(function () {
        Route::get('/notes', [NoteController::class, 'index'])->name('notes.index')->middleware('check.permission:notes.view');
        Route::get('/notes/classe/{classe}', [NoteController::class, 'saisir'])->name('notes.saisir')->middleware('check.permission:notes.create');
        Route::get('/teacher/notes/classe/{classe}', [NoteController::class, 'teacherSaisir'])->name('teacher.notes.saisir')->middleware('role:teacher');
        Route::post('/notes', [NoteController::class, 'store'])->name('notes.store')->middleware('check.permission:notes.create');
        Route::get('/notes/eleve/{eleve}', [NoteController::class, 'eleveNotes'])->name('notes.eleve')->middleware('check.permission:notes.view');
        Route::get('/notes/{note}/edit', [NoteController::class, 'edit'])->name('notes.edit')->middleware('check.permission:notes.edit');
        Route::put('/notes/{note}', [NoteController::class, 'update'])->name('notes.update')->middleware('check.permission:notes.edit');
        Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy')->middleware('check.permission:notes.delete');
        Route::get('/notes/statistiques', [NoteController::class, 'statistiques'])->name('notes.statistiques')->middleware('check.permission:notes.view');
        Route::get('/notes/statistiques/{classe}', [NoteController::class, 'statistiquesClasse'])->name('notes.statistiques.classe')->middleware('check.permission:notes.view');
        Route::get('/notes/statistiques/{classe}/imprimer', [NoteController::class, 'statistiquesClasseImprimable'])->name('notes.statistiques.classe.imprimer')->middleware('check.permission:notes.view');
        Route::get('/notes/bulletins', [NoteController::class, 'bulletins'])->name('notes.bulletins')->middleware('check.permission:notes.view');
        Route::get('/notes/bulletins/{classe}', [NoteController::class, 'genererBulletins'])->name('notes.bulletins.classe')->middleware('check.permission:notes.view');
        Route::get('/notes/rapport-global', [NoteController::class, 'rapportGlobal'])->name('notes.rapport-global')->middleware('check.permission:notes.view');
        Route::get('/notes/export', [NoteController::class, 'exporterNotes'])->name('notes.export')->middleware('check.permission:notes.view');
        Route::get('/notes/parametres', [NoteController::class, 'parametres'])->name('notes.parametres')->middleware('check.permission:notes.view');
        Route::post('/notes/periodes-scolaires', [NoteController::class, 'createPeriodeScolaire'])->name('notes.periodes.create')->middleware('check.permission:notes.create');
        Route::put('/notes/periodes-scolaires/{id}', [NoteController::class, 'updatePeriodeScolaire'])->name('notes.periodes.update')->middleware('check.permission:notes.edit');
        Route::delete('/notes/periodes-scolaires/{id}', [NoteController::class, 'deletePeriodeScolaire'])->name('notes.periodes.delete')->middleware('check.permission:notes.delete');
        Route::get('/api/matiere/{matiere}/coefficient', [NoteController::class, 'getCoefficientMatiere'])->name('api.matiere.coefficient');
    });
    
    // Routes pour la gestion des absences (Admin et Enseignants)
    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/absences', [AbsenceController::class, 'index'])->name('absences.index');
        Route::get('/absences/classe/{classe}', [AbsenceController::class, 'saisir'])->name('absences.saisir');
        Route::post('/absences', [AbsenceController::class, 'store'])->name('absences.store');
        Route::get('/absences/eleve/{eleve}', [AbsenceController::class, 'eleveAbsences'])->name('absences.eleve');
        Route::post('/absences/{absence}/justifier', [AbsenceController::class, 'justifier'])->name('absences.justifier');
        Route::get('/absences/rapport/{classe}', [AbsenceController::class, 'rapportClasse'])->name('absences.rapport');
        Route::post('/absences/{absence}/notifier', [AbsenceController::class, 'notifierParents'])->name('absences.notifier');
        Route::get('/absences/statistiques', [AbsenceController::class, 'statistiques'])->name('absences.statistiques');
    });
    
    // Routes pour la gestion des enseignants (Admin seulement)
    Route::middleware('role:admin,personnel_admin')->group(function () {
        Route::get('enseignants', [EnseignantController::class, 'index'])->name('enseignants.index')->middleware('check.permission:enseignants.view');
        Route::get('enseignants/create', [EnseignantController::class, 'create'])->name('enseignants.create')->middleware('check.permission:enseignants.create');
        Route::post('enseignants', [EnseignantController::class, 'store'])->name('enseignants.store')->middleware('check.permission:enseignants.create');
        // Routes pour les photos (doivent être avant les routes génériques)
        Route::get('/enseignants/{enseignant}/photo', [EnseignantController::class, 'showPhoto'])->name('enseignants.show-photo');
        Route::delete('/enseignants/{enseignant}/photo', [EnseignantController::class, 'deletePhoto'])->name('enseignants.delete-photo')->middleware('check.permission:enseignants.edit');
        
        // Routes génériques pour les enseignants
        Route::get('enseignants/{enseignant}', [EnseignantController::class, 'show'])->name('enseignants.show')->middleware('check.permission:enseignants.view');
        Route::get('enseignants/{enseignant}/edit', [EnseignantController::class, 'edit'])->name('enseignants.edit')->middleware('check.permission:enseignants.edit');
        Route::put('enseignants/{enseignant}', [EnseignantController::class, 'update'])->name('enseignants.update')->middleware('check.permission:enseignants.edit');
        
        // Route de test temporaire sans middleware
        Route::put('enseignants/{enseignant}/test-update', [EnseignantController::class, 'update'])->name('enseignants.test-update');
        Route::get('enseignants/{enseignant}/test-edit', [EnseignantController::class, 'testEdit'])->name('enseignants.test-edit');
        
        // Route de test simple sans middleware
        Route::put('test-enseignant-update/{enseignant}', [EnseignantController::class, 'update'])->name('test.enseignant.update');
        
        Route::delete('enseignants/{enseignant}', [EnseignantController::class, 'destroy'])->name('enseignants.destroy')->middleware('check.permission:enseignants.delete');
        Route::post('/enseignants/{enseignant}/reset-password', [EnseignantController::class, 'resetPassword'])->name('enseignants.reset-password')->middleware('check.permission:enseignants.edit');
        Route::post('/enseignants/{enseignant}/reactivate', [EnseignantController::class, 'reactivate'])->name('enseignants.reactivate')->middleware('check.permission:enseignants.edit');
    });
    
    // Route de test complètement libre (en dehors de tout middleware)
    Route::put('test-update-simple/{enseignant}', [EnseignantController::class, 'update'])->name('test.update.simple');
    Route::post('test-update-simple/{enseignant}', [EnseignantController::class, 'update'])->name('test.update.simple.post');
    Route::get('test-update-simple/{enseignant}', [EnseignantController::class, 'update'])->name('test.update.simple.get');
    
    // Route de test simple pour vérifier l'utilisateur
    Route::get('/test-user', function() {
        if (!auth()->check()) {
            return response()->json(['error' => 'Non connecté']);
        }
        $user = auth()->user();
        return response()->json([
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'is_admin' => $user->role === 'admin',
            'is_personnel_admin' => $user->role === 'personnel_admin',
            'can_access_admin' => $user->role === 'admin' || $user->role === 'personnel_admin',
            'has_enseignants_edit_permission' => $user->hasPermission('enseignants.edit'),
            'personnel_admin_permissions' => $user->personnelAdministration ? $user->personnelAdministration->permissions : null
        ]);
    })->name('test.user');

    // Routes de test pour les statistiques (sans préfixe admin)
    Route::middleware(['auth', 'role:admin,personnel_admin'])->group(function () {
        Route::get('/test-statistiques-financieres', [StatistiqueController::class, 'financieres'])->name('test.statistiques.financieres')->middleware('check.permission:statistiques.financieres');
        Route::get('/test-statistiques-absences', [StatistiqueController::class, 'absences'])->name('test.statistiques.absences')->middleware('check.permission:statistiques.absences');
    });
    
    // Routes pour l'administration
    Route::middleware(['auth', 'role:admin,personnel_admin'])->prefix('admin')->group(function () {
        Route::get('/statistiques', [\App\Http\Controllers\AdminController::class, 'statistiques'])->name('admin.statistiques')->middleware('check.permission:statistiques.view');
        Route::get('/statistiques-generales', [StatistiqueController::class, 'index'])->name('statistiques.generales')->middleware('check.permission:statistiques.view');
        Route::get('/statistiques-financieres', [StatistiqueController::class, 'financieres'])->name('statistiques.financieres')->middleware('check.permission:statistiques.financieres');
        Route::get('/statistiques-absences', [StatistiqueController::class, 'absences'])->name('statistiques.absences')->middleware('check.permission:statistiques.absences');
        Route::get('/api/statistiques/data', [StatistiqueController::class, 'apiData'])->name('statistiques.api.data')->middleware('check.permission:statistiques.view');
        
        
        // Route de test pour vérifier les permissions
        Route::get('/test-permissions', function() {
            $user = auth()->user();
            return response()->json([
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_name' => $user->name,
                'is_admin' => $user->role === 'admin',
                'is_personnel_admin' => $user->role === 'personnel_admin',
                'has_role_method' => method_exists($user, 'hasRole'),
                'can_access_admin' => $user->role === 'admin' || $user->role === 'personnel_admin'
            ]);
        })->name('test.permissions');
        
        // Gestion du personnel d'administration
        Route::resource('personnel-administration', \App\Http\Controllers\PersonnelAdministrationController::class);
        Route::get('/personnel-administration/{personnelAdministration}/permissions', [\App\Http\Controllers\PersonnelAdministrationController::class, 'managePermissions'])->name('personnel-administration.permissions');
        Route::put('/personnel-administration/{personnelAdministration}/permissions', [\App\Http\Controllers\PersonnelAdministrationController::class, 'updatePermissions'])->name('personnel-administration.update-permissions');
        Route::post('/personnel-administration/{personnelAdministration}/reset-password', [\App\Http\Controllers\PersonnelAdministrationController::class, 'resetPassword'])->name('personnel-administration.reset-password');
        
        // Gestion des comptes administrateurs
        Route::get('accounts', [\App\Http\Controllers\AdminAccountController::class, 'index'])->name('admin.accounts.index')->middleware('check.permission:admin.accounts.view');
        Route::get('accounts/create', [\App\Http\Controllers\AdminAccountController::class, 'create'])->name('admin.accounts.create')->middleware('check.permission:admin.accounts.create');
        Route::post('accounts', [\App\Http\Controllers\AdminAccountController::class, 'store'])->name('admin.accounts.store')->middleware('check.permission:admin.accounts.create');
        Route::get('accounts/{adminAccount}', [\App\Http\Controllers\AdminAccountController::class, 'show'])->name('admin.accounts.show')->middleware('check.permission:admin.accounts.view');
        Route::get('accounts/{adminAccount}/edit', [\App\Http\Controllers\AdminAccountController::class, 'edit'])->name('admin.accounts.edit')->middleware('check.permission:admin.accounts.edit');
        Route::put('accounts/{adminAccount}', [\App\Http\Controllers\AdminAccountController::class, 'update'])->name('admin.accounts.update')->middleware('check.permission:admin.accounts.edit');
        Route::delete('accounts/{adminAccount}', [\App\Http\Controllers\AdminAccountController::class, 'destroy'])->name('admin.accounts.destroy')->middleware('check.permission:admin.accounts.delete');
        Route::get('/accounts/{adminAccount}/permissions', [\App\Http\Controllers\AdminAccountController::class, 'managePermissions'])->name('admin.accounts.permissions')->middleware('check.permission:admin.accounts.edit');
        Route::put('/accounts/{adminAccount}/permissions', [\App\Http\Controllers\AdminAccountController::class, 'updatePermissions'])->name('admin.accounts.update-permissions')->middleware('check.permission:admin.accounts.edit');
        Route::post('/accounts/{adminAccount}/reset-password', [\App\Http\Controllers\AdminAccountController::class, 'resetPassword'])->name('admin.accounts.reset-password')->middleware('check.permission:admin.accounts.edit');
        Route::post('/accounts/{adminAccount}/toggle-status', [\App\Http\Controllers\AdminAccountController::class, 'toggleStatus'])->name('admin.accounts.toggle-status')->middleware('check.permission:admin.accounts.edit');
        
        // Notifications - Création et gestion (Admin seulement)
        Route::get('/notifications/create', [NotificationController::class, 'create'])->name('notifications.create');
        Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
        
        // Messages des parents - Gestion par l'administration
        Route::get('/messages-parents', [AdminNotificationController::class, 'index'])->name('admin.notifications.index');
        Route::get('/messages-parents/{message}', [AdminNotificationController::class, 'show'])->name('admin.notifications.show');
        Route::post('/messages-parents/{message}/repondre', [AdminNotificationController::class, 'repondre'])->name('admin.notifications.repondre');
        Route::put('/messages-parents/{message}/marquer-lue', [AdminNotificationController::class, 'marquerLue'])->name('admin.notifications.marquer-lue');
        Route::delete('/messages-parents/{message}', [AdminNotificationController::class, 'destroy'])->name('admin.notifications.destroy');
        Route::put('/messages-parents/marquer-toutes-lues', [AdminNotificationController::class, 'marquerToutesLues'])->name('admin.notifications.marquer-toutes-lues');
        Route::get('/messages-parents-statistiques', [AdminNotificationController::class, 'statistiques'])->name('admin.notifications.statistiques');
        Route::get('/api/admin/messages/compteur-non-lues', [AdminNotificationController::class, 'compterNonLues'])->name('admin.notifications.compteur-non-lues');
        Route::get('/parametres', [\App\Http\Controllers\AdminController::class, 'parametres'])->name('admin.parametres');
        Route::put('/parametres', [\App\Http\Controllers\AdminController::class, 'updateParametres'])->name('admin.parametres.update');
        
        // Gestion des utilisateurs
        Route::get('/utilisateurs', [\App\Http\Controllers\AdminController::class, 'utilisateurs'])->name('admin.utilisateurs');
        Route::get('/utilisateurs/create', [\App\Http\Controllers\AdminController::class, 'createUtilisateur'])->name('admin.utilisateurs.create');
        Route::post('/utilisateurs', [\App\Http\Controllers\AdminController::class, 'storeUtilisateur'])->name('admin.utilisateurs.store');
        Route::get('/utilisateurs/{utilisateur}/edit', [\App\Http\Controllers\AdminController::class, 'editUtilisateur'])->name('admin.utilisateurs.edit');
        Route::put('/utilisateurs/{utilisateur}', [\App\Http\Controllers\AdminController::class, 'updateUtilisateur'])->name('admin.utilisateurs.update');
        Route::delete('/utilisateurs/{utilisateur}', [\App\Http\Controllers\AdminController::class, 'destroyUtilisateur'])->name('admin.utilisateurs.destroy');
        Route::patch('/utilisateurs/{utilisateur}/toggle', [\App\Http\Controllers\AdminController::class, 'toggleUtilisateur'])->name('admin.utilisateurs.toggle');
        
        // Maintenance du système
        Route::get('/cache/clear', [\App\Http\Controllers\AdminController::class, 'clearCache'])->name('admin.cache.clear');
        Route::get('/db/optimize', [\App\Http\Controllers\AdminController::class, 'optimizeDatabase'])->name('admin.db.optimize');
        Route::get('/backup/create', [\App\Http\Controllers\AdminController::class, 'createBackup'])->name('admin.backup.create');
        Route::get('/backup/list', [\App\Http\Controllers\AdminController::class, 'listBackups'])->name('admin.backup.list');
        Route::get('/backup/{filename}/download', [\App\Http\Controllers\AdminController::class, 'downloadBackup'])->name('admin.backup.download');
        Route::delete('/backup/{filename}', [\App\Http\Controllers\AdminController::class, 'deleteBackup'])->name('admin.backup.delete');
    });
    
    // Routes pour la gestion des élèves (Admin seulement)
    Route::middleware('role:admin,personnel_admin')->group(function () {
        Route::get('eleves', [EleveController::class, 'index'])->name('eleves.index')->middleware('check.permission:eleves.view');
        Route::get('eleves/create', [EleveController::class, 'create'])->name('eleves.create')->middleware('check.permission:eleves.create');
        Route::post('eleves', [EleveController::class, 'store'])->name('eleves.store')->middleware('check.permission:eleves.create');
        Route::get('eleves/{eleve}', [EleveController::class, 'show'])->name('eleves.show')->middleware('check.permission:eleves.view');
        Route::get('eleves/{eleve}/edit', [EleveController::class, 'edit'])->name('eleves.edit')->middleware('check.permission:eleves.edit');
        Route::put('eleves/{eleve}', [EleveController::class, 'update'])->name('eleves.update')->middleware('check.permission:eleves.edit');
        Route::delete('eleves/{eleve}', [EleveController::class, 'destroy'])->name('eleves.destroy')->middleware('check.permission:eleves.delete');
        Route::post('/eleves/{eleve}/reset-password', [EleveController::class, 'resetPassword'])->name('eleves.reset-password')->middleware('check.permission:eleves.edit');
        Route::post('/eleves/{eleve}/add-parent', [EleveController::class, 'addParent'])->name('eleves.add-parent')->middleware('check.permission:eleves.edit');
        Route::delete('/eleves/{eleve}/photo', [EleveController::class, 'deletePhoto'])->name('eleves.delete-photo');
        Route::patch('/eleves/{eleve}/reactivate', [EleveController::class, 'reactivate'])->name('eleves.reactivate');
        Route::post('/eleves/store-step', [EleveController::class, 'storeStep'])->name('eleves.store-step');
        Route::post('/eleves/generate-matricule', [EleveController::class, 'generateMatricule'])->name('eleves.generate-matricule');
        
        // Routes pour la réinscription
        Route::get('/eleves-reinscription', [EleveController::class, 'showReinscription'])->name('eleves.reinscription');
        Route::post('/eleves-reinscription', [EleveController::class, 'processReinscription'])->name('eleves.reinscription.process');
    });
    
    // Routes pour la gestion des matières (Admin seulement)
    Route::middleware('role:admin,personnel_admin')->group(function () {
        Route::resource('matieres', MatiereController::class)->middleware('check.permission:matieres.view');
        Route::post('/matieres/delete-all', [MatiereController::class, 'deleteAll'])->name('matieres.delete-all')->middleware('check.permission:matieres.delete');
        Route::patch('/matieres/{matiere}/toggle-status', [MatiereController::class, 'toggleStatus'])->name('matieres.toggle-status')->middleware('check.permission:matieres.edit');
        Route::patch('/matieres/{matiere}/reactivate', [MatiereController::class, 'reactivate'])->name('matieres.reactivate')->middleware('check.permission:matieres.edit');
        Route::get('/api/matiere/{matiere}/coefficient', [MatiereController::class, 'getCoefficient'])->name('api.matiere.coefficient')->middleware('check.permission:matieres.view');
    });
    
    
    // Routes pour la gestion des classes (Admin seulement)
    Route::middleware('role:admin,personnel_admin')->group(function () {
        Route::get('classes', [ClasseController::class, 'index'])->name('classes.index')->middleware('check.permission:classes.view');
        Route::get('classes/create', [ClasseController::class, 'create'])->name('classes.create')->middleware('check.permission:classes.create');
        Route::post('classes', [ClasseController::class, 'store'])->name('classes.store')->middleware('check.permission:classes.create');
        Route::get('classes/{classe}', [ClasseController::class, 'show'])->name('classes.show')->middleware('check.permission:classes.view');
        Route::get('classes/{classe}/edit', [ClasseController::class, 'edit'])->name('classes.edit')->middleware('check.permission:classes.edit');
        Route::put('classes/{classe}', [ClasseController::class, 'update'])->name('classes.update')->middleware('check.permission:classes.edit');
        Route::delete('classes/{classe}', [ClasseController::class, 'destroy'])->name('classes.destroy')->middleware('check.permission:classes.delete');
    });
    
    // Routes pour la gestion de l'établissement (Admin seulement)
    Route::middleware('role:admin,personnel_admin')->group(function () {
        Route::get('/etablissement/informations', [\App\Http\Controllers\EtablissementController::class, 'informations'])->name('etablissement.informations')->middleware('check.permission:etablissement.view');
        Route::get('/etablissement/informations/edit', [\App\Http\Controllers\EtablissementController::class, 'editInformations'])->name('etablissement.informations.edit')->middleware('check.permission:etablissement.edit');
        Route::put('/etablissement/informations', [\App\Http\Controllers\EtablissementController::class, 'updateInformations'])->name('etablissement.informations.update')->middleware('check.permission:etablissement.edit');
        Route::get('/etablissement/responsabilites', [\App\Http\Controllers\EtablissementController::class, 'responsabilites'])->name('etablissement.responsabilites')->middleware('check.permission:etablissement.view');
        Route::get('/etablissement/responsabilites/edit', [\App\Http\Controllers\EtablissementController::class, 'editResponsabilites'])->name('etablissement.responsabilites.edit')->middleware('check.permission:etablissement.edit');
        Route::put('/etablissement/responsabilites', [\App\Http\Controllers\EtablissementController::class, 'updateResponsabilites'])->name('etablissement.responsabilites.update')->middleware('check.permission:etablissement.edit');
        Route::delete('/etablissement/reset', [\App\Http\Controllers\EtablissementController::class, 'reset'])->name('etablissement.reset')->middleware('check.permission:etablissement.delete');
    });
    
    // Routes pour la gestion des années scolaires (Admin seulement)
    Route::middleware('role:admin,personnel_admin')->group(function () {
        Route::get('annees-scolaires', [\App\Http\Controllers\AnneeScolaireController::class, 'index'])->name('annees-scolaires.index')->middleware('check.permission:annees_scolaires.view');
        Route::get('annees-scolaires/create', [\App\Http\Controllers\AnneeScolaireController::class, 'create'])->name('annees-scolaires.create')->middleware('check.permission:annees_scolaires.create');
        Route::post('annees-scolaires', [\App\Http\Controllers\AnneeScolaireController::class, 'store'])->name('annees-scolaires.store')->middleware('check.permission:annees_scolaires.create');
        Route::get('annees-scolaires/{anneesScolaire}', [\App\Http\Controllers\AnneeScolaireController::class, 'show'])->name('annees-scolaires.show')->middleware('check.permission:annees_scolaires.view');
        Route::get('annees-scolaires/{anneesScolaire}/edit', [\App\Http\Controllers\AnneeScolaireController::class, 'edit'])->name('annees-scolaires.edit')->middleware('check.permission:annees_scolaires.edit');
        Route::put('annees-scolaires/{anneesScolaire}', [\App\Http\Controllers\AnneeScolaireController::class, 'update'])->name('annees-scolaires.update')->middleware('check.permission:annees_scolaires.edit');
        Route::delete('annees-scolaires/{anneesScolaire}', [\App\Http\Controllers\AnneeScolaireController::class, 'destroy'])->name('annees-scolaires.destroy')->middleware('check.permission:annees_scolaires.delete');
        Route::post('/annees-scolaires/{anneesScolaire}/activer', [\App\Http\Controllers\AnneeScolaireController::class, 'activer'])->name('annees-scolaires.activer')->middleware('check.permission:annees_scolaires.edit');
    });
    
    // Routes pour la gestion des emplois du temps (Admin seulement)
    Route::middleware('role:admin,personnel_admin')->group(function () {
        Route::get('/emplois-temps', [EmploiTempsController::class, 'index'])->name('emplois-temps.index')->middleware('check.permission:emplois-temps.view');
        Route::get('/emplois-temps/classe/{classe}', [EmploiTempsController::class, 'show'])->name('emplois-temps.show')->middleware('check.permission:emplois-temps.view');
        Route::get('/emplois-temps/classe/{classe}/data', function(App\Models\Classe $classe) {
            $emplois = App\Models\EmploiTemps::where('classe_id', $classe->id)
                ->with(['matiere', 'enseignant.utilisateur'])
                ->get();
            return response()->json(['classe' => $classe, 'emplois' => $emplois]);
        })->middleware('check.permission:emplois-temps.view');
        Route::post('/emplois-temps', [EmploiTempsController::class, 'store'])->name('emplois-temps.store')->middleware('check.permission:emplois-temps.create');
        Route::delete('/emplois-temps/{emploiTemps}', [EmploiTempsController::class, 'destroy'])->name('emplois-temps.destroy')->middleware('check.permission:emplois-temps.delete');
        Route::post('/emplois-temps/duplicate', [EmploiTempsController::class, 'duplicate'])->name('emplois-temps.duplicate')->middleware('check.permission:emplois-temps.create');
        Route::get('/emplois-temps/classe/{classe}/export', [EmploiTempsController::class, 'export'])->name('emplois-temps.export')->middleware('check.permission:emplois-temps.view');
        Route::post('/emplois-temps/delete-all', [EmploiTempsController::class, 'deleteAll'])->name('emplois-temps.delete-all')->middleware('check.permission:emplois-temps.delete');
    });
    
    // Routes pour la gestion des paiements (Admin seulement)
    Route::middleware('role:admin,personnel_admin')->group(function () {
        Route::get('/paiements', [PaiementController::class, 'index'])->name('paiements.index')->middleware('check.permission:paiements.view');
        Route::get('/paiements/create', [PaiementController::class, 'create'])->name('paiements.create')->middleware('check.permission:paiements.create');
        Route::post('/paiements', [PaiementController::class, 'store'])->name('paiements.store')->middleware('check.permission:paiements.create');
        Route::get('/paiements/{frais}', [PaiementController::class, 'show'])->name('paiements.show')->middleware('check.permission:paiements.view');
        Route::get('/paiements/{frais}/payer-direct', [PaiementController::class, 'payerDirect'])->name('paiements.payer-direct')->middleware('check.permission:paiements.create');
        Route::post('/paiements/{frais}/enregistrer-direct', [PaiementController::class, 'enregistrerPaiementDirect'])->name('paiements.enregistrer-direct')->middleware('check.permission:paiements.create');
        Route::get('/paiements/tranche/{tranche}/payer', [PaiementController::class, 'payerTranche'])->name('paiements.payer-tranche')->middleware('check.permission:paiements.create');
        Route::post('/paiements/tranche/{tranche}/enregistrer', [PaiementController::class, 'enregistrerPaiementTranche'])->name('paiements.enregistrer-tranche')->middleware('check.permission:paiements.enregistrer');
        Route::get('/paiements/rapports', [PaiementController::class, 'rapports'])->name('paiements.rapports')->middleware('check.permission:paiements.view');
        Route::get('/paiements/{frais}/recu/{paiement?}', [PaiementController::class, 'genererRecu'])->name('paiements.recu')->middleware('check.permission:paiements.view');
        
        // Routes pour la gestion des entrées
        Route::get('/entrees', [\App\Http\Controllers\EntreeController::class, 'index'])->name('entrees.index');
        Route::get('/entrees/create', [\App\Http\Controllers\EntreeController::class, 'create'])->name('entrees.create');
        Route::post('/entrees', [\App\Http\Controllers\EntreeController::class, 'store'])->name('entrees.store');
        Route::get('/entrees/{entree}', [\App\Http\Controllers\EntreeController::class, 'show'])->name('entrees.show');
        Route::get('/entrees/{entree}/edit', [\App\Http\Controllers\EntreeController::class, 'edit'])->name('entrees.edit');
        Route::put('/entrees/{entree}', [\App\Http\Controllers\EntreeController::class, 'update'])->name('entrees.update');
        Route::delete('/entrees/{entree}', [\App\Http\Controllers\EntreeController::class, 'destroy'])->name('entrees.destroy');
    });
    
    // Routes pour la gestion des dépenses (Admin seulement)
    Route::middleware('role:admin,personnel_admin')->group(function () {
        Route::get('/depenses', [DepenseController::class, 'index'])->name('depenses.index');
        Route::get('/depenses/create', [DepenseController::class, 'create'])->name('depenses.create');
        Route::post('/depenses', [DepenseController::class, 'store'])->name('depenses.store');
        Route::get('/depenses/{depense}', [DepenseController::class, 'show'])->name('depenses.show');
        Route::get('/depenses/{depense}/edit', [DepenseController::class, 'edit'])->name('depenses.edit');
        Route::put('/depenses/{depense}', [DepenseController::class, 'update'])->name('depenses.update');
        Route::delete('/depenses/{depense}', [DepenseController::class, 'destroy'])->name('depenses.destroy');
        Route::post('/depenses/{depense}/approuver', [DepenseController::class, 'approuver'])->name('depenses.approuver');
        Route::get('/depenses/{depense}/payer', [DepenseController::class, 'payer'])->name('depenses.payer');
        Route::post('/depenses/{depense}/enregistrer-paiement', [DepenseController::class, 'enregistrerPaiement'])->name('depenses.enregistrer-paiement');
        Route::post('/depenses/{depense}/annuler', [DepenseController::class, 'annuler'])->name('depenses.annuler');
        Route::post('/depenses/creer-salaire-enseignant', [DepenseController::class, 'creerSalaireEnseignant'])->name('depenses.creer-salaire-enseignant');
    });
    
    // Route spéciale pour les rapports de dépenses
    Route::get('/depenses/rapports', [DepenseController::class, 'rapports'])->name('depenses.rapports')->middleware('auth', 'check.permission:depenses.view');
    
    // Routes pour la gestion des entrées d'argent (Admin seulement)
    Route::middleware('role:admin,personnel_admin')->group(function () {
        Route::get('entrees', [EntreeController::class, 'index'])->name('entrees.index')->middleware('check.permission:entrees.view');
        Route::get('entrees/create', [EntreeController::class, 'create'])->name('entrees.create')->middleware('check.permission:entrees.create');
        Route::post('entrees', [EntreeController::class, 'store'])->name('entrees.store')->middleware('check.permission:entrees.create');
        Route::get('entrees/{entree}', [EntreeController::class, 'show'])->name('entrees.show')->middleware('check.permission:entrees.view');
        Route::get('entrees/{entree}/edit', [EntreeController::class, 'edit'])->name('entrees.edit')->middleware('check.permission:entrees.edit');
        Route::put('entrees/{entree}', [EntreeController::class, 'update'])->name('entrees.update')->middleware('check.permission:entrees.edit');
        Route::delete('entrees/{entree}', [EntreeController::class, 'destroy'])->name('entrees.destroy')->middleware('check.permission:entrees.delete');
    });
    
    // Routes pour les rapports comptables (Admin seulement)
    Route::middleware('role:admin,personnel_admin')->group(function () {
        Route::get('/rapports', [RapportController::class, 'index'])->name('rapports.index');
        Route::get('/rapports/unifies', [RapportController::class, 'unifies'])->name('rapports.unifies');
        Route::post('/rapports/detaille', [RapportController::class, 'detaille'])->name('rapports.detaille');
    });
    
    // Routes pour la gestion des salaires des enseignants (Admin et Personnel Admin)
    Route::middleware('role:admin,personnel_admin')->group(function () {
        Route::get('/salaires', [SalaireEnseignantController::class, 'index'])->name('salaires.index')->middleware('check.permission:salaires.view');
        Route::get('/salaires/create', [SalaireEnseignantController::class, 'create'])->name('salaires.create')->middleware('check.permission:salaires.create');
        Route::post('/salaires', [SalaireEnseignantController::class, 'store'])->name('salaires.store')->middleware('check.permission:salaires.create');
        Route::get('/salaires/{salaire}', [SalaireEnseignantController::class, 'show'])->name('salaires.show')->middleware('check.permission:salaires.view');
        Route::get('/salaires/{salaire}/edit', [SalaireEnseignantController::class, 'edit'])->name('salaires.edit')->middleware('check.permission:salaires.edit');
        Route::put('/salaires/{salaire}', [SalaireEnseignantController::class, 'update'])->name('salaires.update')->middleware('check.permission:salaires.edit');
        Route::delete('/salaires/{salaire}', [SalaireEnseignantController::class, 'destroy'])->name('salaires.destroy')->middleware('check.permission:salaires.delete');
        Route::post('/salaires/{salaire}/valider', [SalaireEnseignantController::class, 'valider'])->name('salaires.valider')->middleware('check.permission:salaires.valider');
        Route::get('/salaires/{salaire}/payer', [SalaireEnseignantController::class, 'payerForm'])->name('salaires.payer.form')->middleware('check.permission:salaires.payer');
        Route::post('/salaires/{salaire}/payer', [SalaireEnseignantController::class, 'payer'])->name('salaires.payer')->middleware('check.permission:salaires.payer');
        Route::post('/salaires/calculer-periode', [SalaireEnseignantController::class, 'calculerSalairesPeriode'])->name('salaires.calculer-periode')->middleware('check.permission:salaires.create');
        Route::get('/salaires/rapports', [SalaireEnseignantController::class, 'rapports'])->name('salaires.rapports')->middleware('check.permission:salaires.rapports');
    });
    
    // Routes pour la gestion des tarifs (Admin seulement)
    Route::middleware('role:admin,personnel_admin')->group(function () {
        Route::get('/tarifs', [TarifClasseController::class, 'index'])->name('tarifs.index')->middleware('check.permission:tarifs.view');
        Route::get('/tarifs/create', [TarifClasseController::class, 'create'])->name('tarifs.create')->middleware('check.permission:tarifs.create');
        Route::post('/tarifs', [TarifClasseController::class, 'store'])->name('tarifs.store')->middleware('check.permission:tarifs.create');
        Route::get('/tarifs/{tarif}', [TarifClasseController::class, 'show'])->name('tarifs.show')->middleware('check.permission:tarifs.view');
        Route::get('/tarifs/{tarif}/edit', [TarifClasseController::class, 'edit'])->name('tarifs.edit')->middleware('check.permission:tarifs.edit');
        Route::put('/tarifs/{tarif}', [TarifClasseController::class, 'update'])->name('tarifs.update')->middleware('check.permission:tarifs.edit');
        Route::delete('/tarifs/{tarif}', [TarifClasseController::class, 'destroy'])->name('tarifs.destroy')->middleware('check.permission:tarifs.delete');
        Route::post('/tarifs/{tarif}/toggle-status', [TarifClasseController::class, 'toggleStatus'])->name('tarifs.toggle-status')->middleware('check.permission:tarifs.edit');
        Route::post('/tarifs/{tarif}/duplicate', [TarifClasseController::class, 'duplicate'])->name('tarifs.duplicate')->middleware('check.permission:tarifs.create');
        Route::get('/tarifs-tableau', [TarifClasseController::class, 'tableau'])->name('tarifs.tableau')->middleware('check.permission:tarifs.view');
        Route::get('/api/tarifs/classe/{classe_id}', [TarifClasseController::class, 'getTarifsByClasse'])->name('api.tarifs.by-classe')->middleware('check.permission:tarifs.view');
    });
    
    // Routes pour la comptabilité (Admin seulement)
    Route::middleware(['role:admin,personnel_admin', 'check.permission:comptabilite.view'])->group(function () {
        Route::get('/comptabilite', [ComptabiliteController::class, 'index'])->name('comptabilite.index');
        Route::get('/comptabilite/rapports', [ComptabiliteController::class, 'rapports'])->name('comptabilite.rapports')->middleware('check.permission:comptabilite.rapports');
        Route::get('/comptabilite/entrees', [ComptabiliteController::class, 'entrees'])->name('comptabilite.entrees')->middleware('check.permission:comptabilite.entrees');
        Route::get('/comptabilite/sorties', [ComptabiliteController::class, 'sorties'])->name('comptabilite.sorties')->middleware('check.permission:comptabilite.sorties');
    });
    
    
    // Routes pour la gestion des cartes scolaires (Admin seulement)
    Route::middleware('role:admin,personnel_admin')->group(function () {
        Route::resource('cartes-scolaires', CarteScolaireController::class);
        Route::get('/cartes-scolaires/{cartes_scolaire}/imprimer', [CarteScolaireController::class, 'imprimer'])->name('cartes-scolaires.imprimer');
        Route::get('/cartes-scolaires/{cartes_scolaire}/renouveler', [CarteScolaireController::class, 'renouveler'])->name('cartes-scolaires.renouveler');
        Route::post('/cartes-scolaires/{cartes_scolaire}/traiter-renouvellement', [CarteScolaireController::class, 'traiterRenouvellement'])->name('cartes-scolaires.traiter-renouvellement');
        
        // Routes pour les cartes enseignants
        Route::resource('cartes-enseignants', CarteEnseignantController::class);
        Route::get('/cartes-enseignants/{cartes_enseignant}/imprimer', [CarteEnseignantController::class, 'imprimer'])->name('cartes-enseignants.imprimer');
        Route::get('/cartes-enseignants/{cartes_enseignant}/renouveler', [CarteEnseignantController::class, 'renouveler'])->name('cartes-enseignants.renouveler');
        Route::post('/cartes-enseignants/{cartes_enseignant}/traiter-renouvellement', [CarteEnseignantController::class, 'traiterRenouvellement'])->name('cartes-enseignants.traiter-renouvellement');
    });
    
    // Routes pour les parents - consultation des informations de leurs enfants
    Route::middleware('role:parent')->group(function () {
        // Paiements
        Route::get('/parent/paiements', [ParentPaiementController::class, 'index'])->name('parent.paiements.index');
        Route::get('/parent/paiements/{frais}', [ParentPaiementController::class, 'show'])->name('parent.paiements.show');
        Route::get('/parent/paiements-historique', [ParentPaiementController::class, 'historique'])->name('parent.paiements.historique');
        Route::get('/parent/echeances', [ParentPaiementController::class, 'echeances'])->name('parent.echeances');
        Route::get('/parent/recapitulatif', [ParentPaiementController::class, 'recapitulatif'])->name('parent.recapitulatif');
        
        // Notes
        Route::get('/parent/notes', [\App\Http\Controllers\ParentNoteController::class, 'index'])->name('parent.notes.index');
        Route::get('/parent/notes/eleve/{eleve}', [\App\Http\Controllers\ParentNoteController::class, 'show'])->name('parent.notes.show');
        Route::get('/parent/notes/eleve/{eleve}/bulletin', [\App\Http\Controllers\ParentNoteController::class, 'bulletin'])->name('parent.notes.bulletin');
        Route::get('/parent/notes/eleve/{eleve}/export', [\App\Http\Controllers\ParentNoteController::class, 'export'])->name('parent.notes.export');
        
        // Absences
        Route::get('/parent/absences', [\App\Http\Controllers\ParentAbsenceController::class, 'index'])->name('parent.absences.index');
        Route::get('/parent/absences/eleve/{eleve}', [\App\Http\Controllers\ParentAbsenceController::class, 'show'])->name('parent.absences.show');
        Route::post('/parent/absences/{absence}/justifier', [\App\Http\Controllers\ParentAbsenceController::class, 'justifier'])->name('parent.absences.justifier');
        Route::get('/parent/absences/eleve/{eleve}/rapport', [\App\Http\Controllers\ParentAbsenceController::class, 'rapport'])->name('parent.absences.rapport');
        Route::get('/parent/absences/eleve/{eleve}/export', [\App\Http\Controllers\ParentAbsenceController::class, 'export'])->name('parent.absences.export');
        
        // Notifications
        Route::get('/parent/notifications', [\App\Http\Controllers\ParentNotificationController::class, 'index'])->name('parent.notifications.index');
        Route::get('/parent/notifications/create', [\App\Http\Controllers\ParentNotificationController::class, 'create'])->name('parent.notifications.create');
        Route::post('/parent/notifications', [\App\Http\Controllers\ParentNotificationController::class, 'store'])->name('parent.notifications.store');
        Route::get('/parent/notifications/{notification}', [\App\Http\Controllers\ParentNotificationController::class, 'show'])->name('parent.notifications.show');
        Route::post('/parent/notifications/{notification}/repondre', [\App\Http\Controllers\ParentNotificationController::class, 'repondre'])->name('parent.notifications.repondre');
        Route::put('/parent/notifications/{notification}/marquer-lue', [\App\Http\Controllers\ParentNotificationController::class, 'marquerLue'])->name('parent.notifications.marquer-lue');
        Route::delete('/parent/notifications/{notification}', [\App\Http\Controllers\ParentNotificationController::class, 'destroy'])->name('parent.notifications.destroy');
        Route::put('/parent/notifications/marquer-toutes-lues', [\App\Http\Controllers\ParentNotificationController::class, 'marquerToutesLues'])->name('parent.notifications.marquer-toutes-lues');
        Route::get('/api/parent/notifications/compteur-non-lues', [\App\Http\Controllers\ParentNotificationController::class, 'compterNonLues'])->name('parent.notifications.compteur-non-lues');
    });

    // Routes pour les fonctionnalités élèves
    Route::prefix('student')->name('student.')->middleware(['auth', 'role:student'])->group(function () {
        // Emploi du temps
        Route::get('/emploi-temps', [\App\Http\Controllers\StudentController::class, 'emploiTemps'])->name('emploi-temps');
        
        // Notes
        Route::get('/notes', [\App\Http\Controllers\StudentController::class, 'notes'])->name('notes');
        
        // Absences
        Route::get('/absences', [\App\Http\Controllers\StudentController::class, 'absences'])->name('absences');
        
        // Bulletin
        Route::get('/bulletin', [\App\Http\Controllers\StudentController::class, 'bulletin'])->name('bulletin');
    });
});

// Routes pour les fonctionnalités enseignants
Route::prefix('teacher')->name('teacher.')->middleware(['auth', 'role:teacher'])->group(function () {
        // Emploi du temps
        Route::get('/emploi-temps', [\App\Http\Controllers\TeacherController::class, 'emploiTemps'])->name('emploi-temps');
        
        // Mes élèves
        Route::get('/mes-eleves', [\App\Http\Controllers\TeacherController::class, 'mesEleves'])->name('mes-eleves');
        Route::get('/eleves/{eleve}/profil', [\App\Http\Controllers\TeacherController::class, 'profilEleve'])->name('eleves.profil');
        
        // Classes
        Route::get('/classes', [\App\Http\Controllers\TeacherController::class, 'classes'])->name('classes');
        Route::get('/classes/{classe}/eleves', [\App\Http\Controllers\TeacherController::class, 'elevesClasse'])->name('eleves-classe');
        
        // Notes
        Route::get('/classes/{classe}/selection-matiere', [\App\Http\Controllers\TeacherController::class, 'selectionMatiere'])->name('selection-matiere');
        Route::get('/classes/{classe}/matiere/{matiere}/saisir-notes', [\App\Http\Controllers\TeacherController::class, 'saisirNotes'])->name('saisir-notes');
        Route::post('/classes/{classe}/matiere/{matiere}/enregistrer-notes', [\App\Http\Controllers\TeacherController::class, 'enregistrerNotes'])->name('enregistrer-notes');
        Route::get('/classes/{classe}/historique-notes', [\App\Http\Controllers\TeacherController::class, 'historiqueNotes'])->name('historique-notes');
        Route::get('/classes/{classe}/matiere/{matiere}/historique-notes', [\App\Http\Controllers\TeacherController::class, 'historiqueNotes'])->name('historique-notes-matiere');
        
        // Absences
        Route::get('/absences', [\App\Http\Controllers\TeacherController::class, 'saisirAbsences'])->name('absences');
        Route::get('/absences/classe/{classe}', [\App\Http\Controllers\TeacherController::class, 'saisirAbsencesClasse'])->name('absences.classe');
        Route::post('/absences', [\App\Http\Controllers\TeacherController::class, 'storeAbsences'])->name('absences.store');
        Route::get('/classes/{classe}/saisir-absences', [\App\Http\Controllers\TeacherController::class, 'saisirAbsences'])->name('saisir-absences');
        Route::post('/classes/{classe}/enregistrer-absences', [\App\Http\Controllers\TeacherController::class, 'enregistrerAbsences'])->name('enregistrer-absences');
        Route::get('/classes/{classe}/historique-absences', [\App\Http\Controllers\TeacherController::class, 'historiqueAbsences'])->name('historique-absences');
        
        // Notes (nouvelles routes)
        Route::get('/notes/classe/{classe}', [\App\Http\Controllers\TeacherController::class, 'saisirNotes'])->name('notes.classe');
        Route::post('/notes', [\App\Http\Controllers\TeacherController::class, 'storeNotes'])->name('notes.store');
    });

    // Routes pour la gestion des événements avec permissions
    Route::middleware('auth')->group(function () {
        // Routes de consultation (tous les utilisateurs authentifiés)
        Route::get('/evenements', [EvenementController::class, 'index'])->name('evenements.index');
        Route::get('/evenements/{evenement}', [EvenementController::class, 'show'])->name('evenements.show');
        Route::get('/evenements-calendrier', [EvenementController::class, 'calendrier'])->name('evenements.calendrier');
        
        // Routes de création (permission requise)
        Route::middleware('check.permission:evenements.create')->group(function () {
            Route::get('/evenements/create', [EvenementController::class, 'create'])->name('evenements.create');
            Route::post('/evenements', [EvenementController::class, 'store'])->name('evenements.store');
        });
        
        // Routes de modification (permission requise)
        Route::middleware('check.permission:evenements.edit')->group(function () {
            Route::get('/evenements/{evenement}/edit', [EvenementController::class, 'edit'])->name('evenements.edit');
            Route::put('/evenements/{evenement}', [EvenementController::class, 'update'])->name('evenements.update');
        });
        
        // Routes de suppression (permission requise)
        Route::middleware('check.permission:evenements.delete')->group(function () {
            Route::delete('/evenements/{evenement}', [EvenementController::class, 'destroy'])->name('evenements.destroy');
        });
    });
