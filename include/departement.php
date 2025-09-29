<?php

if (!isset($_SESSION['connect']) || $_SESSION['nom'] !== 'Administrateur') {
    header("Location: accueil.php?adminlog=1");
    exit();
}

require "include/config.php";

// Pagination
$page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1; // Page actuelle, par défaut 1
$perPage = 20; // Nombre d'éléments par page
$offset = ($page - 1) * $perPage; // Calcul de l'offset

try {
    // Récupérer les données depuis la table departement avec pagination
    $query = "SELECT * FROM departement ORDER BY id ASC LIMIT ? OFFSET ?";
    $stmt = $link->prepare($query);
    $stmt->bind_param("ii", $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $rowCount = $result->num_rows;

    // Calculer le nombre total de pages
    $totalQuery = "SELECT COUNT(*) as total FROM departement";
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
    <br>
    <div class="d-flex justify-content-between">
        <h2 class="pull-left">Modifications des départements</h2>
        <h2 class="pull-right">
            <a href="adddepart.php" class="btn btn-success">
                <i class="bi bi-plus"></i> Ajouter
            </a>
        </h2>
    </div>


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

    <div class="table-responsive">
        <table class="table">
            <thead class="table-header">
                <tr>
                    <th>Département</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rowCount > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td width="300px"><?= htmlspecialchars($row['depart']) ?></td>
                            <td align="center" style="width:150px;">
                                <a href="updatedepart.php?id=<?= htmlspecialchars($row['id']) ?>">
                                    <button class="btn btn-primary"><b>Modifier</b></button>
                                </a>
                            </td>
                            <td align="center" style="width:150px;">
                                <a href="deletedepart.php?id=<?= htmlspecialchars($row['id']) ?>">
                                    <button class="btn btn-primary"><b>Supprimer</b></button>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted">Aucune donnée disponible</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

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
</div>