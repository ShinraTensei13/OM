<?php 
session_start(); /* Created by Seifeddine Zouari - sz@omct.org */

$session_timeout = 21600; // 6 heures (21600 secondes)

if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $session_timeout) {
        session_unset();
        session_destroy();
        // Redirection immédiate après expiration de la session
        header("Location: index.php?session_expired=1");
        exit();  // Terminer l'exécution du script
    }
}

if($_SESSION['caisse'] !== 'Oui'){
	header("Location: accueil.php?adminlog=1");
    exit();
}

// Mettre à jour le timestamp d'activité
$_SESSION['last_activity'] = time();
?>
<?php
$nom = $_SESSION['nom'];
if(!isset($_SESSION['connect'])){ 
	header("location: index.php?redirecterror=1");
    exit();  }
else {
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
		<?php } ?>
</html>