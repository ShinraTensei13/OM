<?php
require_once "include/config.php";

// Vérification des droits d'accès
if (!isset($_SESSION['connect']) || $_SESSION['type'] !== 'Super user') {
    header("Location: index.php");
    exit();
}

// Initialisation des variables
$currentDateTime = date('Y-m-d');
$error = '';

// Traitement de la requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["id"])) {
    $id = (int)$_POST["id"];
    
    try {
        // Récupération du statut actuel
        $stmt = $link->prepare("SELECT actif FROM usersom WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $newStatus = $row['actif'] === 'Actif' ? 'Inactif' : 'Actif';
            
            // Mise à jour du statut
            $updateStmt = $link->prepare("UPDATE usersom SET actif = ? WHERE id = ?");
            $updateStmt->bind_param("si", $newStatus, $id);
            
            if ($updateStmt->execute()) {
                header("Location: users.php?accountstatut=1");
                exit();
            }
        }
        $error = "Erreur lors de la mise à jour";
    } catch (Exception $e) {
        $error = "Erreur de base de données : " . $e->getMessage();
    }
    
    header("Location: users.php?error=" . urlencode($error));
    exit();
}

// Traitement de la requête GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET["id"])) {
    $id = (int)$_GET["id"];
    
    try {
        $stmt = $link->prepare("SELECT * FROM usersom WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Échappement des sorties
            $nom = htmlspecialchars($user['nom']);
            $poste = htmlspecialchars($user['poste']);
            $email = htmlspecialchars($user['email']);
            $actif = htmlspecialchars($user['actif']);
        } else {
            throw new Exception("Utilisateur introuvable");
        }
    } catch (Exception $e) {
        header("Location: users.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: users.php");
    exit();
}
?>

<div class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mt-5">Changement du statut utilisateur</h2>
                
                <!-- Carte d'information -->
                <div class="card mb-4">
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">Nom :</dt>
                            <dd class="col-sm-9"><?= $nom ?></dd>

                            <dt class="col-sm-3">Poste :</dt>
                            <dd class="col-sm-9"><?= $poste ?></dd>

                            <dt class="col-sm-3">Email :</dt>
                            <dd class="col-sm-9"><?= $email ?></dd>

                            <dt class="col-sm-3">Statut actuel :</dt>
                            <dd class="col-sm-9"><?= $actif ?></dd>
                        </dl>
                    </div>
                </div>

                <!-- Formulaire de confirmation -->
                <form method="post">
                    <div class="alert alert-warning">
                        <h4 class="alert-heading">Attention !</h4>
						<?php if($actif == 'Actif'){ ?>
                        <p>Vous êtes sur le point de désactiver ce compte.</p>
						<?php } else { ?>
						<p>Vous êtes sur le point de réactiver ce compte.</p>
						<?php } ?>
                        <p>Nouveau statut : <strong><?= $actif === 'Actif' ? 'Inactif' : 'Actif' ?></strong></p>
                    </div>

                    <input type="hidden" name="id" value="<?= $id ?>">
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-toggle-off"></i> Confirmer le changement
                        </button>
                        <a href="users.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>