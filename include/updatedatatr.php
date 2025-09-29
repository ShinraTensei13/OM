<?php
if (!isset($_SESSION['connect']) || $_SESSION['nom'] !== 'Administrateur') {
    header("Location: accueil.php?adminlog=1");
    exit();
}

require "include/config.php";

// Initialisation des variables
$villed = $villem = $fraistr = $fxcar = "";
$villed_err = $villem_err = $fraistr_err = $fxcar_err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !empty($_POST['id'])) {
    // Récupérer l'ID de l'enregistrement à modifier
    $id = $_POST['id'];

    // Récupération et validation des champs
    $villed = trim($_POST['villed']);
    if (empty($villed)) {
        $villed_err = "La ville de départ est requise.";
    }

    $villem = trim($_POST['villem']);
    if (empty($villem)) {
        $villem_err = "La ville de retour est requise.";
    }

    $fraistr = trim($_POST['fraistr']);
    if (empty($fraistr) || !is_numeric($fraistr)) {
        $fraistr_err = "<p id='error'>Les frais de transport en commun doivent être un nombre.</p>";
    } else {
        $fraistr = (float)$fraistr;
    }

    $fxcar = trim($_POST['fxcar']);
    if (empty($fxcar) || !is_numeric($fxcar)) {
        $fxcar_err = "<p id='error'>Les frais de carburant doivent être un nombre.</p>";
    } else {
        $fxcar = (float)$fxcar;
    }

    // Si aucune erreur, on met à jour l'enregistrement dans la base de données
    if (empty($villed_err) && empty($villem_err) && empty($fraistr_err) && empty($fxcar_err)) {
        $sql = "UPDATE trpub SET villed = ?, villem = ?, fraistr = ?, fxcar = ? WHERE id = ?";
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("ssdsi", $villed, $villem, $fraistr, $fxcar, $id);
            if ($stmt->execute()) { 
                header("Location: fraistr.php?updtrsuccess=1");
                exit();
            } else {
                header("Location: fraistr.php?updtrerror=1");
                exit();
            }
            $stmt->close();
        }
    }
} elseif (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    // Cas d'affichage du formulaire : récupération de l'enregistrement à modifier
    $id = trim($_GET['id']);
    $sql = "SELECT * FROM trpub WHERE id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $villed = $row['villed'];
                $villem = $row['villem'];
                $fraistr = $row['fraistr'];
                $fxcar = $row['fxcar'];
            } else {
                header("Location: fraistr.php?InvalidID=1");
                exit();
            }
        } else {
            echo "Une erreur est survenue lors de la récupération des données.";
        }
        $stmt->close();
    }
} else {
    // Aucun ID valide n'a été fourni
    header("Location: fraistr.php?InvalidID=1");
    exit();
}
?>

<div class="wrapper">
    <h2 class="my-4">Modifier les frais de déplacement</h2>
	
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

        <div class="form-group">
            <label for="villed"><b>Ville de départ</b></label>
            <select name="villed" id="villed" class="form-control">
                <option value="">Sélectionner une ville</option>
                <?php
                $sql_villes = "SELECT DISTINCT villed FROM trpub ORDER BY villed ASC";
                if ($result_villes = $link->query($sql_villes)) {
                    while ($row_ville = $result_villes->fetch_assoc()) {
                        $selected = ($row_ville['villed'] == $villed) ? "selected" : "";
                        echo '<option value="'. htmlspecialchars($row_ville['villed']) .'" '. $selected .'>'. htmlspecialchars($row_ville['villed']) .'</option>';
                    }
                }
                ?>
            </select>
            <span class="error"><?php echo $villed_err; ?></span>
        </div>

        <div class="form-group">
            <label for="villem"><b>Ville de retour</b></label>
            <select name="villem" id="villem" class="form-control">
                <option value="">Sélectionner une ville</option>
                <?php
                $sql_villes = "SELECT DISTINCT villem FROM trpub ORDER BY villem ASC";
                if ($result_villes = $link->query($sql_villes)) {
                    while ($row_ville = $result_villes->fetch_assoc()) {
                        $selected = ($row_ville['villem'] == $villem) ? "selected" : "";
                        echo '<option value="'. htmlspecialchars($row_ville['villem']) .'" '. $selected .'>'. htmlspecialchars($row_ville['villem']) .'</option>';
                    }
                }
                ?>
            </select>
            <span class="error"><?php echo $villem_err; ?></span>
        </div>

        <div class="form-group">
            <label for="fraistr"><b>Frais de transport en commun</b></label>
            <input type="text" name="fraistr" id="fraistr" class="form-control" value="<?php echo htmlspecialchars($fraistr); ?>">
            <span class="error"><?php echo $fraistr_err; ?></span>
        </div>

        <div class="form-group">
            <label for="fxcar"><b>Frais de carburant</b></label>
            <input type="text" name="fxcar" id="fxcar" class="form-control" value="<?php echo htmlspecialchars($fxcar); ?>">
            <span class="error"><?php echo $fxcar_err; ?></span>
        </div>
		<table align="center">
        <tr class="form-group mt-3">
            <td><button type="submit" class="btn btn-primary"><b>Mettre à jour</b></button></td>
            <td><button type="button" class="btn btn-secondary" onclick="window.location.href='fraistr.php';"><b>Annuler</b></button></td>
		</tr>
		</table>
    </form>
</div>