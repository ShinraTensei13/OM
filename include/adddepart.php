<?php
require 'include/config.php';

// Vérification de l'accès (Super user uniquement)
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'Super user') {
    header("Location: accueil.php?adminlog=1");
    exit();
}

// Génération et stockage du token CSRF s'il n'existe pas déjà
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('Location: adddepart.php?csrferror=1');
        exit();
    }
    
    if (empty($_POST['depart'])) {
        header('Location: adddepart.php?erreurdepart=1');
        exit();
    }

    $depart = htmlspecialchars(trim($_POST['depart']));

    // Vérifie si le département existe déjà
    $check_query = "SELECT 1 FROM departement WHERE depart = ?";
    if ($check_stmt = $link->prepare($check_query)) {
        $check_stmt->bind_param("s", $depart);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            header('Location: adddepart.php?departexist=1'); 
            exit();
        }
        $check_stmt->close();
    }

    // Insertion du nouveau département
    $insert_query = "INSERT INTO departement (depart) VALUES (?)";
    if ($stmt = $link->prepare($insert_query)) {
        $stmt->bind_param("s", $depart);
        if ($stmt->execute()) {
            header('Location: departement.php?adddepartok=1');
        } else {
            header('Location: adddepart.php?linkresetdwn=1');
        }
        $stmt->close();
    } else {
        header('Location: adddepart.php?sqlerror=1');
    }
    
    $link->close();
    exit();
}

// Récupération des départements (optionnel)
$departments = [];
$select_query = "SELECT depart FROM departement";
if ($result = $link->query($select_query)) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row['depart'];
    }
    $result->free();
}
?>

<div class="wrapper">
    <b><p align="right"><?php echo date('d-m-Y'); ?></p></b>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <!-- Inclusion du token CSRF dans le formulaire -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <table align="center">
            <h2 class="mt-5"><p>Ajouter un département</p></h2>
            <br>
            <tr>
                <td><b>Département</b></td>
                <td><input type="text" name="depart" class="form-control" required></td>
            </tr>
        </table>
        <table align="center">
            <tr>
                <td width="200px">
                    <center>
                        <button type="submit" class="btn btn-primary"><b>Enregistrer</b></button>
                    </center>
                </td>
                <td width="200px">
                    <center>
                        <button type="button" class="btn btn-secondary" onclick="history.back();"><b>Annuler</b></button>
                    </center>
                </td>
            </tr>
        </table>
    </form>
</div>
