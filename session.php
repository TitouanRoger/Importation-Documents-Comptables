<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importation Documents Comptables</title>
    <link rel="icon" href="images/logo.jpg">
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

    ob_start();
    session_start();
    ?>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
        }

        .sidebar {
            background-color: #FA193B;
            color: white;
            width: 200px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            transition: transform 0.5s;
        }

        .sidebar a {
            text-decoration: none;
            color: inherit;
        }

        .logo {
            width: 100%;
            margin-bottom: 20px;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            box-sizing: border-box;
        }

        .import-container {
            text-align: center;
        }

        .content h1 {
            font-weight: bold;
            font-size: 24px;
            margin-bottom: 10px;
            text-align: center;
        }

        .content h2 {
            font-weight: bold;
            font-size: 20px;
            margin-bottom: 10px;
            text-align: center;
        }

        .content h3 {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
            text-align: center;
        }

        .content p {
            font-size: 16px;
            line-height: 1.5;
            text-align: center;
        }

        .content .button {
            display: block;
            margin: 0 auto;
        }

        .toggle-sidebar {
            position: absolute;
            right: 3%;
        }

        .toggle-sidebar:hover {
            cursor: pointer;
        }

        .button-import {
            display: block;
            margin: 10px auto;
            padding: 10px;
            font-size: 16px;
            background-color: #FA193B;
            border: none;
            color: white;
            border-radius: 10px;
        }

        .button-import:hover {
            background-color: #e61b4b;
        }

        @media (max-width: 435px) {
            .sidebar {
                width: 100%;
                height: 100vh;
                position: fixed;
                transform: translateX(-90%);
                padding: 10px;
            }

            .content {
                margin-left: 30px;
                padding-top: 20px;
            }

            .sidebar .logo {
                width: 150px;
            }

            .sidebar .onglets {
                padding-left: 30%;
            }

            .gestion {
                left: 30%;
            }

            .deconnexion {
                left: 30%;
            }

            .content h1 {
                font-size: 32px;
            }

            .content h2 {
                font-size: 24px;
            }

            .content h3 {
                font-size: 20px;
            }

            .content p {
                font-size: 18px;
            }
        }

        .import-container input[type="file"] {
            display: none;
        }

        .import-container label {
            background-color: #FA193B;
            border: none;
            color: white;
            padding: 10px;
            border-radius: 10px;
            cursor: pointer;
        }

        .import-container label:hover {
            background-color: #e61b4b;
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
            margin: auto;
        }

        .gestion {
            display: block;
            margin: 80px auto;
            padding: 10px;
            background-color: #FA193B;
            color: white;
            text-align: center;
            border: 2px solid white;
            border-radius: 20px;
            text-decoration: none;
            width: 200px;
            box-sizing: border-box;
        }

        .gestion:hover {
            background-color: #e61b4b;
        }

        .deconnexion {
            display: block;
            margin: 10px auto;
            padding: 10px;
            background-color: #FA193B;
            color: white;
            text-align: center;
            border: 2px solid white;
            border-radius: 20px;
            text-decoration: none;
            width: 200px;
            box-sizing: border-box;
        }

        .deconnexion:hover {
            background-color: #e61b4b;
        }

        input[type="date"] {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
            color: white;
            background-color: #FA193B;
        }

        .download-grid {
            gap: 20px;
            padding: 20px;
            background-color: #F0F0F0;
            border-radius: 10px;
        }

        .grid {
            gap: 20px;
            padding: 20px;
            background-color: #F0F0F0;
            border-radius: 10px;
        }

        .button-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 20px;
        }

        .document-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 10px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            grid-column: span 1;
        }

        .button {
            display: block;
            margin: 20px auto;
            padding: 10px;
            font-size: 18px;
            background-color: #FA193B;
            border: none;
            color: white;
            border-radius: 10px;
            text-align: center;
            width: 200px;
        }

        .button:hover {
            background-color: #e61b4b;
        }

        @media (max-width: 435px) {
            .download-grid {
                gap: 10px;
                padding: 10px;
            }

            .button-grid {
                gap: 10px;
                padding: 10px;
            }

            .button {
                padding: 10px;
                font-size: 14px;
                width: 75px;
            }
        }
    </style>
    <script>
        let inactivityTimer;

        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                localStorage.clear();
            }, 600000); // 10 minutes
        }

        // Fonction pour changer le contenu et stocker l'état dans localStorage
        function changeContent(content, section) {
            const toggleButton = document.querySelector('.toggle-sidebar');
            const sidebar = document.querySelector('.sidebar');
            document.getElementById('content').innerHTML = content;

            // Enregistrer la section actuelle dans localStorage
            localStorage.setItem('lastSection', section);

            if (window.innerWidth <= 435) {
                sidebar.style.transform = 'translateX(-90%)';
            }
        }

        // Fonction pour afficher les années
        function showYears() {
            fetch('get_folders.php')
                .then(response => response.json())
                .then(folders => {
                    // Trouver la première et la dernière année disponibles
                    const minYear = Math.min(...folders);  // L'année la plus ancienne
                    const maxYear = new Date().getFullYear();  // L'année actuelle

                    // Créer les périodes disponibles sous la forme '2024-2025'
                    const periods = [];
                    for (let year = minYear; year < maxYear; year++) {
                        periods.push(`${year}-${year + 1}`);
                    }

                    const html = `
                <div class="section">
                    <h1>Documents</h1>
                    <div class="download-grid">
                        <h2>Téléchargements</h2>
                        <a href="telecharger_tout.php" class="button">Télécharger Tout</a>
                        <br><br>
                        <div>
                            <h3>Téléchargement Période</h3>
                            <p><span id="selected-period">Période sélectionnée :</span></p>
                            <select id="periodSelect" class="button">
                                ${periods.map(period => {
                        return `<option value="${period}">${period}</option>`;
                    }).join('')}
                            </select>
                        </div>
                        <br>
                        <a href="#" class="button" onclick="downloadPeriod()">Télécharger Période</a>
                    </div>
                    <br><br>
                    <div class="grid">
                        <h2>Consulter les documents</h2>
                        <div class="button-grid">
                            ${folders.map(folder => {
                        return `<button class="button" onclick="showMonths('${folder}'); localStorage.setItem('lastYear', '${folder}');">${folder}</button>`;
                    }).join('')}
                        </div>
                    </div>
                </div>
            `;
                    changeContent(html, 'annees');
                })
                .catch(error => console.error(error));
        }

        // Fonction pour afficher les mois d'une année donnée
        function showMonths(year) {
            fetch(`get_folders.php?year=${year}`)
                .then(response => response.json())
                .then(folders => {
                    const monthNames = {
                        '01': '01 - Janvier',
                        '02': '02 - Février',
                        '03': '03 - Mars',
                        '04': '04 - Avril',
                        '05': '05 - Mai',
                        '06': '06 - Juin',
                        '07': '07 - Juillet',
                        '08': '08 - Août',
                        '09': '09 - Septembre',
                        '10': '10 - Octobre',
                        '11': '11 - Novembre',
                        '12': '12 - Décembre'
                    };
                    const html = `
                        <div class="section">
                            <h1>Documents</h1>
                            <h2>${year}</h2>
                            <button class="button" onclick="showYears()">Retour</button>
                            <br>
                            <div class="download-grid">
                                <h2>Téléchargements</h2>
                                <br>
                                <a href="telecharger_tout.php?year=${year}" class="button">Télécharger Tout</a>
                            </div>
                            <br><br>
                            <div class="grid">
                                <h2>Consulter les documents</h2>
                                <div class="button-grid">
                                    ${folders.map(folder => {
                        const monthName = monthNames[folder] || folder;
                        return `<button class="button" onclick="showDays('${year} > ${monthName}', '${year}/${folder}'); localStorage.setItem('lastYearMonth', '${year} > ${monthName}'); localStorage.setItem('lastMonth', '${year}/${folder}');">${monthName}</button>`;
                    }).join('')}
                                </div>
                            </div>
                        </div>
                    `;
                    changeContent(html, 'mois');
                })
                .catch(error => console.error(error));
        }

        // Fonction pour afficher les fichiers d'un mois donné
        function showDays(year_month, month) {
            fetch(`get_folders.php?month=${month}`)
                .then(response => response.json())
                .then(files => {
                    const html = `
                        <div class="section">
                            <h1>Documents</h1>
                            <h2>${year_month}</h2>
                            <button class="button" onclick="showMonths('${month.split('/')[0]}')">Retour</button>
                            <br>
                            <div class="download-grid">
                                <h2>Téléchargements</h2>
                                <br>
                                <a href="telecharger_tout.php?year=${month.split('/')[0]}&month=${month.split('/')[1]}" class="button">Télécharger Tout</a>
                            </div>
                            <br><br>
                            <div class="grid">
                                <h2>Consulter les documents</h2>
                                <div class="button-grid">
                                    ${files.map(file => {
                        return `
                                            <div class="document-item">
                                                <a href="documents/${month}/${file}" target="_blank">${file}</a>
                                                <button class="button" onclick="editFile('${month}/${file}');">Modifier</button>
                                            </div>
                                        `;
                    }).join('')}
                                </div>
                            </div>
                        </div>
                    `;
                    changeContent(html, 'jours');
                })
                .catch(error => console.error(error));
        }

        // Fonction pour afficher l'importation de documents
        function showImport() {
            changeContent(`
                <div class="import-container">
                    <h1>Importer Document</h1>
                    <br>
                    <form action="javascript:;" method="post" enctype="multipart/form-data" onsubmit="uploadFile(event)">
                        <input type="file" id="file" name="fichier" accept="application/pdf">
                        <label for="file">Choisir un fichier</label>
                        <p><span id="selected-file">Fichier sélectionné : <span id="selected-file-name">Aucun</span></span></p>
                        <input type="date" id="select-date" name="date">
                        <input class="button-import" type="submit" value="Importer" />
                    </form>
                </div>
            `, 'importer');
            const fileInput = document.getElementById('file');

            fileInput.addEventListener('change', function () {
                const selectedFileNameElement = document.getElementById('selected-file-name');
                const fileName = fileInput.files.length > 0 ? fileInput.files[0].name : 'Aucun';
                selectedFileNameElement.innerText = fileName;
            });
        }

        function downloadPeriod() {
            const period = document.getElementById('periodSelect').value;

            if (period) {
                const [startYear, endYear] = period.split('-');
                const periodUrl = `telecharger_periode.php?startYear=${startYear}&endYear=${endYear}`;
                window.location.href = periodUrl;  // Redirige vers telecharger_periode.php avec les paramètres de période
            } else {
                alert("Veuillez sélectionner une période valide.");
            }
        }

        // Fonction pour gérer l'importation du fichier
        function uploadFile(event) {
            event.preventDefault();
            const fileInput = document.getElementById('file');
            const dateInput = document.getElementById('select-date');

            if (fileInput.files.length === 0) {
                alert('Veuillez sélectionner un fichier');
                return;
            }

            if (dateInput.value === '') {
                alert('Veuillez sélectionner une date');
                return;
            }
            const formData = new FormData(event.target);
            const button = document.querySelector('.button-import');
            button.disabled = true;
            button.value = 'Importation...';
            fetch('upload.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(message => {
                    alert(message);
                    button.disabled = false;
                    button.value = 'Importer';
                    window.location.reload();
                })
                .catch(error => console.error(error));
        }

        // Fonction pour gérer la modification du fichier
        function editFile(fileName) {
            const fileParts = fileName.split('/');
            const year = fileParts[0];
            const month = fileParts[1];
            const file = fileParts.slice(2).join('/');  // Le nom du fichier à remplacer

            const formHtml = `
                <div class="import-container">
                    <h1>Modifier le fichier : ${file}</h1>
                    <button class="button" type="button" onclick="showDays('${year}/${month}')">Retour</button>
                    <br>
                    <form action="javascript:;" method="post" enctype="multipart/form-data" onsubmit="uploadEditedFile(event, '${year}', '${month}', '${file}')">
                        <input type="file" id="file" name="fichier" accept="application/pdf">
                        <label for="file">Choisir un nouveau fichier</label>
                        <p><span id="selected-file">Fichier sélectionné : <span id="selected-file-name">Aucun</span></span></p>
                        <input class="button-import" type="submit" value="Remplacer" />
                    </form>
                </div>
            `;

            changeContent(formHtml, 'modifier');

            const fileInput = document.getElementById('file');
            fileInput.addEventListener('change', function () {
                const selectedFileNameElement = document.getElementById('selected-file-name');
                const fileName = fileInput.files.length > 0 ? fileInput.files[0].name : 'Aucun';
                selectedFileNameElement.innerText = fileName;
            });
        }

        // Fonction pour gérer l'upload du fichier modifié
        function uploadEditedFile(event, year, month, file) {
            event.preventDefault();

            const fileInput = document.getElementById('file');

            // Vérifier si un fichier est sélectionné
            if (fileInput.files.length === 0) {
                alert('Veuillez sélectionner un fichier');
                return;
            }

            // Créer un FormData pour l'upload du fichier
            const formData = new FormData(event.target);
            formData.append('year', year);
            formData.append('month', month);
            formData.append('oldFile', file);  // Nom de l'ancien fichier à remplacer

            // Envoyer la requête via fetch
            fetch('edit.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(message => {
                    alert(message);
                    window.location.reload();  // Recharger la page après l'upload
                })
                .catch(error => console.error(error));
        }

        // Gérer le redimensionnement de la fenêtre
        document.addEventListener('DOMContentLoaded', function () {
            const toggleButton = document.querySelector('.toggle-sidebar');
            const sidebar = document.querySelector('.sidebar');

            if (window.innerWidth <= 435) {
                toggleButton.style.display = 'block';
                sidebar.style.transform = 'translateX(-90%)';
            } else {
                toggleButton.style.display = 'none';
            }

            toggleButton.addEventListener('click', function () {
                if (sidebar.style.transform === 'translateX(-20%)') {
                    sidebar.style.transform = 'translateX(-90%)';
                } else {
                    sidebar.style.transform = 'translateX(-20%)';
                }
            });

            const lastSection = localStorage.getItem('lastSection');
            const lastYear = localStorage.getItem('lastYear');
            const lastMonth = localStorage.getItem('lastMonth');
            const lastYearMonth = localStorage.getItem('lastYearMonth');

            // Vérifier si une section est stockée dans localStorage et la charger
            if (lastSection === 'annees') {
                showYears();
            } else if (lastSection === 'mois') {
                showMonths(lastYear);
            } else if (lastSection === 'jours' || lastSection === 'modifier') {
                showDays(lastYearMonth, lastMonth);
            } else if (lastSection === 'importer') {
                showImport();
            } else {
                changeContent('<h1>Bienvenue sur la page d\'accueil</h1>', 'accueil');
            }
        });

        // Gérer le redimensionnement de la fenêtre
        window.addEventListener('resize', function () {
            const toggleButton = document.querySelector('.toggle-sidebar');
            const sidebar = document.querySelector('.sidebar');

            if (window.innerWidth <= 435) {
                toggleButton.style.display = 'block';
                sidebar.style.transform = 'translateX(-90%)';
            } else {
                toggleButton.style.display = 'none';
            }
        });
    </script>
