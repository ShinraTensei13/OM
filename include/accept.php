<?php
require_once "include/config.php";
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception; 
/* Definir les variables */
$statut = "";
$statut_err = "";
$currentDateTime = date('y-m-d');
$day = date('y');


$approve = 'Approuvé';
/* verifier la valeur id dans le post pour la mise à jour */
if(isset($_POST["id"]) && !empty($_POST["id"])){
    /* recuperation du champ caché */
    $id = $_POST["id"];
    /* Validate approve */
	$nom     = $_SESSION['nom'];
	$poste   = $_SESSION['poste'];
	$type    = $_SESSION['type'];
	$referto = $_SESSION['referto']; 
    /* verifier les erreurs avant modification */
        if($type ='Super user'){
        $sql = "UPDATE ordred SET dateupdate='$currentDateTime', statut='$approve', approvedby='$nom', posteapprove ='$poste' WHERE id=?";
		} elseif($type ='Administrateur') {
		$sql = "UPDATE ordred SET dateupdate='$currentDateTime', statut='$approve', approvedby='$nom', posteapprove ='$poste' WHERE id=? AND referto ='$nom'";	
		}
        if($stmt = mysqli_prepare($link, $sql)){
            
            mysqli_stmt_bind_param($stmt, "s", $param_id);
            
            $param_id = $id;
            
            
            if(mysqli_stmt_execute($stmt)){
                /* enregistremnt modifié, retourne */
				

// Paramètres de connexion à la base de données
$host = 'localhost';
$dbname = 'omcttunijorani';
$username = 'root';
$password = ''; // Assure-toi de ne pas utiliser de mot de passe en clair pour la production

// Récupérer l'id de la session ou de la requête POST/GET
$id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : null);

if (!$id) {
    echo "ID introuvable.";
    exit;
}

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Étape 1 : Récupérer le nom correspondant à $id depuis une table (exemple : table `ordred`)
    $stmt = $pdo->prepare("SELECT nom FROM ordred WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $nom_correspondant = $result['nom'];
    } else {
        echo "Aucun enregistrement trouvé avec cet ID.";
        exit;
    }

    // Étape 2 : Récupérer l'email correspondant au nom depuis la table `userom`
    $stmt = $pdo->prepare("SELECT email FROM usersom WHERE nom = :nom");
    $stmt->bindParam(':nom', $nom_correspondant, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $email_referto = $user['email']; // Email récupéré
    } else {
        echo "Aucun utilisateur trouvé avec ce nom.";
        exit;
    }
} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
    exit;
}

// Créer une instance de PHPMailer
$mail = new PHPMailer(true);

try {
    // Paramètres du serveur SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';  // Serveur SMTP de Gmail
    $mail->SMTPAuth = true;
    $mail->Username = 'mail.jorani.omct@gmail.com';  // Remplacez par votre adresse Gmail
    $mail->Password = 'cdubxwvgxipizdje';  // Utiliser une variable d'environnement pour sécuriser le mot de passe
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

	// Encodage UTF-8
    $mail->CharSet = 'UTF-8'; // Définir l'encodage en UTF-8
		
    // Destinataire et expéditeur
    $mail->setFrom('mail.jorani.omct@gmail.com', 'Ordre de Mission');  // L'email de l'expéditeur
    $mail->addAddress($email_referto);  // Utilisation de l'email récupéré depuis la base de données

    // Contenu de l'email
    $mail->isHTML(true);
    $mail->Subject = "Votre deamnde a été acceptée";
    $mail->Body    = "
	<html>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
        </head>
        <body>
    <h3>Votre ordre de mission a bien été accepté</h3>
    <p><strong>Message :</strong><br>Veuillez cliquer sur <a href='https://jorani.omct-tunisie.org/ordre/'><b>Ce lien</b></a> pour accéder à la plateforme</p>
    <br>
			<br>
			<br>
			<p><font color='red'>*** Ceci est un message généré automatiquement, veuillez ne pas répondre à ce message ***</font></p>
        </body>
        </html>";

    // Envoi de l'email
    $mail->send();
    header("location: accueil.php?successacceptom=1");
                exit();
} catch (Exception $e) {
    echo "Erreur lors de l'envoi de l'email. Erreur : {$mail->ErrorInfo}";
}
                header("location: accueil.php?successacceptom=1");
                exit();
            } else{
                header('location: accueil.php?erroracceptom=1');
				exit();
            }
        }
         
        
        mysqli_stmt_close($stmt);
    
    
    mysqli_close($link);
} else{
    /* si il existe un paramettre id */
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
        
        $id =  trim($_GET["id"]);
        
		 if($type ='Super user'){
        $sql = "SELECT * FROM ordred WHERE id=?";
		 }
		 elseif($type ='Administrateur'){
		 $sql = "SELECT * FROM ordred WHERE id=? AND referto = '$nom'";
		 }

        if($stmt = mysqli_prepare($link, $sql)){
            
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            
            
            $param_id = $id;
            
            
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
    
                if(mysqli_num_rows($result) == 1){
                    /* recupere l'enregistremnt */
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    
                    /* recupere les champs */
                    $statut = $row["statut"];
                } else{
                    
                    header("location: error.php");
                    exit();
                }
                
            } else{
                echo "Oops! une erreur est survenue.";
            }
        }
        
        /* Close statement */
        mysqli_stmt_close($stmt);
        
        /* Close connection */
        mysqli_close($link);
    }  else{
        /* pas de id parametter valid, retourne erreur */
        header("location: error.php");
        exit();
    }
}
?>


        <div class="wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="mt-5">Approbation de l'Ordre de Mission</h2><br>
                        <p>Veuillez cliquer sur <b>Approuver</b> pour accepter l'ordre de mission de <b><?php echo htmlspecialchars($row["nom"]); ?></b>.</p><br>
                        <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                            <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                            <input type="submit" class="btn btn-success" value="Approuver">
                            <input class="btn btn-secondary" onclick="history.back();" value="Annuler">
                        </form>
                    </div>
                </div>
            </div>
        </div>