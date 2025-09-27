<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'utilisateur_id',
        'titre',
        'message',
        'type',
        'lien',
        'icone',
        'lue',
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'lue' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtenir l'utilisateur associé à cette notification.
     */
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }

    /**
     * Scope pour filtrer les notifications non lues.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNonLues($query)
    {
        return $query->where('lue', false);
    }

    /**
     * Scope pour filtrer les notifications lues.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLues($query)
    {
        return $query->where('lue', true);
    }

    /**
     * Scope pour filtrer les notifications par type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour filtrer les notifications récentes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $jours
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecentes($query, $jours = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($jours));
    }

    /**
     * Marquer cette notification comme lue.
     *
     * @return bool
     */
    public function marquerCommeLue()
    {
        return $this->update(['lue' => true]);
    }

    /**
     * Marquer cette notification comme non lue.
     *
     * @return bool
     */
    public function marquerCommeNonLue()
    {
        return $this->update(['lue' => false]);
    }
}