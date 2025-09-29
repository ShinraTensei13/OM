<?php
require 'include/config.php';

// Check if user is logged in and is a Super user
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'Super user') {
    header("Location: accueil.php?adminlog=1");
    exit();
}

// Fetch departments from the database
$departments = [];
$query = "SELECT depart FROM departement"; // Assuming 'nom' is the column name for department names
$result = mysqli_query($link, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $departments[] = $row['depart']; // Add each department name to the array
    }
    mysqli_free_result($result);
} else {
    // Handle database error
    die("Erreur lors de la récupération des départements: " . mysqli_error($link));
}

// Fetch referto options from the usersom table (only Super user and Administrateur)
$refertoOptions = [];
$query = "SELECT nom FROM usersom WHERE type IN ('Super user', 'Administrateur') AND nom != 'Administrateur' ORDER BY nom";
$result = mysqli_query($link, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $refertoOptions[] = $row['nom']; // Add each referto name to the array
    }
    mysqli_free_result($result);
} else {
    // Handle database error
    die("Erreur lors de la récupération des responsables: " . mysqli_error($link));
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $requiredFields = ['nom', 'poste', 'depart', 'caisse', 'email', 'password', 'password_confirm', 'type', 'referto'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            header('Location: adduser.php?errorpwd=1&missing=' . urlencode($field));
            exit();
        }
    }

    // Sanitize inputs
    $nom = htmlspecialchars(trim($_POST['nom']));
    $poste = htmlspecialchars(trim($_POST['poste']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $pass_confirm = trim($_POST['password_confirm']);
    $type = htmlspecialchars(trim($_POST['type']));
    $referto = htmlspecialchars(trim($_POST['referto']));
    $depart = htmlspecialchars(trim($_POST['depart']));
    $caisse = htmlspecialchars(trim($_POST['caisse']));
    $actif = 'Actif';

    // Generate the sign automatically
    $nameParts = explode(' ', $nom); // Split the full name into parts
    if (count($nameParts) >= 2) {
        $firstName = $nameParts[0]; // First name
        $lastName = $nameParts[1]; // Last name
        $sign = strtoupper(substr($firstName, 0, 2) . substr($lastName, 0, 2)); // First 2 letters of first and last name
    } else {
        // If the name doesn't have two parts, use the first 4 letters
        $sign = strtoupper(substr($nom, 0, 4));
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: adduser.php?errorpwd=1&invalidemail=1');
        exit();
    }

    // Check if passwords match
    if ($password !== $pass_confirm) {
        header('Location: adduser.php?errorpwd=1&pass=1');
        exit();
    }

    // Validate password strength
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        header('Location: adduser.php?errorpwd=1&weakpass=1');
        exit();
    }

    // Check if email already exists
    $stmt = $link->prepare("SELECT COUNT(*) FROM usersom WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        header('Location: adduser.php?errorpwd=1&email=1');
        exit();
    }

    // Generate a secure token
    $secret = bin2hex(random_bytes(32));

    // Hash the password securely
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into the database
    $stmt = $link->prepare("INSERT INTO usersom (nom, poste, depart, email, password, secret, type, caisse, referto, sign, actif) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssss", $nom, $poste, $depart, $email, $password_hash, $secret, $type, $caisse, $referto, $sign, $actif);

    if ($stmt->execute()) {
        header('Location: adduser.php?successpwd=1');
    } else {
        header('Location: adduser.php?errorpwd=1&db=1');
    }

    $stmt->close();
    $link->close();
    exit();
}
?>

    <div class="wrapper">
        <b><p align="right"><?php echo date('d-m-Y'); ?></p></b>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <table align="center">
                <h2 class="mt-5"><p>Remplir le formulaire pour enregistrer le nouvel utilisateur</p></h2>
                <br>
                <?php
                if (isset($_GET['errorpwd'])) {
                    if (isset($_GET['pass'])) {
                        echo '<p id="error">Les mots de passe ne correspondent pas.</p>';
                    } elseif (isset($_GET['email'])) {
                        echo '<p id="error">Cet utilisateur existe déjà.</p>';
                    } elseif (isset($_GET['db'])) {
                        echo '<p id="error">Erreur de base de données. Veuillez réessayer.</p>';
                    } elseif (isset($_GET['weakpass'])) {
                        echo '<p id="error"><b>Mot de passe très faible. Utilisez au moins 8 caractères, une majuscule et un chiffre.</b></p>';
                    } elseif (isset($_GET['invalidemail'])) {
                        echo '<p id="error">Adresse email invalide.</p>';
                    } elseif (isset($_GET['missing'])) {
                        echo '<p id="error">Le champ ' . htmlspecialchars($_GET['missing']) . ' est requis.</p>';
                    }
                } elseif (isset($_GET['successpwd'])) {
                    echo '<p id="success">Utilisateur ajouté avec succès.</p>';
                }
                ?>
                <tr>
                    <td><b>Nom</b></td>
                    <td><input type="text" name="nom" class="form-control" required></td>
                </tr>
                <tr>
                    <td><b>Poste</b></td>
                    <td><input type="text" name="poste" class="form-control" required></td>
                </tr>
                <tr>
                    <td><b>Département</b></td>
                    <td>
                        <select class="form-control" name="depart" id="depart-select" required>
                            <?php
                            if (!empty($departments)) {
                                foreach ($departments as $department) {
                                    echo '<option value="' . htmlspecialchars($department) . '">' . htmlspecialchars($department) . '</option>';
                                }
                            } else {
                                echo '<option value="">Aucun département trouvé</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><b>Email</b></td>
                    <td><input type="email" name="email" class="form-control" required></td>
                </tr>
                <tr>
                    <td><b>Mot de passe</b></td>
                    <td><input type="password" name="password" class="form-control" required></td>
                </tr>
                <tr>
                    <td><b>Confirmer mot de passe</b></td>
                    <td><input type="password" name="password_confirm" class="form-control" required></td>
                </tr>
                <tr>
                    <td><b>Type d'utilisateur</b></td>
                    <td>
                        <select class="form-control" name="type" id="type-select" required>
                            <option value="Utilisateur">Utilisateur</option>
                            <option value="Super user">Super user</option>
                            <option value="Administrateur">Administrateur</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><b>Droit d'accès aux ordres des missions approuvés</b></td>
                    <td>
                        <select class="form-control" name="caisse" id="type-select" required>
                            <option value="Non">Non</option>
                            <option value="Oui">Oui</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><b>Responsable du coordinateur</b></td>
                    <td>
                        <select class="form-control" name="referto" id="referto-select" required>
                            <?php
                            if (!empty($refertoOptions)) {
                                foreach ($refertoOptions as $referto) {
                                    echo '<option value="' . htmlspecialchars($referto) . '">' . htmlspecialchars($referto) . '</option>';
                                }
                            } else {
                                echo '<option value="">Aucun responsable trouvé</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><center><input type="submit" class="btn btn-primary" value="Enregistrer"></center></td>
                    <td><center><button class="btn btn-secondary" onclick="history.back();"><b>Annuler</b></button></center></td>
                </tr>
            </table>
        </form>
    </div>
