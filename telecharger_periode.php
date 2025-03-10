<?php
// Vérifier si l'utilisateur est connecté et a l'autorisation de télécharger
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

// Récupérer les années de début et de fin à partir des paramètres URL
$startYear = isset($_GET['startYear']) ? (int) $_GET['startYear'] : null;
$endYear = isset($_GET['endYear']) ? (int) $_GET['endYear'] : null;

// Vérifier que les années sont valides
if ($startYear === null || $endYear === null || $startYear > $endYear) {
    die("Période invalide.");
}

// Dossier de base
$dossier_base = 'documents/';

// Créer un fichier ZIP temporaire
$nomZip = 'periode_' . $startYear . '-' . $endYear . '.zip';
$fichierZip = sys_get_temp_dir() . '/' . $nomZip;

// Créer une instance de ZipArchive
$zip = new ZipArchive();

if ($zip->open($fichierZip, ZipArchive::CREATE) !== TRUE) {
    die("Impossible d'ouvrir le fichier ZIP pour la compression.");
}

// Ajouter les fichiers pour chaque mois de la période
for ($year = $startYear; $year <= $endYear; $year++) {
    // Définir les mois pour cette année
    $startMonth = ($year == $startYear) ? 9 : 1; // Début en septembre pour l'année de début
    $endMonth = ($year == $endYear) ? 8 : 12;  // Fin en août pour l'année de fin

    for ($month = $startMonth; $month <= $endMonth; $month++) {
        $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT); // Format mois avec 2 chiffres
        $folder = $dossier_base . $year . '/' . $monthStr . '/';

        // Vérifier si le dossier existe
        if (is_dir($folder)) {
            // Ajouter tous les fichiers du dossier dans l'archive
            $dossierRealPath = realpath($folder);
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dossierRealPath),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($dossierRealPath) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }
    }
}

// Fermer l'archive ZIP
$zip->close();

// Envoyer les headers pour forcer le téléchargement
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($fichierZip) . '"');
header('Content-Length: ' . filesize($fichierZip));

// Lire et envoyer le fichier
readfile($fichierZip);

// Supprimer le fichier temporaire après téléchargement
unlink($fichierZip);
exit;