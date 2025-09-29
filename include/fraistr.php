<?php
if (!isset($_SESSION['connect']) || $_SESSION['nom'] !== 'Administrateur') {
    header("Location: accueil.php?adminlog=1");
    exit();
}

require "include/config.php";

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Page actuelle, par défaut 1
$perPage = 20; // Nombre d'éléments par page
$offset = ($page - 1) * $perPage; // Calcul de l'offset

try {
    // Récupérer les données depuis la table trpub avec pagination
    $query = "SELECT * FROM trpub ORDER BY id ASC LIMIT ? OFFSET ?";
    $stmt = $link->prepare($query);
    $stmt->bind_param("ii", $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $rowCount = $result->num_rows;

    // Calculer le nombre total de pages
    $totalQuery = "SELECT COUNT(*) as total FROM trpub";
    $totalResult = $link->query($totalQuery);
    $totalRows = $totalResult->fetch_assoc()['total'];
    $totalPages = ceil($totalRows / $perPage); // Nombre total de pages

    // Calculer la plage de pages à afficher
    $pagesToShow = 10; // Nombre de pages à afficher dans la pagination
    $startPage = max(1, $page - floor($pagesToShow / 2)); // Page de départ
    $endPage = min($totalPages, $startPage + $pagesToShow - 1); // Page de fin

    // Ajuster $startPage si $endPage dépasse le nombre total de pages
    if ($endPage - $startPage < $pagesToShow - 1) {
        $startPage = max(1, $endPage - $pagesToShow + 1);
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage()); // Log l'erreur
    die("Une erreur s'est produite. Veuillez réessayer plus tard."); // Message générique pour l'utilisateur
}
?>

<div class="wrapper">
<form>
    <h2 class="my-4">Frais de déplacement</h2>
    <?php include 'include/erreur.php'; // Inclure le fichier d'erreurs ?>

<!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=1">Première</a>
            <a href="?page=<?= $page - 1 ?>">Précédent</a>
        <?php endif; ?>

        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="?page=<?= $i ?>" <?= ($i == $page) ? 'class="active"' : '' ?>><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>">Suivant</a>
            <a href="?page=<?= $totalPages ?>">Dernière</a>
        <?php endif; ?>
    </div>
	</form>
	<br>
	<form>
    <div class="table-responsive">
        <table align="center">
            <thead class="table-header">
                <tr>
                    <th>ID</th>
                    <th>Ville de départ</th>
                    <th>Ville de retour</th>
                    <th>Transport en commun</th>
                    <th>Voiture personnelle</th>
                    <th>Modifier</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rowCount > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td width="50px"><?= htmlspecialchars($row['id']) ?></td>
                            <td width="150px"><b><?= htmlspecialchars($row['villed']) ?></b></td>
                            <td width="150px"><b><?= htmlspecialchars($row['villem']) ?></b></td>
                            <td width="100px"><?= htmlspecialchars($row['fraistr']) ?></td>
                            <td width="100px"><?= htmlspecialchars($row['fxcar']) ?></td>
                            <td align="center" style="width:200px;">
                                
<button type="button" style="width:200px;" class="btn btn-primary" onclick="window.location.href='updatedatatr.php?id=<?= htmlspecialchars($row['id']) ?>';"><b>Modifier</b></button>                            
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Aucune donnée disponible</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</form>
<br>
<form>
    <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=1">Première</a>
            <a href="?page=<?= $page - 1 ?>">Précédent</a>
        <?php endif; ?>

        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="?page=<?= $i ?>" <?= ($i == $page) ? 'class="active"' : '' ?>><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>">Suivant</a>
            <a href="?page=<?= $totalPages ?>">Dernière</a>
        <?php endif; ?>
    </div>
	<form>
</div>