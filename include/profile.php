<?php

require "include/config.php"; // Inclut le fichier de configuration de la base de données

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['connect'])) {
    // Si l'utilisateur n'est pas connecté, redirigez-le vers la page de connexion
    header("Location: accueil.php?redirecterror=1");
    exit();
}

// Récupère le nom de l'utilisateur depuis la session
if (!isset($_SESSION['nom'])) {
    // Si le nom n'est pas défini dans la session, redirigez l'utilisateur
    header("Location: accueil.php?redirecterror=1");
    exit();
}
$nom = $_SESSION['nom'];

// Exemple de récupération de données depuis la base de données
// Utilisation d'une requête préparée pour éviter les injections SQL
$query = "SELECT * FROM usersom WHERE nom = ?";
$stmt = $link->prepare($query); // Prépare la requête
if ($stmt) {
    $stmt->bind_param("s", $nom); // Lie le paramètre
    $stmt->execute(); // Exécute la requête
    $result = $stmt->get_result(); // Récupère le résultat
    $user = $result->fetch_assoc(); // Récupère les données de l'utilisateur

    // Ferme le statement
    $stmt->close();
} else {
    // Gestion des erreurs de préparation de la requête
    die("Erreur lors de la préparation de la requête : " . $link->error);
}

// Gestion de la déconnexion
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    // Supprime toutes les variables de session
    session_unset();
    // Détruit la session
    session_destroy();
    // Redirige vers la page de connexion
    header("Location: accueil.php?redirecterror=1");
    exit();
}
?>

    <div class="wrapper">
        <h2 color="red">Mon Profil</h2>
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
                <td><b>Consultation des ordres de missions</b></td>
                <td><?php echo htmlspecialchars($user['caisse']); ?></td>
            </tr>
			<tr>
                <td><b>Responsable</b></td>
                <td><?php echo htmlspecialchars($user['referto']); ?></td>
            </tr>
			<tr>
                <td><b>Droits d'administration du site</b></td>
                <td><?php echo htmlspecialchars($user['type']); ?></td>
            </tr>
        </table>
    </div>