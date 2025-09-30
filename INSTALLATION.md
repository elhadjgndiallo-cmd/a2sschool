# 🚀 Installation A2S School Management System

## 📋 Prérequis

- PHP 8.1 ou supérieur
- Composer
- MySQL 5.7 ou supérieur
- Serveur web (Apache/Nginx)

## 🔧 Installation automatique

### 1. Cloner le projet
```bash
git clone https://github.com/elhadjgndiallo-cmd/a2sschool.git
cd a2sschool
```

### 2. Installer les dépendances
```bash
composer install
```

### 3. Configuration de l'environnement
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configuration de la base de données
Modifiez le fichier `.env` avec vos paramètres de base de données :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Migration automatique (RECOMMANDÉ)
```bash
php artisan migrate:auto
```

**OU** si la commande automatique n'est pas disponible :
```bash
php install-migrations.php
```

### 6. Créer le lien symbolique pour le stockage
```bash
php artisan storage:link
```

### 7. Nettoyer les caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 🎯 Première utilisation

1. **Accédez à votre site** dans le navigateur
2. **Suivez l'assistant de configuration** pour créer le compte administrateur principal
3. **Configurez les informations de l'établissement**
4. **Commencez à utiliser le système !**

## 🔧 Commandes utiles

### Migration automatique
```bash
php artisan migrate:auto
```

### Vérifier le statut des migrations
```bash
php artisan migrate:status
```

### Réinitialiser la base de données (ATTENTION: supprime toutes les données)
```bash
php artisan migrate:fresh
```

## 🆘 Résolution de problèmes

### Erreur "Table already exists"
```bash
php artisan migrate:auto
```

### Erreur de clé étrangère
Les migrations ont été corrigées pour éviter les erreurs de clés étrangères.

### Problème de permissions
```bash
php artisan app:fix-admin-permissions
```

## 📞 Support

En cas de problème, vérifiez :
1. Que toutes les migrations sont exécutées : `php artisan migrate:status`
2. Que les liens symboliques sont créés : `php artisan storage:link`
3. Que les caches sont nettoyés : `php artisan config:clear`

## 🎉 Félicitations !

Votre système de gestion scolaire A2S est maintenant prêt à être utilisé !
