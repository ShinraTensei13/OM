<?php
require 'include/config.php';

// Vérifier si l'utilisateur est connecté et est un Super user
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'Super user') {
    header("Location: accueil.php?adminlog=1");
    exit();
}

// Générer un jeton CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Récupérer les départements
$departments = [];
$query = "SELECT depart FROM departement";
$result = mysqli_query($link, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $departments[] = $row['depart'];
    }
    mysqli_free_result($result);
} else {
    die("Erreur lors de la récupération des départements: " . mysqli_error($link));
}

// Récupérer les responsables
$refertoOptions = [];
$query = "SELECT nom FROM usersom WHERE type IN ('Super user', 'Administrateur') AND nom != 'Administrateur' ORDER BY nom";
$result = mysqli_query($link, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $refertoOptions[] = $row['nom'];
    }
    mysqli_free_result($result);
} else {
    die("Erreur lors de la récupération des responsables: " . mysqli_error($link));
}

// Récupérer les données de l'utilisateur
$id = filter_var(trim($_GET['id'] ?? ''), FILTER_VALIDATE_INT);
if ($id === false) {
    header("Location: accueil.php?error=1");
    exit();
}

$user = [];
$query = "SELECT * FROM usersom WHERE id = ?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);
} else {
    header("Location: accueil.php?error=1");
    exit();
}

$previousNom = $user['nom'];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le jeton CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur de sécurité : jeton CSRF invalide.");
    }

    // Vérifier les champs obligatoires
    $requiredFields = ['nom', 'poste', 'depart', 'caisse', 'email', 'type', 'referto'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            header('Location: updateuser.php?id=' . $id . '&errorpwd=1&missing=' . urlencode($field));
            exit();
        }
    }

    // Nettoyer et valider les entrées
    $nom = htmlspecialchars(trim($_POST['nom']));
    $poste = htmlspecialchars(trim($_POST['poste']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $type = htmlspecialchars(trim($_POST['type']));
    $referto = htmlspecialchars(trim($_POST['referto']));
    $depart = trim($_POST['depart']);
    $caisse = htmlspecialchars(trim($_POST['caisse']));

    // Générer la signature
    $nameParts = explode(' ', $nom);
    if (count($nameParts) >= 2) {
        $firstName = $nameParts[0];
        $lastName = $nameParts[1];
        $sign = strtoupper(substr($firstName, 0, 2) . substr($lastName, 0, 2));
    } else {
        $sign = strtoupper(substr($nom, 0, 4));
    }

    // Valider l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: updateuser.php?id=' . $id . '&errorpwd=1&invalidemail=1');
        exit();
    }

    // Vérifier l'unicité de l'email
    $stmt_check = $link->prepare("SELECT id FROM usersom WHERE email = ? AND id != ?");
    $stmt_check->bind_param("si", $email, $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        header('Location: updateuser.php?id=' . $id . '&errorpwd=1&emailtaken=1');
        exit();
    }

    // Mettre à jour l'utilisateur
    $stmt = $link->prepare("UPDATE usersom SET nom = ?, poste = ?, depart = ?, email = ?, type = ?, caisse = ?, referto = ?, sign = ? WHERE id = ?");
    $stmt->bind_param("ssssssssi", $nom, $poste, $depart, $email, $type, $caisse, $referto, $sign, $id);

    if ($stmt->execute()) {
        // Mettre à jour les enregistrements liés
        $stmt2 = $link->prepare("UPDATE ordred SET nom = ?, referto = ? WHERE nom = ? OR referto = ?");
        $stmt2->bind_param("ssss", $nom, $nom, $previousNom, $previousNom);
        $stmt2->execute();
        $stmt2->close();

        header('Location: profileadmin.php?id=' . $id . '&successpwd=1');
    } else {
        header('Location: updateuser.php?id=' . $id . '&errorpwd=1&db=1');
    }

    $stmt->close();
    $link->close();
    exit();
}
?>

<div class="wrapper">
    <b><p align="right"><?= date('d-m-Y') ?></p></b>
    <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>?id=<?= $id ?>" method="post">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <table align="center">
            <h2 class="mt-5"><p>Modifier les informations de l'utilisateur</p></h2>
            <?php if (isset($_GET['errorpwd'])): ?>
                <?php if (isset($_GET['emailtaken'])): ?>
                    <p id="error">Cet email est déjà utilisé par un autre utilisateur.</p>
                <?php elseif (isset($_GET['invalidemail'])): ?>
                    <p id="error">Adresse email invalide.</p>
                <?php else: ?>
                    <p id="error">Veuillez remplir tous les champs obligatoires.</p>
                <?php endif; ?>
            <?php endif; ?>
            <tr>
                <td><b>Nom</b></td>
                <td><input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" required></td>
            </tr>
            <tr>
                <td><b>Poste</b></td>
                <td><input type="text" name="poste" class="form-control" value="<?= htmlspecialchars($user['poste']) ?>" required></td>
            </tr>
            <tr>
                <td><b>Département</b></td>
                <td>
                    <select class="form-control" name="depart" id="depart-select" required>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?= htmlspecialchars($department) ?>" <?= ($department == $user['depart']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($department) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><b>Email</b></td>
                <td><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required></td>
            </tr>
            <tr>
                <td><b>Type d'utilisateur</b></td>
                <td>
                    <select class="form-control" name="type" id="type-select" required>
                        <option value="Utilisateur" <?= ($user['type'] == 'Utilisateur') ? 'selected' : '' ?>>Utilisateur</option>
                        <option value="Super user" <?= ($user['type'] == 'Super user') ? 'selected' : '' ?>>Super user</option>
                        <option value="Administrateur" <?= ($user['type'] == 'Administrateur') ? 'selected' : '' ?>>Administrateur</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><b>Droit d'accès aux ordres des missions approuvés</b></td>
                <td>
                    <select class="form-control" name="caisse" id="type-select" required>
                        <option value="Non" <?= ($user['caisse'] == 'Non') ? 'selected' : '' ?>>Non</option>
                        <option value="Oui" <?= ($user['caisse'] == 'Oui') ? 'selected' : '' ?>>Oui</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><b>Responsable du coordinateur</b></td>
                <td>
                    <select class="form-control" name="referto" id="referto-select" required>
                        <?php foreach ($refertoOptions as $referto): ?>
                            <option value="<?= htmlspecialchars($referto) ?>" <?= ($referto == $user['referto']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($referto) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><b>Signature (Exemple : Foulani Foulani -> FFO)</b></td>
                <td><input type="text" name="sign" class="form-control" value="<?= htmlspecialchars($user['sign']) ?>" readonly></td>
            </tr>
            <tr>
                <td><center><input type="submit" class="btn btn-primary" value="Enregistrer"></center></td>
                <td><center><button type="button" class="btn btn-secondary" onclick="history.back();"><b>Annuler</b></button></center></td>
            </tr>
        </table>
    </form>
</div>