# Monitoring des Performances - Comptabilité

## Comment vérifier les performances

### 1. Temps de chargement des pages

Dans le navigateur (F12 → Onglet Réseau) :
- **Bon** : < 2 secondes
- **Acceptable** : 2-5 secondes  
- **Lent** : 5-10 secondes
- **Problématique** : > 10 secondes

### 2. Activer le Query Log Laravel

Ajouter temporairement dans `app/Providers/AppServiceProvider.php` :

```php
public function boot()
{
    if (config('app.debug')) {
        \DB::listen(function($query) {
            \Log::info('Query:', [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time . 'ms'
            ]);
        });
    }
}
```

Ensuite consulter : `storage/logs/laravel.log`

### 3. Utiliser Laravel Debugbar (Optionnel)

Installation :
```bash
composer require barryvdh/laravel-debugbar --dev
```

Cela affiche :
- Nombre de requêtes SQL
- Temps d'exécution
- Mémoire utilisée
- N+1 queries détectées

### 4. Commandes utiles

#### Vérifier la configuration PHP actuelle
```bash
php -i | findstr "max_execution_time"
php -i | findstr "memory_limit"
```

#### Nettoyer le cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

#### Optimiser pour la production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Indicateurs de Performance

### Base de données

Vérifier le nombre d'enregistrements :
```sql
SELECT 
    'entrees' as table_name, COUNT(*) as count FROM entrees
UNION ALL
SELECT 'paiements', COUNT(*) FROM paiements
UNION ALL
SELECT 'depenses', COUNT(*) FROM depenses
UNION ALL
SELECT 'salaires_enseignants', COUNT(*) FROM salaires_enseignants;
```

### Requêtes lentes

Activer le slow query log MySQL :
```sql
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2; -- Log requêtes > 2 secondes
```

Fichier : `C:\xampp\mysql\data\slow-query.log`

## Alertes à Surveiller

### 🔴 Critique
- Temps de réponse > 30 secondes
- Erreur 500 ou 504 (timeout)
- Mémoire > 512M

### 🟡 Attention
- Temps de réponse > 10 secondes
- Plus de 100 requêtes SQL par page
- Mémoire > 256M

### 🟢 Normal
- Temps de réponse < 5 secondes
- Moins de 50 requêtes SQL par page
- Mémoire < 128M

## Checklist d'Optimisation

- [ ] Index créés sur les tables principales
- [ ] LIMIT appliqué sur les requêtes lourdes
- [ ] Pagination activée sur les listes
- [ ] Cache activé pour les données statiques
- [ ] Eager loading (with()) utilisé correctement
- [ ] Timeouts PHP augmentés si nécessaire
- [ ] Mémoire suffisante allouée

## En Cas de Problème

1. **Vérifier les logs** : `storage/logs/laravel.log`
2. **Regarder le processus MySQL** : Gestionnaire des tâches Windows
3. **Tester avec moins de données** : Filtrer par date
4. **Augmenter progressivement** : 
   - LIMIT 50 → 100 → 200
   - Timeout 300s → 600s
   - Memory 512M → 1G

## Contact Support

Si les optimisations ne suffisent pas :
- Envisager une migration vers un serveur plus puissant
- Utiliser un système de queue pour les tâches lourdes
- Implémenter un cache Redis
- Archiver les anciennes données

---
**Dernière mise à jour** : 03/06/2026
