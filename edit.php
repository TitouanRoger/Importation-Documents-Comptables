<?php

function loadEnv($file)
{
  if (!file_exists($file)) {
    throw new Exception("Le fichier .env est introuvable");
  }

  $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    // Ignorer les commentaires (lignes commençant par #)
    if (strpos($line, '#') === 0) {
      continue;
    }

    // Extraire la clé et la valeur
    list($key, $value) = explode('=', $line, 2);

    // Enregistrer la variable d'environnement
    putenv(trim($key) . '=' . trim($value));
  }
}

// Charger les variables d'environnement depuis le fichier .env
loadEnv(__DIR__ . '/.env');

$ftphost = getenv('FTP_HOST');
$ftpuser = getenv('FTP_USER');
$ftppass = getenv('FTP_PASS');

define('CONFIG_SERVER', $ftphost);  // Adresse du serveur FTP
define('CONFIG_USERNAME', $ftpuser);  // Nom d'utilisateur
define('CONFIG_PASSWORD', $ftppass);  // Mot de passe
define('CONFIG_TIMEOUT', 2);      // Délai de connexion, en secondes

// Fonction pour se connecter au serveur FTP
function ftpConnect()
{
  $ftp = ftp_connect(CONFIG_SERVER, 21, CONFIG_TIMEOUT);  // Connexion au serveur FTP
  if (!$ftp) {
    die('Impossible de se connecter au serveur FTP.');
  }

  $login = ftp_login($ftp, CONFIG_USERNAME, CONFIG_PASSWORD);  // Connexion avec les identifiants
  if (!$login) {
    die('Échec de la connexion avec les identifiants FTP.');
  }

  return $ftp;
}

// Vérifier si le fichier a été envoyé et traiter la requête
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Vérifier si tous les paramètres sont présents
  if (isset($_FILES['fichier']) && isset($_POST['year']) && isset($_POST['month']) && isset($_POST['oldFile'])) {
    $year = $_POST['year'];
    $month = $_POST['month'];
    $oldFile = $_POST['oldFile'];  // Le nom de l'ancien fichier à remplacer

    // Le répertoire sur le serveur FTP où le fichier doit être remplacé
    $remoteDir = '/documents/' . $year . '/' . $month . '/';
    $filePath = $remoteDir . $oldFile;  // Chemin du fichier à remplacer sur le serveur

    // Récupérer le fichier uploadé
    $newFile = $_FILES['fichier'];
    $newFileTmpPath = $newFile['tmp_name'];  // Le chemin temporaire du fichier uploadé

    // Vérifier si le fichier est un PDF
    $fileType = strtolower(pathinfo($newFile['name'], PATHINFO_EXTENSION));
    if ($fileType != 'pdf') {
      echo "Erreur : seul les fichiers PDF sont autorisés.";
      exit;
    }

    // Connexion au serveur FTP
    $ftp = ftpConnect();

    // Remplacer le fichier sur le serveur
    // Téléverser le nouveau fichier
    if (ftp_put($ftp, $filePath, $newFileTmpPath, FTP_BINARY)) {
      echo "Le fichier a été remplacé avec succès.\n";
    } else {
      echo "Erreur lors du téléversement du fichier.\n";
    }
  }

  // Fermer la connexion FTP
  ftp_close($ftp);
}
