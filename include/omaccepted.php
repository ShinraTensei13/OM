
					<div>
                        <h2 class="pull-left">Liste des ordres de missions approuvés</h2>
                    </div>

<?php 
require_once "config.php";             
					$nom    = $_SESSION['nom'];
					$type   = $_SESSION['type'];
					$caisse = $_SESSION['caisse'];
					$sadmin = 'Super user';
					$admin  = 'Administrateur';
					$user   = 'Utilisateur';
					$approuve = 'Approuvé';
					$currentDateTime = date('y-m-d');
					$day = date('y');
					
					
                    $sql = "SELECT * FROM ordred WHERE statut = '$approuve' ORDER BY id DESC";
                    					
					if($result = mysqli_query($link, $sql)){
                        if(mysqli_num_rows($result) > 0){
                            echo '<table class="table">';
                                echo "<thead>";
                                    echo "<tr>";
                                        echo "<th width='250'>N°</th>";
										echo "<th width='300'>Nom du coordinateur</th>";
                                        echo "<th width='600'>Objectif</th>";
										echo "<th width='300'>Période de la mission</th>";
										echo "<th width='200'>Ville de la mission</th>";
										echo "<th width='200'>Moyen de transport</th>";								
										echo "<th width='100'>Total</th>";
										
                                    echo "</tr>";
                                echo "</thead>";
                                echo "<tbody>";
                                while($row = mysqli_fetch_array($result)){
									$dateom = $row['dateom'];
									$date = $row['dateom']; // Supposons que $row['dateom'] contient "2023-10-25"
									$year = date('y', strtotime($date)); // Extrait l'année (ex: "2023")
                                    echo "<tr>";
										echo "<td>";
									if($row['idom'] != '' OR $row['idom'] != 0) {
										echo '<b><a href="consulta.php?id='. $row['id'] .'" class="me-3" >' . $row['idom'] . '</a></b>';
									} else {
										echo '<b><a href="consulta?id='. $row['id'] .'" class="me-3" >OM-'. $year . '-' . $row['id'] . '</a></b>';
									}
                                        echo "</td>";
										echo "<td>" . $row['nom'] . "</td>";
                                        echo "<td>" . $row['obj'] . "</td>";
										echo "<td> <b>Date du départ : </b>" . $row['dated'] . "<br><b>Date du retour &nbsp;: </b>" . $row['dater'] . "</td>";
										echo "<td>" . $row['villem'] . "</td>";
										echo "<td>" . $row['mtr'] . "</td>";
										echo "<td align='right'><b>" . $row['total'] . "</b></td>";
                                    echo "</tr>";
                                }
                                echo "</tbody>";                            
                            echo "</table>";
                            /* Free result set */
                            mysqli_free_result($result);
                        } else{
                            echo '<div class="alert alert-danger"><em>Aucun ordre de mission en cours de traitement</div>';
                        }
                    } else{
                        echo "Oops! Une erreur est survenue";
                    }
?>
