<?php
require_once "config.php";

$nom = $_SESSION['nom'];
$caisse = isset($_SESSION['caisse']) ? $_SESSION['caisse'] : 'Non'; // Valeur par défaut 'Non'

// Vérifiez si le paramètre id existe et est valide
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = intval(trim($_GET["id"]));

    // Vérifier si l'utilisateur est autorisé à voir la page
    if ($caisse == 'Oui') {
        // Si 'caisse' est 'Oui', pas besoin de vérifier le nom
        $sql = "SELECT * FROM ordred WHERE id = ? ORDER BY id DESC";
    } else {
        // Sinon, il doit correspondre au nom
        $sql = "SELECT * FROM ordred WHERE id = ? AND nom = ? ORDER BY id DESC";
    }

    if ($stmt = mysqli_prepare($link, $sql)) {
        if ($caisse == 'Oui') {
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            $param_id = $id;
        } else {
            mysqli_stmt_bind_param($stmt, "is", $param_id, $param_nom);
            $param_id = $id;
            $param_nom = $nom;
        }

        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                // Récupérer les champs
                $day = $row['dateom'];
                list($an, $mo, $jo) = sscanf($day, "%d-%d-%d");
                $an = substr($an, -2, 2);

                $id = str_pad($row['id'], 3, '0', STR_PAD_LEFT);
                $idom = $row['idom'];
                $nom = htmlspecialchars($row["nom"]);
                $poste = htmlspecialchars($row["poste"]);
                $obj = htmlspecialchars($row["obj"]);
                $statut = htmlspecialchars($row["statut"]);
            } else {
                header("location: accueil.php?accessom=1");
                exit();
            }
        } else {
            error_log("Erreur SQL: " . mysqli_error($link)); // Log de l'erreur
            echo "Oops! Une erreur est survenue. Veuillez réessayer plus tard.";
        }

        mysqli_stmt_close($stmt);
    }
    mysqli_close($link);
} else {
    header("location: ../index.php?errorcnx=1");
    exit();
}
?>
<?php
if (!empty($idom)) {
    echo '<title>' . htmlspecialchars($row['idom'], ENT_QUOTES, 'UTF-8') . '</title>';
} else {
    echo '<title>OM-' . htmlspecialchars($an, ENT_QUOTES, 'UTF-8') . '-' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '</title>';
}
?>


    <div class="wrapper">
			<div align="right"><img href="omct-tunisie.org" src="img/OMCT.png" width="115" height="80" /></div>
                    <h1 class="mt-5 mb-3" color="red"><h2 class="mt-5"><p><p><center><b>OM&nbsp;<?php echo $an; ?>-<?php echo $id; ?></b></center></h2></h1>
					<?php
					$statut = $row['statut'];
					if($statut == 'Approuvé'){
					echo '<table align="center" width="700" class="noPrint"><tr>';
					echo '<td align="center"><button onclick="window.print();" class="btn btn-secondary"><b>Imprimer</b></button></td>';
					echo '<td align="center"><b><button class="btn btn-primary" onclick="history.back();"><b>Retour</b></button></b></td>';
					echo '</tr></table>';
					}
					else {
					echo '<table align="center" width="700"><tr>';
					echo '<td align="center"><a href="update.php?id='. $row['id'] .'"  class="btn btn-secondary"><b>Modifier</b></a></td>';
                    echo '<td align="center"><button><a href="delete.php?id='. $row['id'] .'" style="color:white"><b>Supprimer</b></a></button></td>';
                    echo '<td align="center"><a href="index.php" class="btn btn-primary"><b>Retour</b></a></td>';
					echo '</tr></table>';
					}
					?>
					</tr>
					</table>
					<table align="center" width="700">
					<tr>
                        <td><b>Nom</b></td><td><?php echo $row["nom"]; ?></td>
                        <p></p>
                    </tr>
                    <tr>
                        <td><b>Poste</b></td><td><?php echo $row["poste"]; ?></td>
                        <p></p>
                    </tr>
                    <tr>
                        <td><b>Objectif de la mission</b></td><td><?php echo $row["obj"]; ?></td>
                        <p></p>
                    </tr>
					<tr>
                        <td><b>Date de la mission</b></td><td><?php echo $row["datem"]; ?></td>
                        <p></p>
                    </tr>
					<tr>
                        <td><b>Ville de la mission</b></td><td><?php echo $row["villem"]; ?></td>
                        <p></p>
                    </tr>
					<tr>
                        <td><b>ville du départ</b></td><td><?php echo $row["villed"]; ?></td>
                        <p></p>
                    </tr>
					<tr>
                        <td><b>Date du départ</b></td><td><?php echo $row["dated"]; ?></td>
                        <p></p>
                    </tr>
					<tr>
                        <td><b>Date du retour</b></td><td><?php echo $row["dater"]; ?></td>
                        <p></p>
                    </tr>
					<tr>
                        <td><b>Moyen de transport</b></td><td><?php echo $row["mtr"]; ?></td>
                        <p></p>
                    </tr>
					<tr>
                        <td><b>Frais de déplacement</b></td><td><?php echo $row["frais"]; ?></td>
                        <p></p>
                    </tr>
					<tr>
                        <td><b>Perdiem</b></td><td><?php echo $row["perdiem"]; ?></td>
                        <p></p>
                    </tr>
					<?php
					$autre = $row['autre'];
					if($autre == 0 OR $autre ==''){
						
						}
					else {
			  echo '<tr>
						<td><b>Autres dépenses</b></td><td>';echo $row["autre"];'</td>
						<p></p>
					</tr>';	
						}
						?>
						<p></p>
					<tr>
                        <td><b>Montant total</b></td><td><?php echo $row["total"]; ?></td>
                        <p></p>
                    </tr>
					</table>
					<br>
					<br>
					<div class="form-group" align="right">
                        <td><b>Approuvé par</b></td> : <td><?php echo $row["approvedby"]; ?></td><br>
						<td><?php echo $row["posteapprove"]; ?></td><br>
                        <p></p>	
                    </div>
					<br>

					
                </div>