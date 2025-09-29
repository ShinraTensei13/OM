<?php
// Inclure le fichier de configuration
require_once "config.php";

// Vérifier si l'ID est passé en paramètre POST (après soumission du formulaire)
if (isset($_POST["id"]) && !empty($_POST["id"])) {
    // Préparer la requête SQL de suppression
    $sql = "DELETE FROM ordred WHERE id = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        // Liaison des paramètres
        mysqli_stmt_bind_param($stmt, "i", $param_id);

        // Récupérer et valider l'ID
        $param_id = trim($_POST["id"]);

        // Exécuter la requête
        if (mysqli_stmt_execute($stmt)) {
            // Redirection en cas de succès
            header("location: accueil.php?successdeleteom=1");
            exit();
        } else {
            // Redirection en cas d'erreur
            header("location: accueil.php?errordeleteom=1");
            exit();
        }
    }

    // Fermer la déclaration
    mysqli_stmt_close($stmt);
} else {
    // Vérifier si l'ID est passé en paramètre GET (avant soumission du formulaire)
    if (empty(trim($_GET["id"]))) {
        // Redirection si l'ID est manquant
        header("location: accueil.php?errordeleteom=1");
        exit();
    }
}

// Fermer la connexion
mysqli_close($link);
?>

					
    <div class="wrapper container mt-5">
        <h2 class="mb-3">Supprimer l'ordre de mission N°: <?php echo htmlspecialchars(trim($_GET["id"])); ?></h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="alert alert-danger">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars(trim($_GET["id"])); ?>"/>
                <p>Êtes-vous sûr de vouloir supprimer cet ordre de mission ?</p>
                <div>
                    <input type="submit" value="Valider" class="btn btn-danger">
                    <a href="accueil.php" class="btn btn-secondary">Annuler</a>
                </div>
            </div>
        </form>
    </div>