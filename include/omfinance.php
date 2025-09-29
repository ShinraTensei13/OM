<br>
<?php 
require_once "config.php";

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify session variables existence             
$nom = $_SESSION['nom'] ?? '';
$type = $_SESSION['type'] ?? '';
$caisse = $_SESSION['caisse'] ?? '';

// Constants declaration
define('SADMIN', 'Super user');
define('ADMIN', 'Administrateur');
define('USER', 'Utilisateur');
define('APPROVED', 'Approuvé');
define('PENDING', 'En attente');
?>


<div class="mb-4">
    
    <form method="GET" action="">
        <table>
            <tr>
                <td>
                    <div class="row">
					<h2 class="pull-float">Filtrer les ordres des missions</h2><br>
                        <div class="col-md-3">
                            <label for="start_date"><b>Date de début</b></label>
                            <input type="date" name="start_date" id="start_date" class="form-control" 
                            value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date"><b>Date de fin</b></label>
                            <input type="date" name="end_date" id="end_date" class="form-control" 
                            value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
                        </div>
						<div class="col-md-3">
                            <label for="name_filter"><b>Nom du coordinateur</b></label>
                            <select name="name_filter" id="name_filter" class="form-control">
                                <option value="">Tous</option>
                                <?php
                                try {
                                    $name_query = "SELECT DISTINCT nom FROM ordred ORDER BY nom";
                                    $name_result = mysqli_query($link, $name_query);
                                    
                                    if ($name_result) {
                                        while ($name_row = mysqli_fetch_assoc($name_result)) {
                                            $selected = (($_GET['name_filter'] ?? '') === $name_row['nom']) ? 'selected' : '';
                                            echo '<option value="'.htmlspecialchars($name_row['nom']).'" '.$selected.'>'
                                                  .htmlspecialchars($name_row['nom']).'</option>';
                                        }
                                    }
                                } catch(Exception $e) {
                                    error_log("Name filter error: ".$e->getMessage());
                                }
                                ?>
                            </select>
                        </div>
					</div>
                    <br>
                    <div class="col-md-6 mb-2">
                        <div class="d-flex">
                            <button type="submit" class="btn-primary flex-fill"><b>Filtrer</b></button>
                            <a href="?" class="btn btn-secondary flex-fill"><b>Réinitialiser</b></a>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </form>
</div>
<form>
<?php 
function displayMissions($link, $status, $isApproved = false) {
    try {
        $sql = "SELECT * FROM ordred WHERE statut = ?";
        $params = [];
        $types = "s";
        $params[] = &$status;

        // Name filter
        if (!empty($_GET['name_filter'])) {
            $sql .= " AND nom = ?";
            $params[] = $_GET['name_filter'];
            $types .= "s";
        }

        // Date filter
        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $sql .= " AND dated BETWEEN ? AND ?";
            $params[] = $_GET['start_date'];
            $params[] = $_GET['end_date'];
            $types .= "ss";
        }

        $sql .= " ORDER BY id DESC";

        $stmt = mysqli_prepare($link, $sql);
        if (!$stmt) throw new Exception("Prepare failed: ".mysqli_error($link));

        // Dynamic parameter binding
        $bind_params = array($types);
        foreach ($params as &$param) {
            $bind_params[] = &$param;
        }
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bind_params));

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result) > 0) {
            $totalSum = 0;
            $rows = [];
            while($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
                $totalSum += (float)$row['total'];
            }

            echo '<table class="table">';
            
            // Total display at top-right
            echo '<div class="position-absolute top-0 end-0 mt-2 me-3"><span class="badge fs-4" style="background-color: #bd0202; color: white;">Total: '.number_format($totalSum, 3, ',', ' ').'</span></div>';
            
            echo "<thead>";
            echo "<tr>";
            echo "<th width='250'>N°</th>";
			echo "<th width='250'>Date de l'OM</th>";
            echo "<th width='300'>Nom du coordinateur</th>";
            echo "<th width='600'>Objectif</th>";
            echo "<th width='300'>Période de la mission</th>";
            echo "<th width='200'>Ville de la mission</th>";
            echo "<th width='200'>Moyen de transport</th>";                                
            echo "<th width='100'>Total</th>";
            echo "</tr>";
            echo "</thead>";
            
            echo "<tbody>";
            foreach($rows as $row) {
                $id = htmlspecialchars($row['id']);
                $dateom = htmlspecialchars($row['dateom']);
                $year = date('y', strtotime($dateom));
                $idom = !empty($row['idom']) ? htmlspecialchars($row['idom']) : 'OM-'.$year.'-'.$id;
                $totalValue = (float)$row['total'];

                echo "<tr>";
                echo "<td>";
                echo '<b><a href="'.($isApproved ? 'consulta.php' : 'consult.php').'?id='. $id .'" class="me-3">' . $idom . '</a></b>';
                echo "</td>";
				echo "<td>" . htmlspecialchars($row['dateom']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nom']) . "</td>";
                echo "<td>" . htmlspecialchars($row['obj']) . "</td>";
                echo "<td><b>Date du départ : </b>" . htmlspecialchars($row['dated']) . 
                     "<br><b>Date du retour &nbsp;: </b>" . htmlspecialchars($row['dater']) . "</td>";
                echo "<td>" . htmlspecialchars($row['villem']) . "</td>";
                echo "<td>" . htmlspecialchars($row['mtr']) . "</td>";
                echo "<td align='right'><b>" . number_format($totalValue, 3, ',', ' ') . "</b></td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            
            mysqli_free_result($result);
        } else {
            echo '<div class="alert alert-danger"><em>Aucun résultat trouvé</em></div>';
        }
        
        mysqli_stmt_close($stmt);
    } catch(Exception $e) {
        error_log("Database error: ".$e->getMessage());
        echo '<div class="alert alert-danger">Erreur technique</div>';
    }
}

// Display pending missions
echo '<form><div class="position-relative">';
echo '<h2 class="pull-float">Liste des ordres de missions en attente d\'approbation</h2>';
displayMissions($link, PENDING);
echo '</div></form><br>';

// Approved missions section
echo '<form><div class="position-relative">';
echo '<h2 class="float-left">Liste des ordres de missions approuvés</h2>';
displayMissions($link, APPROVED, true);
echo '</div></form>';

mysqli_close($link);
?>
</form>
<br>