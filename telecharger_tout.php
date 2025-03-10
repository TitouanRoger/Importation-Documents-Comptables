<?php
// Vérifier si l'utilisateur est connecté et a l'autorisation de télécharger
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

// Récupérer l'année et le mois à partir des paramètres URL
$year = isset($_GET['year']) ? $_GET['year'] : null;
$month = isset($_GET['month']) ? $_GET['month'] : null;

$dossier = 'documents/';

// Si une année et un mois sont spécifiés, on ne prend que ce sous-dossier
if ($year && $month) {
    $dossier .= $year . '/' . $month . '/';
} elseif ($year) {
    // Si seule l'année est spécifiée, on prend le dossier pour cette année
    $dossier .= $year . '/';
}

// Vérifier si le dossier existe
if (!is_dir($dossier)) {
    die("Le dossier $dossier n'existe pas.");
}

// Fonction pour compresser un dossier en fichier ZIP
function compresserDossier($dossier, $fichierZip)
{
    $zip = new ZipArchive();
    if ($zip->open($fichierZip, ZipArchive::CREATE) === TRUE) {
        // Ajouter tous les fichiers du dossier dans l'archive
        $dossierRealPath = realpath($dossier);
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
        $zip->close();
        return true;
    }
    return false;
}

// Nom du fichier ZIP basé sur les paramètres (année et mois)
if ($year && $month) {
    $nomZip = 'documents_' . $year . '_' . $month . '.zip';
} elseif ($year) {
    $nomZip = 'documents_' . $year . '.zip';
} else {
    $nomZip = 'documents.zip';
}

$fichierZip = sys_get_temp_dir() . '/' . $nomZip;

// Compresser le dossier
if (compresserDossier($dossier, $fichierZip)) {
    // Envoyer les headers pour forcer le téléchargement
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($fichierZip) . '"');
    header('Content-Length: ' . filesize($fichierZip));

    // Lire et envoyer le fichier
    readfile($fichierZip);

    // Supprimer le fichier temporaire après téléchargement
    unlink($fichierZip);
    exit;
} else {
    echo "Une erreur est survenue lors de la compression du dossier.";
}