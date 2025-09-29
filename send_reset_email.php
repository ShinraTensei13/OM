<?php
session_start();
require 'vendor/autoload.php';  // Assure-toi d'avoir PHPMailer installé
require 'include/config.php';

// Configurer le fuseau horaire
date_default_timezone_set('Europe/Paris');

// Vérifier si l'email est fourni via le formulaire
if (isset($_POST['email'])) {
    $email = trim($_POST['email']);

    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "L'adresse email est invalide.";
        exit;
    }

    try {
        // Connexion à la base de données
        
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vérifier si l'email existe dans la base de données
        $stmt = $db->prepare("SELECT * FROM usersom WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Générer un token unique et une expiration
            $token = bin2hex(random_bytes(32));  // Générer un jeton sécurisé
            $expire_time = date('Y-m-d H:i:s', strtotime('+1 hour'));  // Le token expire après 1 heure

            // Enregistrer le jeton et son expiration dans la base de données
            $stmt = $db->prepare("UPDATE usersom SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $stmt->execute([$token, $expire_time, $email]);

            // Construire le lien de réinitialisation du mot de passe
            $reset_link = "https://jorani.omct-tunisie.org/ordre/reset_password.php?token=$token";

            // Utilisation de PHPMailer pour envoyer l'email
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Utilise ton serveur SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'mail.jorani.omct@gmail.com';  // Remplacez par votre adresse Gmail
			$mail->Password = 'cdubxwvgxipizdje';  // Utiliser une variable d'environnement pour sécuriser le mot de passe
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
			
			// Encodage UTF-8
			$mail->CharSet = 'UTF-8'; // Définir l'encodage en UTF-8

            // Destinataire et expéditeur
            $mail->setFrom('mail.jorani.omct@gmail.com', 'Récupération du mot de passe');
            $mail->addAddress($email);

            // Contenu de l'email
            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisez votre mot de passe';
            $mail->Body = "
			<html>
			<head>
            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
			</head>
			<body>
			Bonjour,<br><br>Pour r&eacute;initialiser votre mot de passe, cliquez sur le lien suivant :<br><a href='$reset_link'>$reset_link</a><br><br>Ce lien expirera dans une heure.
			<br>
			<br>
			<br>
			<p><font color='red'>*** Ceci est un message g&eacute;n&eacute;r&eacute; automatiquement, veuillez ne pas r&eacute;pondre &agrave; ce message ***</font></p>
			</body>
			</html>";

            // Envoi de l'email
            if ($mail->send()) {
                header("location: index.php?linkresetok=1");
				exit();
            } else {
                header("location: reset_request.php?pwderror=1");
				exit();
            }
        } else {
            header("location: reset_request.php?nomail=1");
            exit();
        }
    } catch (PDOException $e) {
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
    } catch (Exception $e) {
        echo "Erreur lors de l'envoi de l'email : " . $e->getMessage();
    }
} else {
    echo "Veuillez entrer votre adresse email.";
}
?>

