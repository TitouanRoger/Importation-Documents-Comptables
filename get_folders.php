<?php
$basePath = 'documents/';

// Fonction pour récupérer les dossiers d'un répertoire
function getDirectories($dir)
{
    $directories = [];
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item != '.' && $item != '..' && is_dir($dir . '/' . $item)) {
            $directories[] = $item;
        }
    }
    return $directories;
}

if (isset($_GET['year'])) {
    // Si un paramètre 'year' est passé, on récupère les mois
    $year = $_GET['year'];
    $path = $basePath . $year;
    echo json_encode(getDirectories($path));
} elseif (isset($_GET['month'])) {
    // Si un paramètre 'month' est passé, on récupère les fichiers
    $month = $_GET['month'];
    $path = $basePath . $month;
    $files = [];
    $items = scandir($path);
    foreach ($items as $item) {
        if ($item != '.' && $item != '..' && is_file($path . '/' . $item)) {
            $files[] = $item;
        }
    }
    echo json_encode($files);
} else {
    // Si aucun paramètre n'est passé, on récupère les années
    echo json_encode(getDirectories($basePath));
}
?>