# 🌐 Guide de résolution des images sur le serveur

## Problème
Les images s'affichent en local mais pas sur le serveur de production.

## Solutions pour le serveur

### 1. 🔗 Créer le lien symbolique
```bash
# Sur le serveur
cd /path/to/your/project
php artisan storage:link
```

### 2. 📁 Vérifier les permissions
```bash
# Définir les permissions correctes
chmod -R 755 storage/
chmod -R 755 public/storage/
chown -R www-data:www-data storage/
chown -R www-data:www-data public/storage/
```

### 3. 🔧 Configuration Apache (.htaccess)
Le fichier `public/storage/.htaccess` a été créé avec la configuration suivante :

```apache
# Configuration pour le serveur web
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Autoriser l'accès aux fichiers statiques
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule ^(.*)$ - [L]
</IfModule>

# Autoriser l'accès aux images
<FilesMatch "\.(jpg|jpeg|png|gif|webp|svg|css|js|ico|pdf|txt)$">
    Order Allow,Deny
    Allow from all
    Header set Cache-Control "public, max-age=31536000"
    Header set Expires "Thu, 31 Dec 2025 23:59:59 GMT"
</FilesMatch>

# Sécurité - Bloquer l'exécution de scripts
<FilesMatch "\.(php|phtml|php3|php4|php5|pl|py|jsp|asp|sh|cgi)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

### 4. 🧪 Script de test
Un script de test a été créé : `test-images-server.php`

**Accès** : `http://votre-domaine.com/test-images-server.php`

Ce script :
- ✅ Vérifie le lien symbolique
- ✅ Liste les images disponibles
- ✅ Affiche les images avec liens directs
- ✅ Teste l'accès aux fichiers

### 5. 🔍 Vérifications sur le serveur

#### A. Vérifier le lien symbolique
```bash
ls -la public/storage
# Doit afficher : public/storage -> ../storage/app/public
```

#### B. Vérifier les permissions
```bash
ls -la storage/app/public/
ls -la public/storage/
# Les permissions doivent être 755
```

#### C. Tester l'accès direct
```bash
curl -I http://votre-domaine.com/storage/photos/admin/
curl -I http://votre-domaine.com/storage/profile_images/
```

### 6. 🚨 Problèmes courants et solutions

#### Problème : Erreur 403 Forbidden
**Solution** :
```bash
# Vérifier les permissions
chmod -R 755 storage/
chmod -R 755 public/storage/
chown -R www-data:www-data storage/
chown -R www-data:www-data public/storage/
```

#### Problème : Erreur 404 Not Found
**Solution** :
```bash
# Recréer le lien symbolique
rm -rf public/storage
php artisan storage:link
```

#### Problème : Images ne se chargent pas
**Solution** :
```bash
# Vérifier la configuration du serveur web
# Pour Apache, vérifier que mod_rewrite est activé
# Pour Nginx, vérifier la configuration des fichiers statiques
```

### 7. 🔧 Configuration Nginx (si applicable)

```nginx
location /storage {
    alias /path/to/your/project/public/storage;
    expires 1y;
    add_header Cache-Control "public, immutable";
    
    location ~* \.(jpg|jpeg|png|gif|webp|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 8. 📋 Checklist de déploiement

- [ ] Lien symbolique créé : `php artisan storage:link`
- [ ] Permissions correctes : `chmod -R 755 storage/ public/storage/`
- [ ] Propriétaire correct : `chown -R www-data:www-data storage/ public/storage/`
- [ ] Fichier `.htaccess` présent dans `public/storage/`
- [ ] Script de test accessible : `test-images-server.php`
- [ ] Images accessibles via URL directe
- [ ] Configuration du serveur web correcte

### 9. 🎯 Test final

1. **Accédez au script de test** : `http://votre-domaine.com/test-images-server.php`
2. **Vérifiez que toutes les images s'affichent**
3. **Testez l'accès direct** à quelques images
4. **Vérifiez dans l'application** que les images s'affichent

### 10. 🧹 Nettoyage

Une fois que tout fonctionne, supprimez le script de test :
```bash
rm test-images-server.php
```

## Status
✅ **Solution complète** pour le déploiement des images sur le serveur
✅ **Script de test** pour vérifier le fonctionnement
✅ **Configuration Apache/Nginx** incluse
✅ **Instructions détaillées** pour le déploiement
