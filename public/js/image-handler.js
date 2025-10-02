/**
 * Gestionnaire d'images pour les formulaires
 * Améliore l'affichage et la gestion des images de profil
 */

class ImageHandler {
    constructor() {
        this.init();
    }

    init() {
        // Gérer les changements de fichiers d'image
        document.addEventListener('change', (e) => {
            if (e.target.type === 'file' && e.target.accept && e.target.accept.includes('image/*')) {
                this.handleImageUpload(e.target);
            }
        });

        // Gérer les erreurs d'images
        document.addEventListener('error', (e) => {
            if (e.target.tagName === 'IMG') {
                this.handleImageError(e.target);
            }
        });
    }

    handleImageUpload(input) {
        const file = input.files[0];
        if (!file) return;

        // Vérifier la taille du fichier (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            alert('Le fichier est trop volumineux. Taille maximale: 2MB');
            input.value = '';
            return;
        }

        // Vérifier le type de fichier
        if (!file.type.startsWith('image/')) {
            alert('Veuillez sélectionner un fichier image valide.');
            input.value = '';
            return;
        }

        // Afficher la prévisualisation
        const reader = new FileReader();
        reader.onload = (e) => {
            this.updateImagePreview(e.target.result, input);
        };
        reader.readAsDataURL(file);
    }

    updateImagePreview(imageSrc, input) {
        // Chercher le conteneur de prévisualisation
        const previewContainer = document.getElementById('photoPreview');
        if (!previewContainer) return;

        // Créer l'image de prévisualisation
        const img = document.createElement('img');
        img.src = imageSrc;
        img.className = 'img-thumbnail rounded-circle';
        img.style.cssText = 'width: 150px; height: 150px; object-fit: cover;';
        img.alt = 'Photo de profil';

        // Remplacer le contenu du conteneur
        previewContainer.innerHTML = '';
        previewContainer.appendChild(img);
    }

    handleImageError(img) {
        console.warn('Erreur de chargement d\'image:', img.src);
        
        // Remplacer par un placeholder avec initiales
        const placeholder = this.createPlaceholder(img);
        if (placeholder && img.parentNode) {
            img.parentNode.replaceChild(placeholder, img);
        }
    }

    createPlaceholder(img) {
        // Extraire le nom de l'image alt pour créer les initiales
        const alt = img.alt || 'Utilisateur';
        const name = alt.replace('Photo de ', '');
        const initials = this.getInitials(name);
        const bgColor = this.getColorFromName(name);

        const placeholder = document.createElement('div');
        placeholder.className = img.className;
        placeholder.style.cssText = img.style.cssText + ` background-color: ${bgColor}; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;`;
        placeholder.textContent = initials;

        return placeholder;
    }

    getInitials(name) {
        const words = name.trim().split(' ');
        let initials = '';
        
        words.forEach(word => {
            if (word.length > 0) {
                initials += word.charAt(0).toUpperCase();
            }
        });
        
        return initials.substring(0, 2);
    }

    getColorFromName(name) {
        const colors = [
            '#e74c3c', '#3498db', '#2ecc71', '#f39c12', '#9b59b6',
            '#1abc9c', '#34495e', '#e67e22', '#95a5a6', '#8e44ad'
        ];
        
        let hash = 0;
        for (let i = 0; i < name.length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        
        return colors[Math.abs(hash) % colors.length];
    }
}

// Initialiser le gestionnaire d'images
document.addEventListener('DOMContentLoaded', () => {
    new ImageHandler();
});
