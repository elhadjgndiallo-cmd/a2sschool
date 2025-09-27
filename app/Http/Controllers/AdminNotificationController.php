<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminNotificationController extends Controller
{
    /**
     * Afficher la liste des messages reçus par l'administration
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->canAccessAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        $query = Message::where('destinataire_id', $user->id)
            ->orWhere('destinataire_type', 'admin')
            ->orWhere('destinataire_type', 'personnel_admin')
            ->with(['expediteur', 'destinataire'])
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('priorite')) {
            $query->where('priorite', $request->priorite);
        }

        if ($request->filled('expediteur_type')) {
            $query->where('expediteur_type', $request->expediteur_type);
        }

        $messages = $query->paginate(15);

        return view('admin.notifications.index', compact('messages'));
    }

    /**
     * Afficher les détails d'un message
     */
    public function show(Message $message)
    {
        $user = Auth::user();
        
        if (!$user->canAccessAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        // Vérifier que l'admin a accès à ce message
        if ($message->destinataire_id !== $user->id && 
            !in_array($message->destinataire_type, ['admin', 'personnel_admin'])) {
            abort(403, 'Accès non autorisé à ce message.');
        }

        // Marquer comme lu si c'est le destinataire
        if ($message->destinataire_id === $user->id && !$message->lue) {
            $message->update(['lue' => true]);
        }

        // Récupérer les réponses
        $reponses = Message::where('parent_id', $message->id)
            ->orderBy('created_at', 'asc')
            ->with(['expediteur', 'destinataire'])
            ->get();

        return view('admin.notifications.show', compact('message', 'reponses'));
    }

    /**
     * Répondre à un message
     */
    public function repondre(Request $request, Message $message)
    {
        $user = Auth::user();
        
        if (!$user->canAccessAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        // Vérifier que l'admin peut répondre à ce message
        if ($message->destinataire_id !== $user->id && 
            !in_array($message->destinataire_type, ['admin', 'personnel_admin'])) {
            abort(403, 'Vous ne pouvez pas répondre à ce message.');
        }

        $request->validate([
            'message' => 'required|string'
        ]);

        // Créer la réponse
        Message::create([
            'expediteur_id' => $user->id,
            'expediteur_type' => $user->role === 'admin' ? 'admin' : 'personnel_admin',
            'destinataire_id' => $message->expediteur_id,
            'destinataire_type' => 'parent',
            'titre' => 'Re: ' . $message->titre,
            'message' => $request->message,
            'sujet' => 'Re: ' . $message->titre,
            'contenu' => $request->message,
            'type' => 'reponse',
            'priorite' => $message->priorite,
            'statut' => 'envoyee',
            'lue' => false,
            'parent_id' => $message->id
        ]);

        // Marquer le message original comme répondu
        $message->update(['statut' => 'repondue']);

        return redirect()->route('admin.notifications.show', $message)
            ->with('success', 'Votre réponse a été envoyée avec succès.');
    }

    /**
     * Marquer un message comme lu
     */
    public function marquerLue(Message $message)
    {
        $user = Auth::user();
        
        if (!$user->canAccessAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        if ($message->destinataire_id === $user->id) {
            $message->update(['lue' => true]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Supprimer un message
     */
    public function destroy(Message $message)
    {
        $user = Auth::user();
        
        if (!$user->canAccessAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        // Vérifier que l'admin peut supprimer ce message
        if ($message->destinataire_id !== $user->id && 
            !in_array($message->destinataire_type, ['admin', 'personnel_admin'])) {
            abort(403, 'Vous ne pouvez pas supprimer ce message.');
        }

        $message->delete();

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Message supprimé avec succès.');
    }

    /**
     * Marquer toutes les messages comme lues
     */
    public function marquerToutesLues()
    {
        $user = Auth::user();
        
        if (!$user->canAccessAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        Message::where('destinataire_id', $user->id)
            ->where('lue', false)
            ->update(['lue' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Compter les messages non lus
     */
    public function compterNonLues()
    {
        $user = Auth::user();
        
        if (!$user->canAccessAdmin()) {
            return response()->json(['count' => 0]);
        }

        $count = Message::where('destinataire_id', $user->id)
            ->where('lue', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Statistiques des messages
     */
    public function statistiques()
    {
        $user = Auth::user();
        
        if (!$user->canAccessAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        $stats = [
            'total_messages' => Message::where('destinataire_id', $user->id)->count(),
            'messages_non_lus' => Message::where('destinataire_id', $user->id)->where('lue', false)->count(),
            'messages_par_type' => Message::where('destinataire_id', $user->id)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'messages_par_priorite' => Message::where('destinataire_id', $user->id)
                ->selectRaw('priorite, COUNT(*) as count')
                ->groupBy('priorite')
                ->pluck('count', 'priorite'),
        ];

        return view('admin.notifications.statistiques', compact('stats'));
    }
}