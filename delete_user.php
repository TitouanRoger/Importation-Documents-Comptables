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

if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    if (isset($_GET['id'])) {
        $user_id_to_delete = htmlspecialchars($_GET['id']);

        // Vérifier si l'utilisateur existe
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE id = :id");
        $stmt->bindParam(':id', $user_id_to_delete);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            // Supprimer l'utilisateur
            $delete_stmt = $db->prepare("DELETE FROM utilisateurs WHERE id = :id");
            $delete_stmt->bindParam(':id', $user_id_to_delete);

            if ($delete_stmt->execute()) {
                $_SESSION['message'] = 'Utilisateur supprimé avec succès.';
                $_SESSION['message_type'] = 'success'; // Type de message : succès
            } else {
                $_SESSION['message'] = 'Erreur lors de la suppression de l\'utilisateur.';
                $_SESSION['message_type'] = 'error'; // Type de message : erreur
            }
        } else {
            $_SESSION['message'] = 'Utilisateur non trouvé.';
            $_SESSION['message_type'] = 'error'; // Type de message : erreur
        }
    } else {
        $_SESSION['message'] = 'Aucun utilisateur à supprimer.';
        $_SESSION['message_type'] = 'error'; // Type de message : erreur
    }
} else {
    $_SESSION['message'] = 'Vous devez être connecté pour effectuer cette action.';
    $_SESSION['message_type'] = 'error'; // Type de message : erreur
}

header("Location: gestion.php");
exit;
