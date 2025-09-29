<?php
session_start();
require 'include/config.php';

// Fonction pour récupérer l'adresse IP réelle
function getUserIP() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = 'UNKNOWN';
    }

    // Convertir IPv6 localhost (::1) en 127.0.0.1
    if ($ip === '::1') {
        $ip = '127.0.0.1';
    }

    return $ip;
}

// Vérifier si l'utilisateur est déjà connecté
if (!empty($_SESSION['connect'])) {
    header('Location: accueil.php');
    exit();
}



// Vérification du formulaire
if (!empty($_POST['email']) && !empty($_POST['password'])) {

    // Nettoyage des entrées
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $ip_address = getUserIP(); // Récupérer l'adresse IP

    // Vérifier si l'utilisateur est bloqué
    $stmt = $db->prepare('SELECT attempts, last_attempt FROM usersom WHERE email = ?');
    $stmt->execute([$email]);
    $user_data = $stmt->fetch();

    if ($user_data && $user_data['attempts'] >= 5 && time() - strtotime($user_data['last_attempt']) < 900) {
        // Bloqué pendant 15 minutes
        header('Location: index.php?blocked=1');
        exit();
    }

    // Requête pour récupérer l'utilisateur
    $req = $db->prepare('SELECT * FROM usersom WHERE email = ?');
    $req->execute([$email]);

    if ($user = $req->fetch()) {

        // Vérifier si le compte est inactif
        if ($user['actif'] !== 'Actif') {
            header('Location: index.php?inactif=1');
            exit();
        }

        // Vérification du mot de passe
        if (password_verify($password, $user['password'])) {

            // Réinitialiser les tentatives de connexion
            $reset_attempts = $db->prepare('UPDATE usersom SET attempts = 0 WHERE email = ?');
            $reset_attempts->execute([$email]);

            // Stocker les informations en session
            $_SESSION['connect'] = 1;
			session_regenerate_id(true);
			$_SESSION['last_activity'] = time();
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['poste'] = $user['poste'];
            $_SESSION['depart'] = $user['depart'];
            $_SESSION['referto'] = $user['referto'];
            $_SESSION['type'] = $user['type'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['caisse'] = $user['caisse'];
            $_SESSION['actif'] = $user['actif'];

            // Enregistrement dans le fichier success_logins.txt
            file_put_contents('logs/success_logins.txt', date('Y-m-d H:i:s') . " - Connexion réussie pour $email - IP: $ip_address\n", FILE_APPEND);

            // Gestion du "Se souvenir de moi"
            if (!empty($_POST['connect'])) {
                $cookie_value = bin2hex(random_bytes(32)); // Générer une valeur sécurisée
                setcookie('log', $cookie_value, [
                    'expires' => time() + 3600 * 24 * 30, // 30 jours
                    'path' => '/',
                    'domain' => '',
                    'secure' => true, // Seulement en HTTPS
                    'httponly' => true, // Empêche l'accès via JavaScript
                    'samesite' => 'Strict' // Protection contre les attaques CSRF
                ]);

                // Mise à jour du token en base de données
                $stmt = $db->prepare('UPDATE usersom SET remember_token = ? WHERE email = ?');
                $stmt->execute([$cookie_value, $email]);
            }

            header('Location: accueil.php?successcnx=1');
            exit();
        } else {
            // Mauvais mot de passe : incrémenter les tentatives
            $update_attempts = $db->prepare('UPDATE usersom SET attempts = attempts + 1, last_attempt = NOW() WHERE email = ?');
            $update_attempts->execute([$email]);

            // Enregistrement des tentatives échouées dans failed_logins.txt avec l'IP
            file_put_contents('logs/failed_logins.txt', date('Y-m-d H:i:s') . " - Tentative échouée pour $email - IP: $ip_address\n", FILE_APPEND);
        }
    }

    // Redirection en cas d'échec
    header('Location: index.php?errorcnx=1');
    exit();
}
?>


<?php
echo '<title>Ordres des missions</title>';
include 'include/head.php';
?>
<body class="noPrint">
    <header>
        <h1><b>Ordre de mission</b></h1>
    </header>

    <div class="main-content">
        <div class="login-container">
            
            <form action="index.php" method="POST">
			<h2>Bienvenue</h2>
				<?php
					include "include/erreur.php";
				?>
            <p>Veuillez vous connecter pour accéder à votre espace.</p>
				
                <div class="textbox">
                    <input type="email" id="email" name="email" placeholder="Email" required>
                </div>
                <div class="textbox">
                    <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                </div>
                <button class="btn" type="submit" style="color:white"><b>Se connecter</b></button><br><br>
				<p align="center"><a href="reset_request.php">Mot de passe oublié ?</a></p>
				<p align="center"><a href="guide.php">Guide d'utilisation de l'application</a></p>
            </form>
            
        </div>
    </div>
<?php
include 'include/footer.php';
?>
</body>
</html>
