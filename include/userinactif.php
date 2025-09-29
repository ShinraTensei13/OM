<form>  
<div>
                        <h2 class="pull-left">Liste des utilisateurs inactifs</h2>

</div>
                  
                    <?php 
require_once "include/config.php";
if (!isset($_SESSION['type']) || $_SESSION['type'] != 'Super user') {
    header("location: accueil.php?accessom=1");
    exit();
}

                    
                    $sql = "SELECT id, nom, email, poste, depart, referto FROM usersom WHERE actif = 'Inactif' ORDER BY nom";
$result = mysqli_query($link, $sql);

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        echo '<table class="table">';
        echo "<thead><tr><th>Nom</th><th>Email</th><th>Poste</th><th>Département</th><th>Responsable</th></tr></thead>";
        echo "<tbody>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo '<td><b><a href="profileadmin.php?id=' . htmlspecialchars($row['id']) . '" class="me-3 profile-link" style="text-decoration:none">' . htmlspecialchars($row['nom']) . '</a></b></td>';
            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
            echo '<td>' . htmlspecialchars($row['poste']) . '</td>';
            echo '<td>' . htmlspecialchars($row['depart']) . '</td>';
            echo '<td>' . htmlspecialchars($row['referto']) . '</td>';
            echo "</tr>";
        }
        echo "</tbody></table>";
        mysqli_free_result($result);
    } else {
        echo '<div class="alert alert-danger"><em>Aucun utilisateur inactif trouvé.</em></div>';
    }
} else {
    echo '<div class="alert alert-danger"><em>Une erreur est survenue lors de la récupération des utilisateurs.</em></div>';
}
?>
</form>