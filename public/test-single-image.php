<!DOCTYPE html>
<html>
<head>
    <title>Test Image</title>
</head>
<body>
    <h1>Test d'affichage d'une image</h1>
    
    <h2>Image de test : img_68e06979aa5d9.png</h2>
    <img src="/storage/profile_images/img_68e06979aa5d9.png" 
         alt="Test image" 
         style="width: 200px; height: 200px; border: 2px solid red; object-fit: cover;">
    
    <h2>Image avec chemin complet</h2>
    <img src="/a2sschool/storage/profile_images/img_68e06979aa5d9.png" 
         alt="Test image full path" 
         style="width: 200px; height: 200px; border: 2px solid blue; object-fit: cover;">
    
    <h2>Test direct du fichier</h2>
    <p>Fichier existe : <?php echo file_exists(__DIR__ . '/storage/profile_images/img_68e06979aa5d9.png') ? 'Oui' : 'Non'; ?></p>
    <p>Taille du fichier : <?php echo filesize(__DIR__ . '/storage/profile_images/img_68e06979aa5d9.png'); ?> bytes</p>
</body>
</html>
