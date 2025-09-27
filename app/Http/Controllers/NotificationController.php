<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\Utilisateur;

class NotificationController extends Controller
{
    /**
     * Afficher la liste des notifications de l'utilisateur connecté
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Notification::where('utilisateur_id', $user->id);
        
        // Filtrage par statut de lecture
        if ($request->has('lue')) {
            $query->where('lue', $request->boolean('lue'));
        }
        
        // Filtrage par type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        // Tri par défaut : plus récentes en premier
        $query->orderBy('created_at', 'desc');
        
        // Pagination
        $perPage = $request->input('per_page', 20);
        $notifications = $query->paginate($perPage);
        
        // Statistiques
        $stats = [
            'total' => Notification::where('utilisateur_id', $user->id)->count(),
            'non_lues' => Notification::where('utilisateur_id', $user->id)->nonLues()->count(),
            'lues' => Notification::where('utilisateur_id', $user->id)->lues()->count(),
        ];
        
        // Répartition par type
        $parType = Notification::where('utilisateur_id', $user->id)
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->get()
            ->pluck('total', 'type');
        
        return view('notifications.index', compact('notifications', 'stats', 'parType'));
    }

    /**
     * Marquer une notification comme lue
     */
    public function marquerCommeLue($id)
    {
        $notification = Notification::where('utilisateur_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
        
        $notification->update(['lue' => true]);
        
        return redirect()->back()->with('success', 'Notification marquée comme lue');
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function marquerToutesCommeLues()
    {
        try {
            $count = Notification::where('utilisateur_id', Auth::id())
                ->where('lue', false)
                ->count();
            
            if ($count > 0) {
                Notification::where('utilisateur_id', Auth::id())
                    ->where('lue', false)
                    ->update(['lue' => true]);
                
                return redirect()->route('notifications.index')
                    ->with('success', "{$count} notification(s) marquée(s) comme lue(s)");
            } else {
                return redirect()->route('notifications.index')
                    ->with('info', 'Aucune notification non lue à marquer');
            }
        } catch (\Exception $e) {
            \Log::error('Erreur lors du marquage des notifications comme lues: ' . $e->getMessage());
            return redirect()->route('notifications.index')
                ->with('error', 'Erreur lors du marquage des notifications');
        }
    }

    /**
     * Supprimer une notification
     */
    public function destroy($id)
    {
        $notification = Notification::where('utilisateur_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
        
        $notification->delete();
        
        return redirect()->back()->with('success', 'Notification supprimée');
    }

    /**
     * Supprimer toutes les notifications lues
     */
    public function supprimerToutesLues(Request $request)
    {
        try {
            $count = Notification::where('utilisateur_id', Auth::id())
                ->where('lue', true)
                ->count();
            
            if ($count > 0) {
                Notification::where('utilisateur_id', Auth::id())
                    ->where('lue', true)
                    ->delete();
                
                $message = "{$count} notification(s) supprimée(s)";
            } else {
                $message = 'Aucune notification lue à supprimer';
            }
            
            return redirect()->route('notifications.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression des notifications lues: ' . $e->getMessage());
            
            return redirect()->route('notifications.index')
                ->with('error', 'Erreur lors de la suppression des notifications');
        }
    }

    /**
     * Créer une nouvelle notification (pour les admins)
     */
    public function create()
    {
        // Vérifier que l'utilisateur est admin
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Accès non autorisé');
        }
        
        $utilisateurs = Utilisateur::orderBy('nom')->get();
        
        return view('notifications.create', compact('utilisateurs'));
    }

    /**
     * Enregistrer une nouvelle notification
     */
    public function store(Request $request)
    {
        // Vérifier que l'utilisateur est admin
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Accès non autorisé');
        }
        
        $request->validate([
            'utilisateurs' => 'required|array|min:1',
            'utilisateurs.*' => 'required|exists:utilisateurs,id',
            'titre' => 'required|string|max:100',
            'message' => 'required|string|max:255',
            'type' => 'required|string|in:info,success,warning,danger',
            'lien' => 'nullable|string|max:255',
            'icone' => 'nullable|string|max:50',
        ]);

        $notificationsCreees = 0;
        
        foreach ($request->utilisateurs as $utilisateurId) {
            Notification::create([
                'utilisateur_id' => $utilisateurId,
                'titre' => $request->titre,
                'message' => $request->message,
                'type' => $request->type,
                'lien' => $request->lien,
                'icone' => $request->icone,
                'lue' => false,
            ]);
            $notificationsCreees++;
        }
        
        return redirect()->route('notifications.index')
            ->with('success', "{$notificationsCreees} notification(s) envoyée(s) avec succès");
    }

    /**
     * API pour récupérer le compteur de notifications non lues
     */
    public function compteurNonLues()
    {
        $count = Notification::where('utilisateur_id', Auth::id())
            ->nonLues()
            ->count();
        
        return response()->json(['count' => $count]);
    }

    /**
     * API pour récupérer les dernières notifications
     */
    public function dernieres()
    {
        $notifications = Notification::where('utilisateur_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return response()->json($notifications);
    }
}


