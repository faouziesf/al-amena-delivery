<?php
/**
 * Script pour générer les icônes PNG pour le manifest.json
 * Exécutez ce script une seule fois : php generate-icons.php
 */

// Créer une icône 192x192
$img192 = imagecreatetruecolor(192, 192);

// Fond violet (couleur du thème)
$bgColor = imagecolorallocate($img192, 139, 92, 246); // #8B5CF6
$textColor = imagecolorallocate($img192, 255, 255, 255);

// Remplir le fond
imagefilledrectangle($img192, 0, 0, 192, 192, $bgColor);

// Ajouter le texte "AA" (Al-Amena)
$fontSize = 80;
$angle = 0;
$x = 40;
$y = 130;

// Utiliser une police système ou une police simple
imagettftext($img192, $fontSize, $angle, $x, $y, $textColor, __DIR__ . '/public/arial.ttf', 'AA');

// Si arial.ttf n'existe pas, dessiner des formes simples
if (!file_exists(__DIR__ . '/public/arial.ttf')) {
    // Dessiner un rectangle pour la lettre A
    imagefilledrectangle($img192, 50, 50, 70, 140, $textColor);
    imagefilledrectangle($img192, 50, 50, 90, 70, $textColor);
    imagefilledrectangle($img192, 70, 90, 90, 110, $bgColor);
    imagefilledrectangle($img192, 90, 50, 110, 140, $textColor);
    
    // Deuxième A
    imagefilledrectangle($img192, 122, 50, 142, 140, $textColor);
    imagefilledrectangle($img192, 122, 50, 162, 70, $textColor);
    imagefilledrectangle($img192, 142, 90, 162, 110, $bgColor);
    imagefilledrectangle($img192, 142, 50, 162, 140, $textColor);
}

// Sauvegarder l'icône 192x192
imagepng($img192, __DIR__ . '/public/icon-192.png');
imagedestroy($img192);

// Créer une icône 512x512 (version plus grande)
$img512 = imagecreatetruecolor(512, 512);

$bgColor512 = imagecolorallocate($img512, 139, 92, 246);
$textColor512 = imagecolorallocate($img512, 255, 255, 255);

imagefilledrectangle($img512, 0, 0, 512, 512, $bgColor512);

// Dessiner des formes simples pour l'icône 512
// Grand rectangle arrondi
$white = imagecolorallocate($img512, 255, 255, 255);
imagefilledrectangle($img512, 100, 150, 180, 350, $white);
imagefilledrectangle($img512, 100, 150, 280, 200, $white);
imagefilledrectangle($img512, 180, 250, 280, 300, $bgColor512);
imagefilledrectangle($img512, 260, 150, 340, 350, $white);

imagefilledrectangle($img512, 370, 150, 450, 350, $white);
imagefilledrectangle($img512, 370, 150, 550, 200, $white);
imagefilledrectangle($img512, 450, 250, 550, 300, $bgColor512);
imagefilledrectangle($img512, 530, 150, 610, 350, $white);

imagepng($img512, __DIR__ . '/public/icon-512.png');
imagedestroy($img512);

echo "✅ Icônes créées avec succès!\n";
echo "   - icon-192.png (192x192)\n";
echo "   - icon-512.png (512x512)\n";
echo "\nVous pouvez maintenant supprimer ce fichier generate-icons.php\n";
