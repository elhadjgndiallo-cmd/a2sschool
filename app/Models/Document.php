<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'documents';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'titre',
        'description',
        'type',
        'categorie',
        'chemin',
        'taille',
        'format',
        'public',
        'telechargements',
        'classe_id',
        'createur_id',
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'public' => 'boolean',
        'taille' => 'integer',
        'telechargements' => 'integer',
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
     * Obtenir la classe associée au document.
     */
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    /**
     * Obtenir le créateur du document.
     */
    public function createur()
    {
        return $this->belongsTo(Utilisateur::class, 'createur_id');
    }

    /**
     * Scope pour filtrer les documents par type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour filtrer les documents par catégorie.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $categorie
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCategorie($query, $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    /**
     * Scope pour filtrer les documents publics.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('public', true);
    }

    /**
     * Scope pour filtrer les documents par classe.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $classeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForClasse($query, $classeId)
    {
        return $query->where('classe_id', $classeId);
    }

    /**
     * Scope pour filtrer les documents par créateur.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $createurId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCreateur($query, $createurId)
    {
        return $query->where('createur_id', $createurId);
    }

    /**
     * Scope pour filtrer les documents récents.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope pour rechercher des documents par terme.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('titre', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Obtenir la taille formatée du document.
     *
     * @return string
     */
    public function getTailleFormatteeAttribute()
    {
        $bytes = $this->taille;
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Obtenir l'URL de téléchargement du document.
     *
     * @return string
     */
    public function getUrlTelechargementAttribute()
    {
        return route('api.documents.telecharger', $this->id);
    }

    /**
     * Obtenir l'icône correspondant au format du document.
     *
     * @return string
     */
    public function getIconeAttribute()
    {
        $format = strtolower($this->format);
        
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
        
        return $icones[$format] ?? 'fa-file';
    }
}