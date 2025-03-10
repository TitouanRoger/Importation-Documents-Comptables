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

if (!empty($_FILES['fichier']) && $_FILES['fichier']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['fichier']['tmp_name'])) {
  $file = $_FILES['fichier']['tmp_name'];   // Le fichier téléversé
  $date = date('Y-m-d', strtotime($_POST['date'])); // La date est bien formatée ?
  $annee = date('Y', strtotime($date));
  $mois = date('m', strtotime($date));
  $dest = '/documents/' . $annee . '/' . $mois . '/' . $_FILES['fichier']['name']; // Sa destination

  if (substr(strrchr($_FILES['fichier']['name'], '.'), 1) == 'pdf') {
    $conn_id = ftp_connect(CONFIG_SERVER);   // Création de la connexion au serveur FTP

    if (empty($conn_id)) {
      echo 'Échec de la connexion à ' . CONFIG_SERVER;
    } else {
      // Définition du délai de connexion
      ftp_set_option($conn_id, FTP_TIMEOUT_SEC, CONFIG_TIMEOUT);

      // Identification avec le nom d'utilisateur et le mot de passe
      $login_result = ftp_login($conn_id, CONFIG_USERNAME, CONFIG_PASSWORD);

      if (!$login_result) {
        echo 'Échec d\'identification à ' . CONFIG_SERVER;
      } else {
        // On crée le dossier de l'année s'il n'existe pas
        @ftp_chdir($conn_id, '/documents/' . $annee) || ftp_mkdir($conn_id, '/documents/' . $annee);

        // On crée le dossier du mois s'il n'existe pas
        @ftp_chdir($conn_id, '/documents/' . $annee . '/' . $mois) || ftp_mkdir($conn_id, '/documents/' . $annee . '/' . $mois);

        // Recherche de fichiers existants avec le même préfixe (date)
        $files = ftp_nlist($conn_id, '/documents/' . $annee . '/' . $mois . '/' . $date . '-*.pdf');

        // Vérifier que la liste des fichiers n'est pas vide
        if ($files !== false) {
          // Compte le nombre de fichiers existants avec cette date
          $nb = count($files) + 1;
        } else {
          $nb = 1;
        }

        // On renomme le fichier téléversé en ajoutant le numéro d'ordre
        $dest = '/documents/' . $annee . '/' . $mois . '/' . $date . '-' . str_pad($nb, 3, '0', STR_PAD_LEFT) . '.pdf';

        // Tentative de chargement sur le serveur FTP
        if (ftp_put($conn_id, $dest, $file, FTP_BINARY))
          echo 'Le fichier a été importé avec succès !';
        else
          echo "Problème lors de l'importation du fichier !";
      }
      // Fermeture de la connexion
      ftp_close($conn_id);
    }
  } else {
    echo "Seuls les fichiers .pdf sont autorisés !";
  }
}
?>