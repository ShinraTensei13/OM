<?php
require "include/config.php"; // Inclure la configuration de la base de données

// Démarrer la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['connect'])) {
    header("Location: accueil.php?redirecterror=1");
    exit();
}

// Valider et nettoyer l'ID
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
if (empty($id)) {
    header("Location: accueil.php?linkresetdwn=1");
    exit();
}

// Vérifier le type d'utilisateur
if ($_SESSION['type'] !== 'Super user') {
    header("Location: accueil.php?adminlog=1");
    exit();
}

$nom = $_SESSION['nom'] ?? '';

try {
    // Préparer la requête en fonction du rôle
    if ($nom === 'Administrateur') {
        $query = "SELECT * FROM usersom WHERE id = ?";
    } else {
        $query = "SELECT * FROM usersom WHERE id = ? AND nom != 'Administrateur'";
    }

    // Exécuter la requête
    $stmt = $link->prepare($query);
    if (!$stmt) {
        throw new Exception("Échec de la préparation de la requête : " . $link->error);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        header("Location: accueil.php?adminlog=1");
        exit();
    }

} catch (Exception $e) {
    error_log("Erreur SQL : " . $e->getMessage());
    header("Location: accueil.php?linkresetdwn=1");
    exit();
}

// Gestion de la déconnexion
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_unset();
    session_destroy();
    header("Location: index.php?redirecterror=1");
    exit();
}
?>

<br>
    <div class="wrapper">
	<h2 class="mt-5" style="color:black;">
    Profile de 
    <span style="color:#bd0202;"><?php echo htmlspecialchars($user['nom']); ?></span>
</h2>
		<?php
		include 'include/erreur.php';
		?>
		<?php 
		$statut = $user['actif'];
		echo '<table align="center" class="noPrint" ><tr>';
		 if($statut == 'Actif'){
						echo '<td align="center" style="width:200px;"><a href="updateuser.php?id='. htmlspecialchars($user['id']) .'" style="color:white"><button class="btn btn-primary" ><b>Modifier les données</b></button></a></td>';
						echo '<td align="center" style="width:200px;"><a href="deleteuser.php?id='. htmlspecialchars($user['id']) .'" style="color:white"><button class="btn btn-primary" ><b>Désactiver le compte</b></button></a></td>';
		 } else {
					echo '<table align="center" class="noPrint" style="width:800px;" ><tr>';
						echo '<td align="center" style="width:200px;"><a href="deleteuser.php?id='. htmlspecialchars($user['id']) .'" style="color:white"><button class="btn btn-primary" ><b>Réactiver</b></button></a></td>';
		 }
		 if($_SESSION['type'] === 'Super user' AND $_SESSION['nom'] === 'Administrateur'){
						echo '<td align="center" style="width:200px;"><a href="updateuserpwd.php?id='. htmlspecialchars($user['id']) .'" style="color:white"><button class="btn btn-primary" ><b>Modifier le mot de passe</b></button></a></td>';
		 }
		 echo '<td align="center" style="width:200px;"><a href="users.php"><button class="btn btn-primary" style="color:white"><b>Retour</b></button></a></td>';
					echo '</tr></table>';
		?>
		
        <table class="login-container">
            <!-- Affiche ici les informations de l'utilisateur -->
            <tr>
                <td><b>Nom</b></td>
                <td><?php echo htmlspecialchars($user['nom']); ?></td>
            </tr>
            <tr>
                <td><b>Poste</b></td>
                <td><?php echo htmlspecialchars($user['poste']); ?></td>
            </tr>
            <tr>
                <td><b>Email</b></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
            </tr>
			<tr>
                <td><b>Département</b></td>
                <td><?php echo htmlspecialchars($user['depart']); ?></td>
            </tr>
            <tr>
                <td><b>Consultation des ordres de missions</b></td>
                <td><?php echo htmlspecialchars($user['caisse']); ?></td>
            </tr>
			<tr>
                <td><b>Responsable du coordinateur</b></td>
                <td><?php echo htmlspecialchars($user['referto']); ?></td>
            </tr>
			<tr>
                <td><b>Droits d'administration</b></td>
                <td><?php echo htmlspecialchars($user['type']); ?></td>
            </tr>
			<tr>
                <td><b>Signature</b></td>
                <td><?php echo htmlspecialchars($user['sign']); ?></td>
            </tr>
        </table>
    </div>
	