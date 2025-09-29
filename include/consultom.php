<?php
require_once "config.php";

$nom = $_SESSION['nom']; 
$type = $_SESSION['type']; 
$caisse = $_SESSION['caisse'];

/* Verifiez si le paramettre id existe */
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
	   

if($caisse == 'Oui' OR $type == 'Super user'){
	$sql = "SELECT * FROM ordred WHERE id = ? ORDER BY id DESC";
}else{
header("location: accueil.php?accessom=1");
exit();	
}
    
    if($stmt = mysqli_prepare($link, $sql)){
        
        mysqli_stmt_bind_param($stmt, "i", $param_id);
                
        $param_id = trim($_GET["id"]);
                
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
    
            if(mysqli_num_rows($result) == 1){
                /* recuperer l'enregistrement */
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $day = $row['dateom'];
				list($an, $mo, $jo) = sscanf($day, "%d-%d-%d"); 
				$an=substr("$an", -2, 2);
				
				$id=$row['id'];
				$id=str_pad($id, 3, 0, STR_PAD_LEFT);
                /* recuperer les champs */
			    $idom = $row['idom'];
                $nom = $row["nom"];
                $poste = $row["poste"];
                $obj = $row["obj"];
            } else{
                /* Si pas de id correct retourne la page d'erreur */
                header("location: accueil.php?accessom=1");
                exit();
            }
        } else{
            echo "Oops! une erreur est survenue.";
        }
    }
         
    mysqli_stmt_close($stmt);
       
    mysqli_close($link);
} else{
    /* Si pas de id correct retourne la page d'erreur */
    header("location: accueil.php?linkresetdwn=1");
    exit();
}

?>


<div class="wrapper">
			<img href="omct-tunisie.org" src="img/OMCT.png" width="115" height="80" align="right" />
			<br>
			<br>
                    <h1 class="mt-5 mb-3" color="red"><h2 class="mt-5"><p><p><center><b><?php echo $idom; ?></b></center></h2></h1>
					<?php
					echo '<table align="center" width="700" class="noPrint"><tr>';
					echo '<td align="center"><b><button class="btn btn-secondary" onclick="history.back();"><b>Retour</b></button></b></td>';
					echo '</tr></table>';
					
					?>
					
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
                        <td><b>Ville du départ</b></td><td><?php echo $row["villed"]; ?></td>
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
					<?php
					$statut = $row['statut'];
					if($statut == 'En attente'){
					echo '';	
						}
					else {
					echo '<div class="form-group" align="right">
                        <td><b>Approuvé par</b></td> : <td>'; echo $row["approvedby"]; echo '</td><br>
						<td>'; echo $row["posteapprove"]; echo '</td><br>
                        <p></p>	
                    </div>';	
						}
						?>
					<br>						

					
                </div>