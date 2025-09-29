<?php
require_once "config.php";

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['nom'])) {
    header("location: index.php?errorcnx=1");
    exit();
}

$nom = $_SESSION['nom'];

// Vérifiez si le paramètre id existe et est valide
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);

    // Préparer la requête SQL
    $sql = "SELECT * FROM ordred WHERE id = ? AND nom = ? ORDER BY id DESC";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "is", $param_id, $param_nom);
        $param_id = $id;
        $param_nom = $nom;

        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                // Récupérer les champs
                $day = $row['dateom'];
                list($an, $mo, $jo) = sscanf($day, "%d-%d-%d");
                $an = substr($an, -2, 2);

                $id = str_pad($row['id'], 3, 0, STR_PAD_LEFT);
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
            echo "Oops! Une erreur est survenue.";
        }
    }

    mysqli_stmt_close($stmt);
    mysqli_close($link);
} else {
	
   header("location: accueil.php?linkresetdwn=1");
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
<?php
$statut = $row['statut'];
if($statut != 'Approuvé'){
echo '<div class="wrapper container mt-5 noPrint">';
} else {
echo '<div class="wrapper container mt-5">';
}
?>
			<img href="omct-tunisie.org" src="img/OMCT.png" width="115" height="80" align="right" />
			<br>
			<br>
					<?php
						if (!empty($idom)) {
							echo '<h2 class="mt-5"><center><b>' . htmlspecialchars($row['idom'], ENT_QUOTES, 'UTF-8') . '</b></center></h2>';
							} else {
							echo '<h2 class="mt-5"><center><b>OM-' . htmlspecialchars($an, ENT_QUOTES, 'UTF-8') . '-' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '</b></center></h2>';
							}
					?>
					<?php
					
					echo '<table align="center" class="noPrint"><tr>';
					if($statut != 'Approuvé'){
						echo '<td align="center"><a href="update.php?id='. $row['id'] .'" style="color:white"><button class="btn btn-primary" ><b>Modifier</b></button></a></td>';
						echo '<td align="center"><a href="delete.php?id='. $row['id'] .'" style="color:white"><button class="btn btn-thirdly" ><b>Supprimer</b></button></a></td>';
					}
					else {
						echo '<td align="center"><button onclick="window.print();" class="btn btn-primary"><b>Imprimer</b></button></td>';
					}
					echo '<td align="center"><b><button class="btn btn-secondary" onclick="history.back();"><b>Retour</b></button></b></td>';
					echo '</tr></table>';
					
					?>
					
			<table align="center" width="700px">
					<tr>
                        <td><b>Nom</b></td>
						<td><?php echo $row["nom"]; ?></td>
                    </tr>
                    <tr>
                        <td><b>Poste</b></td>
						<td><?php echo $row["poste"]; ?></td>
                    </tr>
                    <tr>
                        <td><b>Objectif de la mission</b></td>
						<td><?php echo $row["obj"]; ?></td>
                    </tr>
					<tr>
                        <td><b>Date de la mission</b></td>
						<td><?php echo $row["datem"]; ?></td>
                    </tr>
					<tr>
                        <td><b>Ville de la mission</b></td>
						<td><?php echo $row["villem"]; ?></td>
                    </tr>
					<tr>
                        <td><b>Ville du départ</b></td>
						<td><?php echo $row["villed"]; ?></td>
                    </tr>
					<tr>
                        <td><b>Date du départ</b></td>
						<td><?php echo $row["dated"]; ?></td>
                    </tr>
					<tr>
                        <td><b>Date du retour</b></td>
						<td><?php echo $row["dater"]; ?></td>
                    </tr>
					<tr>
                        <td><b>Moyen de transport</b></td>
						<td><?php echo $row["mtr"]; ?></td>
                    </tr>
					<tr>
                        <td><b>Frais de déplacement</b></td>
						<td><?php echo $row["frais"]; ?></td>
                    </tr>
					<tr>
                        <td><b>Perdiem</b></td>
						<td><?php echo $row["perdiem"]; ?></td>
                    </tr>
					<tr>
                        <td><b>Autres dépenses</b></td>
						<td><?php echo $row["autre"]; ?></td>
                    </tr>
					<tr>
                        <td><b>Montant total</b></td>
						<td><?php echo $row["total"]; ?></td>
                    </tr>
					</table>
					<br>
					<br>
					<?php
					$statut = $row['statut'];
					if($statut == 'En attente'){
					echo '';	
						}
					else {
					echo '<div class="form-group" align="right">
                        <td><b>Approuvé par</b></td> : <td>'; echo $row["approvedby"]; echo '</td><br>
						<td>'; echo $row["posteapprove"]; echo '</td><br>
                    </div>';	
						}
						?>
					<br>						
			
</div>