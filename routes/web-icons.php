<?php

use Illuminate\Support\Facades\Route;

/**
 * Routes pour générer les icônes PWA à la volée
 */

// Icône 192x192
Route::get('/icon-192.png', function () {
    $img = imagecreatetruecolor(192, 192);
    
    // Couleurs
    $bgColor = imagecolorallocate($img, 139, 92, 246); // Violet #8B5CF6
    $white = imagecolorallocate($img, 255, 255, 255);
    
    // Fond violet
    imagefilledrectangle($img, 0, 0, 192, 192, $bgColor);
    
    // Dessiner "AA" simplifié
    // Première lettre A
    imagefilledrectangle($img, 40, 50, 55, 140, $white);
    imagefilledrectangle($img, 40, 50, 75, 65, $white);
    imagefilledrectangle($img, 60, 50, 75, 140, $white);
    imagefilledrectangle($img, 45, 85, 70, 100, $bgColor); // Trou du A
    
    // Deuxième lettre A
    imagefilledrectangle($img, 117, 50, 132, 140, $white);
    imagefilledrectangle($img, 117, 50, 152, 65, $white);
    imagefilledrectangle($img, 137, 50, 152, 140, $white);
    imagefilledrectangle($img, 122, 85, 147, 100, $bgColor); // Trou du A
    
    // Retourner l'image
    ob_start();
    imagepng($img);
    $imageData = ob_get_clean();
    imagedestroy($img);
    
    return response($imageData)
        ->header('Content-Type', 'image/png')
        ->header('Cache-Control', 'public, max-age=31536000');
});

// Icône 512x512
Route::get('/icon-512.png', function () {
    $img = imagecreatetruecolor(512, 512);
    
    // Couleurs
    $bgColor = imagecolorallocate($img, 139, 92, 246); // Violet #8B5CF6
    $white = imagecolorallocate($img, 255, 255, 255);
    
    // Fond violet
    imagefilledrectangle($img, 0, 0, 512, 512, $bgColor);
    
    // Dessiner "AA" plus grand
    // Première lettre A
    imagefilledrectangle($img, 100, 130, 130, 380, $white);
    imagefilledrectangle($img, 100, 130, 200, 170, $white);
    imagefilledrectangle($img, 170, 130, 200, 380, $white);
    imagefilledrectangle($img, 115, 230, 185, 280, $bgColor); // Trou du A
    
    // Deuxième lettre A
    imagefilledrectangle($img, 312, 130, 342, 380, $white);
    imagefilledrectangle($img, 312, 130, 412, 170, $white);
    imagefilledrectangle($img, 382, 130, 412, 380, $white);
    imagefilledrectangle($img, 327, 230, 397, 280, $bgColor); // Trou du A
    
    // Retourner l'image
    ob_start();
    imagepng($img);
    $imageData = ob_get_clean();
    imagedestroy($img);
    
    return response($imageData)
        ->header('Content-Type', 'image/png')
        ->header('Cache-Control', 'public, max-age=31536000');
});
