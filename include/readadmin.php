<?php
require_once "config.php";

// Check if user is logged in
if (!isset($_SESSION['nom']) || !isset($_SESSION['type'])) {
    header("location: index.php?errorcnx=1");
    exit();
}

// Validate session variables
$nom = $_SESSION['nom'];
$type = $_SESSION['type'];

// Validate user type
if (!in_array($type, ['Super user', 'Administrateur'])) {
    header("location: accueil.php?accessom=1");
    exit();
}

// Validate and sanitize the id parameter
$id = filter_var(trim($_GET["id"] ?? ''), FILTER_VALIDATE_INT);
if ($id === false) {
    header("location: accueil.php?linkresetdwn=1");
    exit();
}

// Prepare SQL query based on user type
if ($type == 'Super user') {
    $sql = "SELECT * FROM ordred WHERE id = ? ORDER BY id DESC";
} elseif ($type == 'Administrateur') {  // Corrected spelling
    $sql = "SELECT * FROM ordred WHERE id = ? AND referto = ? ORDER BY id DESC";
}

// Prepare and execute the statement
if ($stmt = mysqli_prepare($link, $sql)) {
    // Bind parameters based on user type
    if ($type == 'Administrateur') {
        mysqli_stmt_bind_param($stmt, "is", $id, $nom);
    } else {
        mysqli_stmt_bind_param($stmt, "i", $id);
    }

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

            // Extract and sanitize fields
            $day = $row['dateom'];
            list($an, $mo, $jo) = sscanf($day, "%d-%d-%d");
            $an = substr($an, -2);
            $padded_id = str_pad($row['id'], 3, 0, STR_PAD_LEFT);
            $idom = htmlspecialchars($row['idom'] ?? '');
            $nom = htmlspecialchars($row["nom"]);
            $poste = htmlspecialchars($row["poste"]);
            $obj = htmlspecialchars($row["obj"]);
            $referto = htmlspecialchars($row["referto"]);
            $statut = htmlspecialchars($row["statut"]);
        } else {
            header("location: accueil.php?accessom=1");
            exit();
        }
    } else {
        error_log("SQL Error: " . mysqli_error($link));
        header("location: ../index.php?error=1");
        exit();
    }
    mysqli_stmt_close($stmt);
} else {
    error_log("Prepare failed: " . mysqli_error($link));
    header("location: ../index.php?error=1");
    exit();
}

mysqli_close($link);

// Generate the title safely
$title = !empty($idom) 
    ? htmlspecialchars($idom, ENT_QUOTES, 'UTF-8') 
    : 'OM-' . htmlspecialchars($an, ENT_QUOTES, 'UTF-8') . '-' . htmlspecialchars($padded_id, ENT_QUOTES, 'UTF-8');

echo '<title>' . $title . '</title>';
?>

<div class="wrapper">
<!-- Rest of your HTML and PHP code -->
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
					echo '<td align="center"><a href="accept.php?id='. $row['id'] .'" style="color:white"><button class="btn btn-primary" ><b>Accepter</b></button></a></td>';
					echo '<td align="center"><button class="btn btn-primary" onclick="history.back();" style="color:white"><b>Retour</b></button></td>';
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