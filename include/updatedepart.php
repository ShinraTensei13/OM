<?php

if (!isset($_SESSION['connect']) || $_SESSION['nom'] !== 'Administrateur') {
    header("Location: accueil.php?adminlog=1");
    exit();
}

require "include/config.php";

// Initialisation des variables
$depart = "";
$depart_err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !empty($_POST['id'])) {
    // Récupérer l'ID de l'enregistrement à modifier
    $id = (int)$_POST['id'];

    // Récupération et validation des champs
    $depart = trim($_POST['depart']);
    if (empty($depart)) {
        $depart_err = "Le nom du département est requis";
    } else {
        // Vérifier si le département existe déjà
        $check_query = "SELECT 1 FROM departement WHERE depart = ? AND id <> ?";
        if ($check_stmt = $link->prepare($check_query)) {
            $check_stmt->bind_param("si", $depart, $id);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                header('Location: updatedepart.php?departexist=1&id=' . $id);
                exit();
            }
            $check_stmt->close();
        }
    }

    // Si aucune erreur, on met à jour l'enregistrement dans la base de données
    if (empty($depart_err)) {
        $sql = "UPDATE departement SET depart = ? WHERE id = ?";
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("si", $depart, $id);
            if ($stmt->execute()) { 
                header("Location: departement.php?upddepartsuccess=1");
                exit();
            } else {
                header("Location: departement.php?upddeparterror=1");
                exit();
            }
            $stmt->close();
        }
    }
} elseif (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    // Cas d'affichage du formulaire : récupération de l'enregistrement à modifier
    $id = (int)trim($_GET['id']);
    $sql = "SELECT * FROM departement WHERE id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $depart = $row['depart'];
            } else {
                header("Location: departement.php?InvalidID=1");
                exit();
            }
        } else {
            echo "Une erreur est survenue lors de la récupération des données.";
        }
        $stmt->close();
    }
} else {
    // Aucun ID valide n'a été fourni
    header("Location: departement.php?InvalidID=1");
    exit();
}
?>

<div class="wrapper">
    <h2 class="my-4">Modifier les départements</h2>
	
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

        <div class="form-group">
            <label for="depart"><b>Département</b></label>
            <input type="text" name="depart" id="depart" class="form-control" value="<?php echo htmlspecialchars($depart); ?>">
            <span class="error"><?php echo $depart_err; ?></span>
        </div>
		<table align="center">
        <tr class="form-group mt-3">
            <td><button type="submit" class="btn btn-primary"><b>Mettre à jour</b></button></td>
            <td><button type="button" class="btn btn-secondary" onclick="window.location.href='departement.php';"><b>Annuler</b></button></td>
		</tr>
		</table>
    </form>
</div>