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

// Safely retrieve the user's name
$nom = isset($_SESSION['nom']) ? htmlspecialchars($_SESSION['nom'], ENT_QUOTES, 'UTF-8') : 'Utilisateur';
echo '<title>Profile&nbsp;'. $nom .'</title>';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <style>
        .wrapper{
            width: 700px;
            margin: 0 auto;
        }
    </style>
	<link rel="icon" type="image/png" href="img/OMCT.png">
	<link rel="stylesheet" type="text/css" href="css/default.css">
	
</head>
<body>

<?php

include "include/approvedom.php";

?>   
</body>

</html>
<?php
// Envoi du contenu bufferisé et arrêt de la bufferisation
ob_end_flush();
?>