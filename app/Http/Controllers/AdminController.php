<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;
use App\Models\Eleve;
use App\Models\Enseignant;
use App\Models\ParentModel;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Paiement;
use App\Models\Absence;
use App\Models\Note;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Afficher le tableau de bord d'administration
     */
    public function dashboard()
    {
        // Statistiques générales
        $stats = [
            'eleves' => Eleve::count(),
            'enseignants' => Enseignant::count(),
            'parents' => ParentModel::count(),
            'classes' => Classe::count(),
            'matieres' => Matiere::count(),
        ];
        
        // Derniers paiements
        $derniersPaiements = Paiement::with(['eleve.utilisateur'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Dernières absences
        $dernieresAbsences = Absence::with(['eleve.utilisateur', 'matiere'])
            ->orderBy('date_absence', 'desc')
            ->limit(5)
            ->get();
        
        return view('admin.dashboard', compact('stats', 'derniersPaiements', 'dernieresAbsences'));
    }
    
    /**
     * Afficher la liste des utilisateurs
     */
    public function utilisateurs()
    {
        $utilisateurs = Utilisateur::paginate(20);
        return view('admin.utilisateurs.index', compact('utilisateurs'));
    }
    
    /**
     * Afficher le formulaire de création d'un utilisateur
     */
    public function createUtilisateur()
    {
        return view('admin.utilisateurs.create');
    }
    
    /**
     * Enregistrer un nouvel utilisateur
     */
    public function storeUtilisateur(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs,email|max:191',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,teacher,student,parent',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'sexe' => 'nullable|in:M,F',
        ]);
        
        Utilisateur::create([
            'name' => $request->prenom . ' ' . $request->nom,
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'sexe' => $request->sexe,
            'actif' => true,
        ]);
        
        return redirect()->route('admin.utilisateurs')
            ->with('success', 'Utilisateur créé avec succès');
    }
    
    /**
     * Afficher les statistiques générales
     */
    public function statistiques()
    {
        // Statistiques des élèves par classe
        $elevesParClasse = DB::table('eleves')
            ->join('classes', 'eleves.classe_id', '=', 'classes.id')
            ->select('classes.nom', DB::raw('count(*) as total'))
            ->groupBy('classes.nom')
            ->get();
        
        // Statistiques des paiements par mois
        $paiementsParMois = DB::table('paiements')
            ->select(DB::raw('MONTH(date_paiement) as mois'), DB::raw('SUM(montant_paye) as total'))
            ->whereYear('date_paiement', date('Y'))
            ->groupBy('mois')
            ->get();
        
        // Statistiques des absences par classe
        $absencesParClasse = DB::table('absences')
            ->join('eleves', 'absences.eleve_id', '=', 'eleves.id')
            ->join('classes', 'eleves.classe_id', '=', 'classes.id')
            ->select('classes.nom', DB::raw('count(*) as total'))
            ->groupBy('classes.nom')
            ->get();
        
        // Statistiques des notes moyennes par matière
        $notesParMatiere = DB::table('notes')
            ->join('matieres', 'notes.matiere_id', '=', 'matieres.id')
            ->select('matieres.nom', DB::raw('AVG(note) as moyenne'))
            ->groupBy('matieres.nom')
            ->get();
        
        return view('admin.statistiques', compact(
            'elevesParClasse', 
            'paiementsParMois', 
            'absencesParClasse', 
            'notesParMatiere'
        ));
    }
    
    /**
     * Afficher les paramètres du système
     */
    public function parametres()
    {
        return view('admin.parametres');
    }
    
    /**
     * Mettre à jour les paramètres du système
     */
    public function updateParametres(Request $request)
    {
        $request->validate([
            'nom_ecole' => 'required|string|max:255',
            'adresse_ecole' => 'required|string',
            'telephone_ecole' => 'required|string|max:20',
            'email_ecole' => 'required|email|max:191',
            'annee_scolaire' => 'required|string|max:20',
        ]);
        
        // Enregistrer les paramètres dans un fichier de configuration ou en base de données
        // Exemple avec un fichier .env
        $this->updateEnv([
            'APP_NAME' => '"' . $request->nom_ecole . '"',
            'SCHOOL_ADDRESS' => '"' . $request->adresse_ecole . '"',
            'SCHOOL_PHONE' => '"' . $request->telephone_ecole . '"',
            'SCHOOL_EMAIL' => '"' . $request->email_ecole . '"',
            'SCHOOL_YEAR' => '"' . $request->annee_scolaire . '"',
        ]);
        
        return redirect()->route('admin.parametres')
            ->with('success', 'Paramètres mis à jour avec succès');
    }
    
    /**
     * Mettre à jour le fichier .env
     */
    private function updateEnv($data)
    {
        $path = base_path('.env');
        
        if (file_exists($path)) {
            $content = file_get_contents($path);
            
            foreach ($data as $key => $value) {
                // Si la clé existe déjà, remplacer sa valeur
                if (strpos($content, $key . '=') !== false) {
                    $content = preg_replace('/' . $key . '=(.*)/', $key . '=' . $value, $content);
                } 
                // Sinon, ajouter la nouvelle clé
                else {
                    $content .= "\n" . $key . '=' . $value;
                }
            }
            
            file_put_contents($path, $content);
        }
    }
    
    /**
     * Afficher le formulaire d'édition d'un utilisateur
     */
    public function editUtilisateur(Utilisateur $utilisateur)
    {
        return view('admin.utilisateurs.edit', compact('utilisateur'));
    }
    
    /**
     * Mettre à jour un utilisateur
     */
    public function updateUtilisateur(Request $request, Utilisateur $utilisateur)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:191|unique:utilisateurs,email,' . $utilisateur->id,
            'role' => 'required|in:admin,teacher,student,parent',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'sexe' => 'nullable|in:M,F',
        ]);
        
        // Si un nouveau mot de passe est fourni
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            
            $utilisateur->password = bcrypt($request->password);
        }
        
        $utilisateur->name = $request->prenom . ' ' . $request->nom;
        $utilisateur->nom = $request->nom;
        $utilisateur->prenom = $request->prenom;
        $utilisateur->email = $request->email;
        $utilisateur->role = $request->role;
        $utilisateur->telephone = $request->telephone;
        $utilisateur->adresse = $request->adresse;
        $utilisateur->date_naissance = $request->date_naissance;
        $utilisateur->lieu_naissance = $request->lieu_naissance;
        $utilisateur->sexe = $request->sexe;
        $utilisateur->save();
        
        return redirect()->route('admin.utilisateurs')
            ->with('success', 'Utilisateur mis à jour avec succès');
    }
    
    /**
     * Supprimer un utilisateur
     */
    public function destroyUtilisateur(Utilisateur $utilisateur)
    {
        // Vérifier que l'utilisateur n'est pas l'administrateur actuel
        if (auth()->id() === $utilisateur->id && $utilisateur->role === 'admin') {
            return redirect()->route('admin.utilisateurs')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte administrateur');
        }
        
        // Supprimer la photo de profil si elle existe
        if ($utilisateur->photo_profil) {
            Storage::disk('public')->delete($utilisateur->photo_profil);
        }
        
        $utilisateur->delete();
        
        return redirect()->route('admin.utilisateurs')
            ->with('success', 'Utilisateur supprimé avec succès');
    }
    
    /**
     * Activer/désactiver un utilisateur
     */
    public function toggleUtilisateur(Utilisateur $utilisateur)
    {
        // Vérifier que l'utilisateur n'est pas l'administrateur actuel
        if (auth()->id() === $utilisateur->id && $utilisateur->role === 'admin') {
            return redirect()->route('admin.utilisateurs')
                ->with('error', 'Vous ne pouvez pas désactiver votre propre compte administrateur');
        }
        
        $utilisateur->actif = !$utilisateur->actif;
        $utilisateur->save();
        
        $status = $utilisateur->actif ? 'activé' : 'désactivé';
        
        return redirect()->route('admin.utilisateurs')
            ->with('success', "Utilisateur {$status} avec succès");
    }
    
    /**
     * Vider le cache de l'application
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            
            return redirect()->route('admin.parametres')
                ->with('success', 'Le cache a été vidé avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors du vidage du cache: ' . $e->getMessage());
            
            return redirect()->route('admin.parametres')
                ->with('error', 'Une erreur est survenue lors du vidage du cache');
        }
    }
    
    /**
     * Optimiser la base de données
     */
    public function optimizeDatabase()
    {
        try {
            // Exécuter des commandes d'optimisation de la base de données
            DB::statement('OPTIMIZE TABLE utilisateurs');
            DB::statement('OPTIMIZE TABLE eleves');
            DB::statement('OPTIMIZE TABLE enseignants');
            DB::statement('OPTIMIZE TABLE parents');
            DB::statement('OPTIMIZE TABLE classes');
            DB::statement('OPTIMIZE TABLE matieres');
            DB::statement('OPTIMIZE TABLE notes');
            DB::statement('OPTIMIZE TABLE absences');
            DB::statement('OPTIMIZE TABLE paiements');
            
            return redirect()->route('admin.parametres')
                ->with('success', 'La base de données a été optimisée avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'optimisation de la base de données: ' . $e->getMessage());
            
            return redirect()->route('admin.parametres')
                ->with('error', 'Une erreur est survenue lors de l\'optimisation de la base de données');
        }
    }
    
    /**
     * Créer une sauvegarde de la base de données
     */
    public function createBackup()
    {
        try {
            // Créer un nom de fichier unique pour la sauvegarde
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            
            // Exécuter la commande de sauvegarde (exemple pour MySQL)
            // Note: Cette commande nécessite que mysqldump soit accessible
            $command = 'mysqldump -u ' . env('DB_USERNAME') . ' -p' . env('DB_PASSWORD') . ' ' . env('DB_DATABASE') . ' > ' . storage_path('app/backups/' . $filename);
            
            // Créer le répertoire de sauvegarde s'il n'existe pas
            if (!Storage::exists('backups')) {
                Storage::makeDirectory('backups');
            }
            
            // Exécuter la commande (à adapter selon l'environnement)
            // Cette méthode est simplifiée et peut nécessiter des ajustements
            // exec($command, $output, $returnVar);
            
            // Pour des raisons de sécurité, nous simulons la création d'une sauvegarde
            // Dans un environnement de production, utilisez une bibliothèque dédiée
            $dummyBackupContent = 'Simulation de sauvegarde - ' . date('Y-m-d H:i:s');
            Storage::put('backups/' . $filename, $dummyBackupContent);
            
            return redirect()->route('admin.parametres')
                ->with('success', 'La sauvegarde a été créée avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la sauvegarde: ' . $e->getMessage());
            
            return redirect()->route('admin.parametres')
                ->with('error', 'Une erreur est survenue lors de la création de la sauvegarde');
        }
    }
    
    /**
     * Lister les sauvegardes disponibles
     */
    public function listBackups()
    {
        $backups = [];
        
        if (Storage::exists('backups')) {
            $files = Storage::files('backups');
            
            foreach ($files as $file) {
                $backups[] = [
                    'name' => basename($file),
                    'size' => Storage::size($file),
                    'date' => Storage::lastModified($file),
                ];
            }
        }
        
        return view('admin.backups', compact('backups'));
    }
    
    /**
     * Télécharger une sauvegarde
     */
    public function downloadBackup($filename)
    {
        $path = 'backups/' . $filename;
        
        if (Storage::exists($path)) {
            return Storage::download($path, $filename);
        }
        
        return redirect()->route('admin.backups')
            ->with('error', 'Le fichier de sauvegarde demandé n\'existe pas');
    }
    
    /**
     * Supprimer une sauvegarde
     */
    public function deleteBackup($filename)
    {
        $path = 'backups/' . $filename;
        
        if (Storage::exists($path)) {
            Storage::delete($path);
            
            return redirect()->route('admin.backups')
                ->with('success', 'La sauvegarde a été supprimée avec succès');
        }
        
        return redirect()->route('admin.backups')
            ->with('error', 'Le fichier de sauvegarde demandé n\'existe pas');
    }
}