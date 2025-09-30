#!/bin/bash

echo "🚀 INSTALLATION A2S SCHOOL MANAGEMENT SYSTEM"
echo "============================================="
echo ""

# Vérifier si Composer est installé
if ! command -v composer &> /dev/null; then
    echo "❌ Composer n'est pas installé. Veuillez l'installer d'abord."
    exit 1
fi

# Vérifier si PHP est installé
if ! command -v php &> /dev/null; then
    echo "❌ PHP n'est pas installé. Veuillez l'installer d'abord."
    exit 1
fi

echo "✅ Prérequis vérifiés"
echo ""

# Installer les dépendances
echo "📦 Installation des dépendances..."
composer install --no-dev --optimize-autoloader

# Copier le fichier .env
if [ ! -f .env ]; then
    echo "📝 Création du fichier .env..."
    cp .env.example .env
    echo "⚠️  N'oubliez pas de configurer votre base de données dans le fichier .env"
fi

# Générer la clé d'application
echo "🔑 Génération de la clé d'application..."
php artisan key:generate

# Créer le lien symbolique
echo "🔗 Création du lien symbolique..."
php artisan storage:link

# Nettoyer les caches
echo "🧹 Nettoyage des caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Exécuter les migrations automatiquement
echo "🗄️  Exécution des migrations..."
if php artisan migrate:auto; then
    echo "✅ Migrations exécutées avec succès!"
else
    echo "⚠️  Tentative avec le script d'installation..."
    php install-migrations.php
fi

echo ""
echo "🎉 Installation terminée!"
echo ""
echo "📋 Prochaines étapes:"
echo "1. Configurez votre base de données dans le fichier .env"
echo "2. Accédez à votre site dans le navigateur"
echo "3. Suivez l'assistant de configuration"
echo ""
echo "🌐 Votre système A2S School est prêt!"
