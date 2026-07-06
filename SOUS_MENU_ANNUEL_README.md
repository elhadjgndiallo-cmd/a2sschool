# Sous-menu Annuel - Gestion des Notes

## 📋 Description

Nouveau sous-menu "**Annuel**" ajouté dans la section **Gestion des Notes** qui affiche les résultats annuels des élèves.

## ✨ Fonctionnalités

### 1. **Page d'accueil** (`/notes/annuel`)
- Liste toutes les classes de l'année scolaire active
- Bouton pour accéder aux résultats de chaque classe

### 2. **Résultats Annuels** (`/notes/annuel/resultats/{classe}`)
- **Tableau principal avec les colonnes :**
  - Matricule
  - Nom
  - Prénom
  - Moyenne T1 + Rang T1
  - Moyenne T2 + Rang T2
  - Moyenne Annuelle + Rang Annuel

- **Statistiques affichées :**
  - Meilleure moyenne de la classe
  - Moyenne générale de la classe
  - Nombre d'élèves avec notes

- **Badge spécial pour les 3 premiers :**
  - 🏆 1er : Badge vert "Excellent"
  - 🥈 2ème : Badge bleu "Très bien"
  - 🥉 3ème : Badge orange "Bien"

### 3. **Impression Détail des Notes** (`/notes/annuel/resultats/{classe}/detail-notes/imprimer`)
- **Format paysage (A4 Landscape)**
- **Contenu :**
  - Matricule
  - Nom
  - Prénom
  - Matière
  - Coefficient
  - Note Annuelle (moyenne des 3 trimestres)
  - Appréciation (Excellent, Très bien, Bien, Assez bien, Passable, Insuffisant)

- **Design optimisé pour l'impression**
- Bouton retour vers les résultats

## 🔧 Fichiers Modifiés/Créés

### Contrôleur
- `app/Http/Controllers/NoteController.php`
  - `annuelIndex()` : Page d'accueil
  - `annuelResultats()` : Affichage des résultats
  - `annuelDetailNotesImprimer()` : Impression détail notes

### Modèle
- `app/Models/Note.php`
  - `calculerMoyenneAnnuelleEleveMatiere()` : Calcul moyenne annuelle par matière

### Routes
- `routes/web.php`
  - `GET /notes/annuel` → Index
  - `GET /notes/annuel/resultats/{classe}` → Résultats
  - `GET /notes/annuel/resultats/{classe}/detail-notes/imprimer` → Impression

### Vues
- `resources/views/notes/annuel/index.blade.php` : Page d'accueil
- `resources/views/notes/annuel/resultats.blade.php` : Tableau des résultats
- `resources/views/notes/annuel/detail-notes-imprimer.blade.php` : Impression détail

## 📊 Calcul des Moyennes

### Moyenne Trimestrielle
```
Moyenne T1 = Σ(Note × Coefficient) / Σ(Coefficient)
```

### Moyenne Annuelle
```
Moyenne Annuelle = (Moyenne T1 + Moyenne T2 + Moyenne T3) / Nombre de trimestres
```

### Note Annuelle par Matière
```
Note Annuelle Matière = (Moyenne T1 + Moyenne T2 + Moyenne T3) / 3
```

## 🎯 Permissions Requises

- **Voir les résultats** : `notes.view`
- **Imprimer** : `notes.view`

Les enseignants ne voient que leurs classes.
Les administrateurs voient toutes les classes.

## 🚀 Comment Accéder

1. Connectez-vous à l'application
2. Menu principal → **Gestion des Notes**
3. Cliquez sur **Annuel**
4. Sélectionnez une classe
5. Consultez les résultats ou imprimez le détail

## 📝 Notes Techniques

- Les rangs sont calculés dynamiquement à chaque affichage
- Les élèves sans notes apparaissent avec "-" dans les colonnes
- Le classement se fait par moyenne décroissante
- L'impression est au format paysage pour avoir plus d'espace

## ✅ Tests

Pour tester :
```bash
# Vider le cache
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Accéder à la page
http://localhost/notes/annuel
```

## 🎨 Interface

- Design moderne avec Bootstrap 5
- Icônes Font Awesome
- Responsive (mobile, tablette, desktop)
- Couleurs cohérentes avec le reste de l'application
