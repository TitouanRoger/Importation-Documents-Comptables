<?php
session_start();

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

$dbhost = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$dbuser = getenv('DB_USER');
$dbpass = getenv('DB_PASS');

try {
    $db = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

if (isset($_POST['new_password']) && isset($_POST['user_id'])) {
    $new_password = $_POST['new_password'];
    $user_id = $_POST['user_id'];

    // Hacher le mot de passe côté serveur
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Mettre à jour le mot de passe dans la base de données
    $stmt = $db->prepare("UPDATE utilisateurs SET password = :password, password_changed = FALSE WHERE id = :id");
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':id', $user_id);

    if ($stmt->execute()) {
        // Retourner une réponse JSON avec un statut de succès
        echo json_encode(['success' => true]);
    } else {
        // Retourner une réponse JSON avec un message d'erreur
        echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour du mot de passe']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Paramètres manquants']);
}
