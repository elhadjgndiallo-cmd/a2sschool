<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'messages';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'expediteur_id',
        'expediteur_type',
        'destinataire_id',
        'destinataire_type',
        'titre',
        'message',
        'type',
        'priorite',
        'statut',
        'lue',
        'parent_id',
        'sujet',
        'contenu',
        'piece_jointe',
        'lu',
        'date_lecture',
        'supprime_expediteur',
        'supprime_destinataire',
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'lue' => 'boolean',
        'lu' => 'boolean',
        'supprime_expediteur' => 'boolean',
        'supprime_destinataire' => 'boolean',
        'date_lecture' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Les attributs qui doivent être cachés pour les tableaux.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Obtenir l'expéditeur du message.
     */
    public function expediteur()
    {
        return $this->belongsTo(Utilisateur::class, 'expediteur_id');
    }

    /**
     * Obtenir le destinataire du message.
     */
    public function destinataire()
    {
        return $this->belongsTo(Utilisateur::class, 'destinataire_id');
    }

    /**
     * Obtenir le message parent (pour les réponses).
     */
    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    /**
     * Obtenir les réponses à ce message.
     */
    public function reponses()
    {
        return $this->hasMany(Message::class, 'parent_id');
    }

    /**
     * Scope pour filtrer les messages non lus.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNonLus($query)
    {
        return $query->where('lu', false);
    }

    /**
     * Scope pour filtrer les messages lus.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLus($query)
    {
        return $query->where('lu', true);
    }

    /**
     * Scope pour filtrer les messages reçus par un utilisateur.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecusPar($query, $userId)
    {
        return $query->where('destinataire_id', $userId)
                     ->where('supprime_destinataire', false);
    }

    /**
     * Scope pour filtrer les messages envoyés par un utilisateur.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnvoyesPar($query, $userId)
    {
        return $query->where('expediteur_id', $userId)
                     ->where('supprime_expediteur', false);
    }

    /**
     * Scope pour filtrer les messages avec pièce jointe.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvecPieceJointe($query)
    {
        return $query->whereNotNull('piece_jointe');
    }

    /**
     * Scope pour rechercher des messages par terme.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('sujet', 'like', "%{$term}%")
              ->orWhere('contenu', 'like', "%{$term}%");
        });
    }

    /**
     * Scope pour filtrer les messages d'une conversation entre deux utilisateurs.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $user1Id
     * @param  int  $user2Id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConversation($query, $user1Id, $user2Id)
    {
        return $query->where(function($q) use ($user1Id, $user2Id) {
            $q->where(function($q2) use ($user1Id, $user2Id) {
                $q2->where('expediteur_id', $user1Id)
                   ->where('destinataire_id', $user2Id)
                   ->where('supprime_expediteur', false);
            })->orWhere(function($q2) use ($user1Id, $user2Id) {
                $q2->where('expediteur_id', $user2Id)
                   ->where('destinataire_id', $user1Id)
                   ->where('supprime_destinataire', false);
            });
        });
    }

    /**
     * Vérifier si le message a une pièce jointe.
     *
     * @return bool
     */
    public function hasPieceJointe()
    {
        return !empty($this->piece_jointe);
    }

    /**
     * Obtenir l'URL de téléchargement de la pièce jointe.
     *
     * @return string|null
     */
    public function getUrlPieceJointeAttribute()
    {
        if ($this->hasPieceJointe()) {
            return route('api.messages.telecharger-piece-jointe', $this->id);
        }
        
        return null;
    }

    /**
     * Obtenir le nom du fichier de la pièce jointe.
     *
     * @return string|null
     */
    public function getNomPieceJointeAttribute()
    {
        if ($this->hasPieceJointe()) {
            return pathinfo($this->piece_jointe, PATHINFO_BASENAME);
        }
        
        return null;
    }

    /**
     * Obtenir l'extension du fichier de la pièce jointe.
     *
     * @return string|null
     */
    public function getExtensionPieceJointeAttribute()
    {
        if ($this->hasPieceJointe()) {
            return pathinfo($this->piece_jointe, PATHINFO_EXTENSION);
        }
        
        return null;
    }

    /**
     * Obtenir l'icône correspondant au format de la pièce jointe.
     *
     * @return string|null
     */
    public function getIconePieceJointeAttribute()
    {
        if (!$this->hasPieceJointe()) {
            return null;
        }
        
        $extension = strtolower($this->extension_piece_jointe);
        
        $icones = [
            'pdf' => 'fa-file-pdf',
            'doc' => 'fa-file-word',
            'docx' => 'fa-file-word',
            'xls' => 'fa-file-excel',
            'xlsx' => 'fa-file-excel',
            'ppt' => 'fa-file-powerpoint',
            'pptx' => 'fa-file-powerpoint',
            'txt' => 'fa-file-alt',
            'zip' => 'fa-file-archive',
            'rar' => 'fa-file-archive',
            'jpg' => 'fa-file-image',
            'jpeg' => 'fa-file-image',
            'png' => 'fa-file-image',
            'gif' => 'fa-file-image',
        ];
        
        return $icones[$extension] ?? 'fa-file';
    }
}