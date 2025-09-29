<?php
ob_start();
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start the session
session_start();

// Session timeout duration (6 hours)
define('SESSION_TIMEOUT', 21600);

// Check for session timeout
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        // Destroy the session and redirect
        session_unset();
        session_destroy();
        header("Location: index.php?session_expired=1");
        exit();
    }
}

// Update the last activity timestamp
$_SESSION['last_activity'] = time();

// Check if the user is logged in
if (!isset($_SESSION['connect'])) {
    header("Location: index.php?redirecterror=1");
    exit();
}

if($_SESSION['type'] !== 'Super user'){
	header("Location: accueil.php?adminlog=1");
    exit();
}
// Safely retrieve the user's name
$nom = isset($_SESSION['nom']) ? htmlspecialchars($_SESSION['nom'], ENT_QUOTES, 'UTF-8') : 'Utilisateur';
echo '<title>Modification des utilisateurs</title>';

// Include necessary files
include "include/head.php";
include "include/menu.php";
?>

<body class="noPrint">
<?php
			include "include/erreur.php";
?>
    <div class="wrapped">
        <?php
include "include/updateuser.php";

?>
    </div>
</body>

</html>
<?php
// Envoi du contenu bufferisé et arrêt de la bufferisation
ob_end_flush();
?>