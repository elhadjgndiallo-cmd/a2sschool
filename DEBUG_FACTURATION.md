# Guide de Débogage - Facturation Multi-Lignes

## Problème Rapporté
- **5 mois + inscription** : Ne fonctionne pas ❌
- **5 mois seuls** : Fonctionne ✅
- **6 mois seuls** : Ne fonctionne pas ❌

## Causes Possibles

### 1. ✅ CORRIGÉ : Tolérance d'arrondi trop stricte
**Fichier :** `app/Services/FacturationService.php` - Ligne ~371

**Problème :**
Quand il y a beaucoup de lignes (6+), les arrondis multiples s'accumulent :
- Ligne 1 : 120 000.00
- Ligne 2 : 120 000.00
- ...
- Ligne 6 : 120 000.00
- Total : 720 000.00

Mais avec la remise et les arrondis successifs, vous pouvez avoir :
- Total calculé : 720 000.02 ou 719 999.98

**Solution Appliquée :**
```php
// AVANT
if ($montantVerse > $totalDu + 0.01) {
    throw new \RuntimeException(...);
}

// APRÈS
if ($montantVerse > $totalDu + 1) { // Tolérance de 1 GNF pour les arrondis multiples
    throw new \RuntimeException(...);
}
```

### 2. Limite PHP sur les tableaux POST

**Vérification nécessaire :**
Fichier : `php.ini`

```ini
max_input_vars = 1000  ; Doit être >= nombre de champs du formulaire
```

**Test :**
```php
// Ajouter temporairement dans FacturationController::previewTotaux()
\Log::info('Nombre de lignes reçues : ' . count($request->lignes));
\Log::info('Lignes : ', $request->lignes);
```

### 3. Timeout AJAX

**Fichier :** `resources/views/factures/create.blade.php`

Le timeout de 200ms dans `updateRecap()` peut être trop court pour 6+ lignes.

**Solution potentielle :**
```javascript
// Ligne ~348
recapTimer = setTimeout(() => {
    // ...
}, 500); // Augmenter de 200ms à 500ms
```

## Tests à Effectuer

### Test 1 : 6 Mois Sans Remise
```
Lignes : 6 mois de scolarité à 120 000 GNF chacun
Remise : 0 GNF
Total : 720 000 GNF
```
**Résultat attendu :** ✅ Doit fonctionner

### Test 2 : 5 Mois + Inscription Sans Remise
```
Lignes : 
- 5 mois de scolarité à 120 000 GNF = 600 000 GNF
- 1 inscription à 30 000 GNF = 30 000 GNF
Remise : 0 GNF
Total : 630 000 GNF
```
**Résultat attendu :** ✅ Doit fonctionner

### Test 3 : 5 Mois + Inscription Avec Remise
```
Lignes : 
- 5 mois de scolarité à 120 000 GNF = 600 000 GNF
- 1 inscription à 30 000 GNF = 30 000 GNF
Sous-total : 630 000 GNF
Remise : 100 000 GNF
Total : 530 000 GNF
```
**Résultat attendu :** ✅ Doit fonctionner maintenant

### Test 4 : 6 Mois Avec Remise
```
Lignes : 6 mois à 120 000 GNF = 720 000 GNF
Remise : 150 000 GNF
Total : 570 000 GNF
```
**Résultat attendu :** ✅ Doit fonctionner maintenant

### Test 5 : 9 Mois + Inscription Avec Remise 100%
```
Lignes : 
- 9 mois de scolarité à 120 000 GNF = 1 080 000 GNF
- 1 inscription à 30 000 GNF = 30 000 GNF
Sous-total : 1 110 000 GNF
Remise : 1 110 000 GNF (100%)
Total : 0 GNF (payer 1 GNF symbolique)
```
**Résultat attendu :** ✅ Doit fonctionner maintenant

## Activation du Mode Debug

### 1. Activer les logs Laravel
**Fichier :** `.env`
```
APP_DEBUG=true
LOG_LEVEL=debug
```

### 2. Ajouter des logs dans le contrôleur
**Fichier :** `app/Http/Controllers/FacturationController.php`

Dans la méthode `previewTotaux()`, après la ligne 408 :

```php
$selection = [];
foreach ($request->lignes as $id) {
    $ligne = $disponibles->get($id);
    if ($ligne) {
        $selection[] = $ligne;
    }
}

// AJOUTER ICI
\Log::info('=== DEBUG FACTURATION ===');
\Log::info('Nombre de lignes sélectionnées : ' . count($selection));
\Log::info('Remise type : ' . $request->remise_type);
\Log::info('Remise valeur : ' . $request->remise_valeur);
\Log::info('Montant versé : ' . $request->montant_verse);

if (empty($selection)) {
    return response()->json(['error' => 'Aucune ligne valide sélectionnée.'], 422);
}
```

### 3. Consulter les logs
```powershell
Get-Content "c:\xampp\htdocs\a2sschool-main\storage\logs\laravel.log" -Tail 50
```

## Messages d'Erreur Courants

### "Le montant versé dépasse le total dû"
**Cause :** Arrondi ou calcul incorrect
**Solution :** Correction appliquée avec tolérance de 1 GNF

### "Aucune ligne valide sélectionnée"
**Cause :** Les lignes ne sont pas trouvées dans `getLignesDisponibles()`
**Solution :** Vérifier que les frais existent et ne sont pas déjà payés

### "La remise ne peut pas couvrir la totalité de la facture"
**Cause :** Ancienne restriction (devrait être supprimée maintenant)
**Solution :** Vérifier que le code a bien été modifié

### Timeout ou pas de réponse
**Cause :** Calcul trop long ou erreur silencieuse
**Solution :** 
1. Vérifier les logs
2. Augmenter le timeout AJAX
3. Vérifier `max_execution_time` dans php.ini

## Fichiers Modifiés

1. ✅ `app/Services/FacturationService.php`
   - Ligne ~190 : `calculerTotaux()` - Remise illimitée
   - Ligne ~1445 : `calculerMontantRemise()` - Pas de limite
   - Ligne ~371 : `calculerTotauxAvecVersement()` - Tolérance d'arrondi

2. ✅ `resources/views/paiements/show.blade.php`
   - Calcul progression sans remise

## Contact / Support

Si le problème persiste après ces corrections :

1. **Activer le mode debug** (voir ci-dessus)
2. **Tester chaque scénario** un par un
3. **Copier les logs** et les messages d'erreur exacts
4. **Noter le scénario précis** qui ne fonctionne pas :
   - Nombre de lignes
   - Type de lignes (mois, inscription, etc.)
   - Montant de remise
   - Message d'erreur exact

---

**Dernière mise à jour :** 19 Août 2026
**Status :** Correction appliquée - En attente de test
