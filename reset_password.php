<?php
require "include/config.php";

// Check if the token and passwords are provided
if (!empty($_GET['token']) && !empty($_POST['password']) && !empty($_POST['password_confirm'])) {
    $token = $_GET['token'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Check if passwords match
    if ($password !== $password_confirm) {
        header("location: reset_password.php?token=$token&error=Les mots de passe ne correspondent pas.");
        exit();
    }

    // Validate password strength
    $isStrong = preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password);
    if (!$isStrong) {
        $errorMessage = urlencode("Le mot de passe doit contenir :
            <ul class='mt-2'>
                <li>Minimum 8 caractères</li>
                <li>Au moins une majuscule</li>
                <li>Au moins une minuscule</li>
                <li>Au moins un chiffre</li>
                <li>Au moins un caractère spécial</li>
            </ul>");
        header("location: reset_password.php?token=$token&error=$errorMessage");
        exit();
    }

    // Find the user with the token
    $req = $db->prepare("SELECT * FROM usersom WHERE reset_token = ? AND reset_expires > NOW()");
    $req->execute([$token]);
    $user = $req->fetch();

    if ($user) {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $secret = bin2hex(random_bytes(32));

        // Update the password and clear the token
        $db->prepare("UPDATE usersom SET password = ?, secret = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?")
           ->execute([$hashed_password, $secret, $user['id']]);

        header("location: index.php?pwdrest=1");
        exit();
    } else {
        header("location: index.php?pwderror=1");
        exit();
    }
}

echo '<title>Réinitialisation du mot de passe</title>';
include 'include/head.php';
?>

<body class="noPrint">
    <header>
        <h1><b>Ordre de mission</b></h1>
    </header>

    <div class="main-content">
        <div class="login-container">
            <form action="reset_password.php?token=<?= htmlspecialchars($_GET['token']) ?>" method="POST">
                <h2>Bienvenue</h2>
                <?php
                if (isset($_GET['error'])) {
                    echo '<div class="alert alert-danger"><b>' . htmlspecialchars($_GET['error']) . '</b></div>';
                }
                ?>
                <p>Veuillez entrer votre nouveau mot de passe</p>

                <!-- Password Requirements -->
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

                <div class="textbox">
                    <input type="password" id="password" name="password" required placeholder="Entrez votre nouveau mot de passe"
                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}"
                           title="Doit contenir au moins 8 caractères avec majuscule, minuscule, chiffre et caractère spécial">
                </div>
                <div class="textbox">
                    <input type="password" id="password_confirm" name="password_confirm" required placeholder="Confirmez votre nouveau mot de passe">
                </div>
                <br>
                <button class="btn" type="submit" style="color:white"><b>Réinitialiser le mot de passe</b></button>
            </form>
        </div>
    </div>

    <?php
    include 'include/footer.php';
    ?>

    <script>
        // Client-side validation feedback
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const requirements = {
                length: password.length >= 8,
                lower: /[a-z]/.test(password),
                upper: /[A-Z]/.test(password),
                number: /\d/.test(password),
                special: /[\W_]/.test(password)
            };
            
            const labels = document.querySelectorAll('.password-requirements li');
            labels[0].style.color      = requirements.length ? 'green' : 'inherit';
			labels[0].style.fontWeight = requirements.length ? 'bold'  : 'normal';

			labels[1].style.color      = requirements.upper ? 'green' : 'inherit';
			labels[1].style.fontWeight = requirements.upper ? 'bold'  : 'normal';

			labels[2].style.color      = requirements.lower ? 'green' : 'inherit';
			labels[2].style.fontWeight = requirements.lower ? 'bold'  : 'normal';

			labels[3].style.color      = requirements.number ? 'green' : 'inherit';
			labels[3].style.fontWeight = requirements.number ? 'bold'  : 'normal';

			labels[4].style.color      = requirements.special ? 'green' : 'inherit';
			labels[4].style.fontWeight = requirements.special ? 'bold'  : 'normal';

        });
    </script>
</body>
</html>