<?php
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

if($_SESSION['nom'] !== 'Administrateur'){ 
	header("Location: accueil.php?adminlog=1");
    exit();}
	
// Safely retrieve the user's name
$nom = isset($_SESSION['nom']) ? htmlspecialchars($_SESSION['nom'], ENT_QUOTES, 'UTF-8') : 'Utilisateur';
echo '<title>Administration du site</title>';

// Include necessary files
include "include/head.php";
include "include/menu.php";
?>

<body class="noPrint">
    <?php
    // Display errors (if any)
    include "include/erreur.php";
    ?>
<form>
	<div class="main-content">
        <div class="login-container">
		<h2>Veuillez choisir les données à modifier</h2>
			<div class="wrapper">
				<table>
					<tr>
						<td><center><button type="button" style="width:200px;" class="btn btn-primary" onclick="window.location.href='transport.php';"><b>Moyen de dépalcement</b></button></center></td>
						<td><center><button type="button" style="width:200px;" class="btn btn-primary" onclick="window.location.href='departement.php';"><b>Département</b></button></center></td>
						<td><center><button type="button" style="width:200px;" class="btn btn-primary" onclick="window.location.href='fraistr.php';"><b>Frais de déplacement</b></button></center></td>
					</td>
				</table>
			</div>
		</div>
	</div>
</form>
</body>

</html>
