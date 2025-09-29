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

// Mettre à jour le timestamp d'activité
$_SESSION['last_activity'] = time();
?>
<?php
$nom = $_SESSION['nom'];
echo '<title>Accueil&nbsp;'. $nom .'</title>';
if(!isset($_SESSION['connect'])){ 
	header("location: index.php?redirecterror=1");
    exit();  }
else {
	include "include/head.php";
	include "include/menu.php";
?>

<body>
                   
<?php

include "include/newom.php";

?>

</body>
<br>
		<?php } ?>
</html>