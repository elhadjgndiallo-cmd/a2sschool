<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UtilisateurController;
use App\Http\Controllers\Api\EleveController;
use App\Http\Controllers\Api\ParentController;
use App\Http\Controllers\Api\EnseignantController;
use App\Http\Controllers\Api\ClasseController;
use App\Http\Controllers\Api\MatiereController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\AbsenceController;
use App\Http\Controllers\Api\PaiementController;
use App\Http\Controllers\Api\EmploiDuTempsController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\EvenementController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ConfigurationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\StatistiqueController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Routes publiques
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/reset-request', [AuthController::class, 'resetRequest']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

// Routes protégées par authentification
Route::middleware('auth:sanctum')->group(function () {
    // Authentification
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/password/change', [AuthController::class, 'changePassword']);
    
    // Utilisateurs
    Route::apiResource('utilisateurs', UtilisateurController::class);
    Route::put('/utilisateurs/{id}/statut', [UtilisateurController::class, 'changerStatut']);
    Route::put('/utilisateurs/{id}/password', [UtilisateurController::class, 'changerMotDePasse']);
    Route::delete('/utilisateurs/{id}/photo', [UtilisateurController::class, 'supprimerPhoto']);
    
    // Élèves
    Route::apiResource('eleves', EleveController::class);
    Route::get('/eleves/{id}/notes', [EleveController::class, 'notes']);
    Route::get('/eleves/{id}/absences', [EleveController::class, 'absences']);
    
    // Parents
    Route::apiResource('parents', ParentController::class);
    Route::get('/parents/{id}/eleves', [ParentController::class, 'eleves']);
    Route::get('/parents/{id}/paiements', [ParentController::class, 'paiements']);
    
    // Enseignants
    Route::apiResource('enseignants', EnseignantController::class);
    Route::get('/enseignants/{id}/matieres', [EnseignantController::class, 'matieres']);
    Route::get('/enseignants/{id}/classes', [EnseignantController::class, 'classes']);
    Route::get('/enseignants/{id}/emploi-du-temps', [EnseignantController::class, 'emploiDuTemps']);
    Route::post('/enseignants/{id}/matieres/{matiere_id}', [EnseignantController::class, 'associerMatiere']);
    Route::delete('/enseignants/{id}/matieres/{matiere_id}', [EnseignantController::class, 'dissocierMatiere']);
    Route::post('/enseignants/{id}/classes/{classe_id}', [EnseignantController::class, 'associerClasse']);
    Route::delete('/enseignants/{id}/classes/{classe_id}', [EnseignantController::class, 'dissocierClasse']);
    
    // Classes
    Route::apiResource('classes', ClasseController::class);
    Route::get('/classes/{id}/eleves', [ClasseController::class, 'eleves']);
    Route::get('/classes/{id}/enseignants', [ClasseController::class, 'enseignants']);
    Route::get('/classes/{id}/emploi-du-temps', [ClasseController::class, 'emploiDuTemps']);
    Route::get('/classes/{id}/statistiques', [ClasseController::class, 'statistiques']);
    
    // Matières
    Route::apiResource('matieres', MatiereController::class);
    Route::get('/matieres/{id}/enseignants', [MatiereController::class, 'enseignants']);
    Route::get('/matieres/{id}/classes', [MatiereController::class, 'classes']);
    Route::post('/matieres/{id}/enseignants/{enseignant_id}', [MatiereController::class, 'associerEnseignant']);
    Route::delete('/matieres/{id}/enseignants/{enseignant_id}', [MatiereController::class, 'dissocierEnseignant']);
    Route::post('/matieres/{id}/classes/{classe_id}', [MatiereController::class, 'associerClasse']);
    Route::delete('/matieres/{id}/classes/{classe_id}', [MatiereController::class, 'dissocierClasse']);
    
    // Notes
    Route::apiResource('notes', NoteController::class);
    Route::post('/notes/bulk', [NoteController::class, 'storeBulk']);
    Route::get('/eleves/{id}/bulletin', [NoteController::class, 'bulletinEleve']);
    
    // Absences
    Route::apiResource('absences', AbsenceController::class);
    Route::post('/absences/bulk', [AbsenceController::class, 'storeBulk']);
    Route::put('/absences/{id}/justifier', [AbsenceController::class, 'justifier']);
    Route::get('/absences/statistiques', [AbsenceController::class, 'statistiques']);
    
    // Paiements
    Route::apiResource('paiements', PaiementController::class);
    Route::get('/eleves/{id}/paiements', [PaiementController::class, 'paiementsEleve']);
    Route::get('/paiements/{id}/recu', [PaiementController::class, 'genererRecu']);
    Route::put('/paiements/{id}/statut', [PaiementController::class, 'changerStatut']);
    Route::get('/paiements/statistiques', [PaiementController::class, 'statistiques']);
    
    // Emploi du temps
    Route::apiResource('emploi-du-temps', EmploiDuTempsController::class);
    Route::post('/emploi-du-temps/bulk', [EmploiDuTempsController::class, 'storeBulk']);
    Route::get('/classes/{id}/emploi-du-temps', [EmploiDuTempsController::class, 'emploiDuTempsClasse']);
    Route::get('/enseignants/{id}/emploi-du-temps', [EmploiDuTempsController::class, 'emploiDuTempsEnseignant']);
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::post('/notifications/envoyer-multiple', [NotificationController::class, 'envoyerMultiple']);
    Route::post('/notifications/envoyer-par-role', [NotificationController::class, 'envoyerParRole']);
    Route::put('/notifications/{id}/marquer-comme-lue', [NotificationController::class, 'marquerCommeLue']);
    Route::put('/notifications/marquer-toutes-comme-lues', [NotificationController::class, 'marquerToutesCommeLues']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications/supprimer-toutes-lues', [NotificationController::class, 'supprimerToutesLues']);
    Route::get('/notifications/compteur-non-lues', [NotificationController::class, 'compteurNonLues']);
    
    // Événements du calendrier
    Route::apiResource('evenements', EvenementController::class);
    Route::get('/classes/{classe_id}/evenements', [EvenementController::class, 'evenementsClasse']);
    Route::get('/mes-evenements', [EvenementController::class, 'mesEvenements']);
    Route::get('/evenements-a-venir', [EvenementController::class, 'evenementsAVenir']);
    
    // Documents
    // Routes Document temporairement désactivées - pas besoin de gérer les documents pour le moment
    /*
    Route::apiResource('documents', DocumentController::class);
    Route::get('documents/telecharger/{id}', [DocumentController::class, 'telecharger'])->name('api.documents.telecharger');
    Route::get('documents/classe/{classeId}', [DocumentController::class, 'documentsClasse']);
    Route::get('documents/mes-documents', [DocumentController::class, 'mesDocuments']);
    Route::get('documents/recents', [DocumentController::class, 'documentsRecents']);
    */

    // Configuration du système
    Route::get('configurations', [ConfigurationController::class, 'index']);
    Route::post('configurations/update', [ConfigurationController::class, 'update']);
    Route::post('configurations/update-multiple', [ConfigurationController::class, 'updateMultiple']);
    Route::post('configurations/update-logo', [ConfigurationController::class, 'updateLogo']);
    Route::post('configurations/reset-defaults', [ConfigurationController::class, 'resetDefaults']);
    
    // Messages
    Route::get('/messages', [MessageController::class, 'index']);
    Route::get('/messages/envoyes', [MessageController::class, 'envoyes']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::post('/messages/envoyer-multiple', [MessageController::class, 'envoyerMultiple']);
    Route::post('/messages/envoyer-par-role', [MessageController::class, 'envoyerParRole']);
    Route::get('/messages/{id}', [MessageController::class, 'show']);
    Route::put('/messages/{id}/marquer-comme-lu', [MessageController::class, 'marquerCommeLu']);
    Route::put('/messages/marquer-tous-comme-lus', [MessageController::class, 'marquerTousCommeLus']);
    Route::delete('/messages/{id}', [MessageController::class, 'destroy']);
    Route::get('/messages/{id}/telecharger-piece-jointe', [MessageController::class, 'telechargerPieceJointe'])->name('api.messages.telecharger-piece-jointe');
    Route::get('/messages/compteur-non-lus', [MessageController::class, 'compteurNonLus']);
    Route::get('/messages/conversations', [MessageController::class, 'conversations']);
    Route::get('/messages/conversation/{utilisateurId}', [MessageController::class, 'conversation']);

    // Statistiques
    Route::get('/statistiques', [StatistiqueController::class, 'index']);
    Route::get('/statistiques/eleves', [StatistiqueController::class, 'eleves']);
    Route::get('/statistiques/enseignants', [StatistiqueController::class, 'enseignants']);
    Route::get('/statistiques/notes', [StatistiqueController::class, 'notes']);
    Route::get('/statistiques/absences', [StatistiqueController::class, 'absences']);
    Route::get('/statistiques/paiements', [StatistiqueController::class, 'paiements']);
});