</head>

<body>
    <?php
    if (isset($_SESSION['id'])) {
        $id = $_SESSION['id'];
        $sql = $db->prepare("SELECT * FROM utilisateurs WHERE id = :id");
        $sql->bindParam(':id', $id);
        $sql->execute();

        if ($sql->rowCount() == 0) {
            session_unset();
            session_destroy();
            echo '<div class="message" style="background-color: #f44336;">Votre compte a été supprimé. Vous allez être déconnecté.</div>';
            setcookie(session_name(), '', 0, '/');
            header("Refresh: 2; URL=index.php");
            exit;
        }

        $inactive_time = 900; // 15 minutes
        if (isset($_SESSION['last_activity'])) {
            $session_lifetime = time() - $_SESSION['last_activity'];

            if ($session_lifetime > $inactive_time) {
                session_unset();
                session_destroy();
                setcookie(session_name(), '', 0, '/');
                header("Location: index.php");
                exit;
            }
        }
    }

    $_SESSION['last_activity'] = time();

    if (isset($_GET['logout'])) {
        session_destroy();
        setcookie(session_name(), '', 0, '/');
        header("Location: index.php");
        exit;
    }
    ?>
    <?php if (isset($_SESSION['id'])): ?>
        <div class="sidebar">
            <div class="onglets">
                <div class="toggle-sidebar" style="display: none;">&#9776;</div>
                <img src="images/logo.jpg" alt="Logo" class="logo">
                <br><br><br>
                <a href="#" onclick="showYears()">Documents</a>
                <br><br>
                <a href="#" onclick="showImport()">Importer Document</a>
            </div>
            <?php
            $sql = $db->prepare("SELECT * FROM utilisateurs WHERE id = :id");
            $sql->bindParam(':id', $id);
            $sql->execute();
            $user = $sql->fetch();
            if ($user['role'] === 'administrateur'): ?>
                <a href="gestion.php" class="gestion" style="position: absolute; bottom: 100px;">Gestion</a>
            <?php endif; ?>
            <a href="session.php?logout=true" class="deconnexion" style="position: absolute; bottom: 100px;">Deconnexion</a>
        </div>
        <div class="content" id="content">
            <!-- Default content can go here -->
        </div>
    <?php else:
        echo '<div class="message" style="background-color: #f44336;">Vous n\'êtes pas connecté.</div>';
        setcookie(session_name(), '', 0, '/');
        header("Refresh: 2; URL=index.php");
    endif;
    ?>
</body>

</html>