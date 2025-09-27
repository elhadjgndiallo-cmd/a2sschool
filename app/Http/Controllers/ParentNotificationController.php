<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentNotificationController extends Controller
{
    /**
     * Afficher la liste des notifications du parent
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        if (!$parent) {
            abort(403, 'Profil parent non trouvé');
        }

        $query = Message::where('destinataire_id', $user->id)
            ->orWhere('destinataire_type', 'parent')
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $notifications = $query->paginate(15);

        return view('parent.notifications.index', compact('notifications'));
    }

    /**
     * Afficher le formulaire pour créer une nouvelle notification
     */
    public function create()
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        if (!$parent) {
            abort(403, 'Profil parent non trouvé');
        }

        // Récupérer les administrateurs et personnel d'administration
        $destinataires = Utilisateur::whereIn('role', ['admin', 'personnel_admin'])
            ->with('parent', 'enseignant', 'personnelAdministration')
            ->get();

        return view('parent.notifications.create', compact('destinataires'));
    }

    /**
     * Enregistrer une nouvelle notification
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        if (!$parent) {
            abort(403, 'Profil parent non trouvé');
        }

        $request->validate([
            'destinataire_id' => 'required|exists:utilisateurs,id',
            'titre' => 'required|string|max:255',
            'message' => 'required|string',
            'priorite' => 'required|in:faible,moyenne,haute,urgente',
            'type' => 'required|in:question,demande,information,plainte,autre'
        ]);

        $message = Message::create([
            'expediteur_id' => $user->id,
            'expediteur_type' => 'parent',
            'destinataire_id' => $request->destinataire_id,
            'destinataire_type' => 'admin',
            'titre' => $request->titre,
            'message' => $request->message,
            'sujet' => $request->titre, // Remplir le champ sujet avec le titre
            'contenu' => $request->message, // Remplir le champ contenu avec le message
            'type' => $request->type,
            'priorite' => $request->priorite,
            'statut' => 'envoyee',
            'lue' => false
        ]);

        return redirect()->route('parent.notifications.index')
            ->with('success', 'Votre message a été envoyé avec succès.');
    }

    /**
     * Afficher les détails d'une notification
     */
    public function show(Message $notification)
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        if (!$parent) {
            abort(403, 'Profil parent non trouvé');
        }

        // Vérifier que le parent a accès à cette notification
        if ($notification->destinataire_id !== $user->id && $notification->expediteur_id !== $user->id) {
            abort(403, 'Accès non autorisé à cette notification.');
        }

        // Marquer comme lue si c'est le destinataire
        if ($notification->destinataire_id === $user->id && !$notification->lue) {
            $notification->update(['lue' => true]);
        }

        // Récupérer les réponses
        $reponses = Message::where('parent_id', $notification->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('parent.notifications.show', compact('notification', 'reponses'));
    }

    /**
     * Répondre à une notification
     */
    public function repondre(Request $request, Message $notification)
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        if (!$parent) {
            abort(403, 'Profil parent non trouvé');
        }

        // Vérifier que le parent peut répondre à cette notification
        if ($notification->destinataire_id !== $user->id) {
            abort(403, 'Vous ne pouvez pas répondre à cette notification.');
        }

        $request->validate([
            'message' => 'required|string'
        ]);

        // Créer la réponse
        Message::create([
            'expediteur_id' => $user->id,
            'expediteur_type' => 'parent',
            'destinataire_id' => $notification->expediteur_id,
            'destinataire_type' => 'admin',
            'titre' => 'Re: ' . $notification->titre,
            'message' => $request->message,
            'sujet' => 'Re: ' . $notification->titre, // Remplir le champ sujet
            'contenu' => $request->message, // Remplir le champ contenu
            'type' => 'reponse',
            'priorite' => $notification->priorite,
            'statut' => 'envoyee',
            'lue' => false,
            'parent_id' => $notification->id
        ]);

        // Marquer la notification originale comme répondue
        $notification->update(['statut' => 'repondue']);

        return redirect()->route('parent.notifications.show', $notification)
            ->with('success', 'Votre réponse a été envoyée avec succès.');
    }

    /**
     * Marquer une notification comme lue
     */
    public function marquerLue(Message $notification)
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        if (!$parent) {
            abort(403, 'Profil parent non trouvé');
        }

        if ($notification->destinataire_id === $user->id) {
            $notification->update(['lue' => true]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Supprimer une notification
     */
    public function destroy(Message $notification)
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        if (!$parent) {
            abort(403, 'Profil parent non trouvé');
        }

        // Vérifier que le parent peut supprimer cette notification
        if ($notification->destinataire_id !== $user->id && $notification->expediteur_id !== $user->id) {
            abort(403, 'Vous ne pouvez pas supprimer cette notification.');
        }

        $notification->delete();

        return redirect()->route('parent.notifications.index')
            ->with('success', 'Notification supprimée avec succès.');
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function marquerToutesLues()
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        if (!$parent) {
            abort(403, 'Profil parent non trouvé');
        }

        Message::where('destinataire_id', $user->id)
            ->where('lue', false)
            ->update(['lue' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Compter les notifications non lues
     */
    public function compterNonLues()
    {
        $user = Auth::user();
        $parent = $user->parent;
        
        if (!$parent) {
            return response()->json(['count' => 0]);
        }

        $count = Message::where('destinataire_id', $user->id)
            ->where('lue', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}
