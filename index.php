<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="icon" href="images/logo.jpg">
</head>
<style>
    html,
    body {
        margin: 0;
        padding: 0;
        height: 100%;
        overflow: hidden;
        font-family: Arial, sans-serif;
        box-sizing: border-box;
    }

    body {
        background: white;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }

    .logo {
        margin-bottom: 20px;
        width: 150px;
        height: auto;
    }

    .container {
        background: #FA193B;
        text-align: center;
        width: 300px;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        color: white;
    }

    label {
        display: block;
        text-align: left;
        margin: 10px 0 5px;
        color: white;
    }

    input {
        width: 100%;
        padding: 8px;
        margin-bottom: 15px;
        border: 1px solid white;
        border-radius: 5px;
        box-sizing: border-box;
    }

    .button {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: none;
        border-radius: 20px;
        background-color: #FA193B;
        color: white;
        font-size: 1em;
        cursor: pointer;
    }

    .button:hover {
        background-color: rgb(230, 23, 54);
        color: white;
        border: 1px solid white;
    }

    .connexion {
        background: transparent;
        border: 1px solid white;
    }

    .forgot {
        text-decoration: none;
        color: white;
        font-size: 0.8em;
    }

    .message {
        padding: 15px;
        color: white;
        font-size: 18px;
        font-weight: bold;
        border-radius: 10px;
        text-align: center;
        width: 300px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        margin-top: 20px;
    }

    .message.success {
        background-color: #4CAF50;
    }

    .message.error {
        background-color: #FA193B;
    }

    @media (max-width: 600px) {
        .container {
            width: 90%;
        }

        .message {
            width: 90%;
        }

        .logo {
            width: 120px;
        }
    }
</style>

<body>
    <img src="images/logo.jpg" alt="Logo" class="logo">
    <div class="container">
        <form method="post" class="form">
            <label>Identifiant</label>
            <input type="text" name="id" placeholder="Identifiant" required>
            <label>Mot de passe</label>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <a href="#" class="forgot" onclick="alert('Veuillez contacter l\'administrateur !'); return false;">Mot de
                passe oublié ?</a>
            <button type="submit" class="button connexion" name="connexion">Connexion</button>
        </form>
    </div>
    <?php
    /**
     * Vérifie si l'identifiant et le mot de passe sont valides
     * Si oui, enregistre l'identifiant en session et redirige vers la page de session
     * Si non, efface la session et affiche un message d'erreur
     */
    function connexion()
    {
        /**
         * Connexion à la base de données
         */
        // Fonction pour charger les variables d'environnement depuis un fichier .env
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
        if (isset($_POST['connexion'])) {
            $id = htmlspecialchars($_POST['id']);
            $password = htmlspecialchars($_POST['password']);
            $sql = $db->prepare("SELECT * FROM utilisateurs WHERE id = :id");
            $sql->bindParam(':id', $id);
            $sql->execute();

            if ($sql->rowCount() == 1) {
                $sql = $db->prepare("SELECT password FROM utilisateurs WHERE id = :id");
                $sql->bindParam(':id', $id);
                $sql->execute();
                $row = $sql->fetch();
                $hashed_password = $row['password'];
                session_start();

                if (password_verify($password, $hashed_password)) {
                    $_SESSION['id'] = $id;
                    $sql = $db->prepare("SELECT password_changed FROM utilisateurs WHERE id = :id");
                    $sql->bindParam(':id', $id);
                    $sql->execute();
                    $user = $sql->fetch();
                    if ($user['password_changed'] === 0) {
                        // Rediriger l'utilisateur vers une page de changement de mot de passe
                        echo '<div class="message success">Connexion réussie !</div>';
                        header("Refresh: 2; URL=change_password.php");
                        exit;
                    }

                    echo '<div class="message success">Connexion réussie !</div>';
                    header("Refresh: 2; URL=session.php");
                    exit;
                } else {
                    session_unset();
                    session_destroy();
                    echo '<div class="message error">Identifiant ou mot de passe incorrect.</div>';
                }
            } else {
                echo '<div class="message error">Identifiant ou mot de passe incorrect.</div>';
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        connexion();
    }
    ?>
</body>

</html>