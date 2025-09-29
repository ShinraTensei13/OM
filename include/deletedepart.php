<?php
if (!isset($_SESSION['nom']) || $_SESSION['nom'] !== 'Administrateur') {
    header("Location: accueil.php?adminlog=1");
    exit();
}

require_once "config.php";

// Initialisation des variables
$id = $depart = "";
$error = "";

// Traitement de la requête GET
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Validation des paramètres GET
    if (empty(trim($_GET["id"])) || !ctype_digit(trim($_GET["id"]))) {
        header("location: departement.php?errordeletedepart=1");
        exit();
    }

    $id = (int)trim($_GET["id"]);

    // Récupérer le département correspondant à l'ID
    $sql = "SELECT depart FROM departement WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $depart);
            mysqli_stmt_fetch($stmt);
        } else {
            header("location: departement.php?existdeletedepart=1");
            exit();
        }

        mysqli_stmt_close($stmt);
    } else {
        error_log("Erreur de préparation : " . mysqli_error($link));
        header("location: departement.php?errordeletedepart=1");
        exit();
    }

    // Génération du token CSRF
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Traitement de la requête POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérification du token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Token CSRF invalide");
    }

    // Validation de l'ID
    if (empty(trim($_POST["id"])) || !ctype_digit(trim($_POST["id"]))) {
        header("location: departement.php?errordeletedepart=1");
        exit();
    }

    // Préparation de la requête de suppression
    $sql = "DELETE FROM departement WHERE id = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        $param_id = (int)trim($_POST["id"]);
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        
        if (mysqli_stmt_execute($stmt)) {
            header("location: departement.php?successdeletedepart=1");
            exit();
        } else {
            error_log("Erreur SQL : " . mysqli_error($link));
            header("location: departement.php?errordeletedepart=1");
            exit();
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Erreur de préparation : " . mysqli_error($link));
        header("location: departement.php?errordeletedepart=1");
        exit();
    }
}
?>

<div class="wrapper container mt-5">

   

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
	<h2 class="mt-1" style="color:black;">
    Supprimer le département :
    <span style="color:#bd0202;"> <?php echo htmlspecialchars($depart); ?></span>
	</h2>
        <div class="mt-5 alert alert-danger">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <p>Êtes-vous sûr de vouloir supprimer ce département ?</p>
            
            <div>
                <input type="submit" value="Confirmer" class="btn btn-danger">
                <a href="departement.php" class="btn btn-secondary">Annuler</a>
            </div>
        </div>
    </form>
</div>