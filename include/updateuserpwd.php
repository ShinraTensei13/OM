<?php
// Redirect if not logged in
if (!isset($_SESSION['connect'])) {
    header("Location: accueil.php?redirecterror=1");
    exit();
}

// Only allow Super users
if ($_SESSION['type'] !== 'Super user') {
    header("Location: accueil.php?adminlog=1");
    exit();
}

// Include database configuration
require "include/config.php";

// Validate and sanitize the user ID
$id = filter_var(trim($_GET['id'] ?? ''), FILTER_VALIDATE_INT);
if ($id === false) {
    header("Location: accueil.php?linkresetdwn=1");
    exit();
}
 if($_SESSION['nom'] != 'Administrateur'){
    header("Location: accueil.php?adminlog=1");
    exit();
}	 
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
        $error = "Veuillez remplir tous les champs.";
    } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Validate password strength
        $password = $_POST['new_password'];
        $isStrong = preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password);
        
        if (!$isStrong) {
            $error = "Le mot de passe doit contenir :
                    <ul class='mt-2'>
                        <li>Minimum 8 caractères</li>
                        <li>Au moins une majuscule</li>
                        <li>Au moins une minuscule</li>
                        <li>Au moins un chiffre</li>
                        <li>Au moins un caractère spécial</li>
                    </ul>";
        } else {
            // Hash the new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Update the password in the database
            try {
                $query = "UPDATE usersom SET password = ? WHERE id = ?";
                $stmt = $link->prepare($query);
                if (!$stmt) {
                    throw new Exception("Failed to prepare statement: " . $link->error);
                }

                $stmt->bind_param("si", $hashed_password, $id);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    header("Location: profileadmin.php?id=$id&pwdrest=1");
					exit();
                } else {
                    $error = "Aucun utilisateur trouvé ou aucune modification effectuée.";
                }

                $stmt->close();
            } catch (Exception $e) {
                error_log("Database error: " . $e->getMessage());
                $error = "Une erreur technique est survenue.";
            }
        }
    }
}

// Fetch user details for display
try {
    $query = "SELECT id, nom FROM usersom WHERE id = ?";
    $stmt = $link->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $link->error);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        header("Location: accueil.php?linkresetdwn=1");
        exit();
    }

    $stmt->close();
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    die("Erreur lors de la récupération des informations utilisateur.");
}
?>

    <div class="wrapper">
        <form method="POST" action="">
		<table align="center">
		<h2 class="mt-5" style="color:black;">
    Modification du mot de passe de:  
    <span style="color:#bd0202;"><?php echo htmlspecialchars($user['nom']); ?></span>
</h2>
<br>

        <div class="password-requirements alert alert-info">
            <strong>Exigences de sécurité :</strong>
            <ul class="mt-2">
                <li>Minimum 8 caractères</li>
                <li>Au moins une lettre majuscule</li>
                <li>Au moins une lettre minuscule</li>
                <li>Au moins un chiffre</li>
                <li>Au moins un caractère spécial (ex: !@#$%^&*)</li>
            </ul>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
            <tr class="form-group">
                <td for="new_password"><b>Nouveau mot de passe</b></td>
                <td><input type="password" name="new_password" id="new_password" class="form-control" required
                       pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}"
                       title="Doit contenir au moins 8 caractères avec majuscule, minuscule, chiffre et caractère spécial"></td>
            </tr>

            <tr class="form-group">
                <td for="confirm_password"><b>Confirmer le mot de passe</b></td>
                <td><input type="password" name="confirm_password" id="confirm_password" class="form-control" required></td>
            </tr>
			<tr class="form-group">
					<td><center><button type="submit" class="btn btn-primary" onclick="submit"><b>Mettre à jour</b></button></center></td>
					<td><center><button type="button" class="btn btn-secondary" onclick="window.location.href='profileadmin.php?id=<?= $id ?>';"><b>Annuler</b></button></center></td>
			</tr>
			</table>
        </form>
		
    </div>

    <script>
        // Client-side validation feedback
        document.getElementById('new_password').addEventListener('input', function(e) {
            const password = e.target.value;
            const requirements = {
                length: password.length >= 8,
                lower: /[a-z]/.test(password),
                upper: /[A-Z]/.test(password),
                number: /\d/.test(password),
                special: /[\W_]/.test(password)
            };
            
            const labels = document.querySelectorAll('.password-requirements li');
            labels[0].style.color = requirements.length ? 'green' : 'inherit';
            labels[1].style.color = requirements.lower ? 'green' : 'inherit';
            labels[2].style.color = requirements.upper ? 'green' : 'inherit';
            labels[3].style.color = requirements.number ? 'green' : 'inherit';
            labels[4].style.color = requirements.special ? 'green' : 'inherit';
        });
    </script>
