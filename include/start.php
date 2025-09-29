<?php
session_start();

if(isset($_SESSION['connect'])){
	header('location: accueil.php');
	exit();
}

require('config.php');

// CONNEXION
if(!empty($_POST['email']) && !empty($_POST['password'])){

	// VARIABLES
	$email 		= $_POST['email'];
	$password 	= $_POST['password'];
	$actif  	= $_POST['actif'];
	$errorcnx		= 1;

	// CRYPTER LE PASSWORD
	$password = "aq1".sha1($password."1254")."25";

	echo $password;

	$req = $link->prepare('SELECT * FROM usersom WHERE email = ? AND actif = "Actif"');
	$req->execute(array($email));

	while($user = $req->fetch()){

		if($password == $user['password']){
			$errorcnx = 0;
			$_SESSION['connect'] = 1;
			$_SESSION['nom']	 = $user['nom'];
			$_SESSION['poste']	 = $user['poste'];
			$_SESSION['depart']	 = $user['depart'];
			$_SESSION['referto'] = $user['referto'];
			$_SESSION['type']	 = $user['type'];
			$_SESSION['email']	 = $user['email'];
            $_SESSION['caisse']	 = $user['caisse'];
			$_SESSION['actif']	 = $user['actif'];
			if(isset($_POST['connect'])) {
				setcookie('log', $user['secret'], time() + 5, '/', null, false, true);
			}

			header('location: accueil.php?successlog=1');
			exit();
		}

	}

	if($errorcnx == 1){
		header('location: index.php?errorlog=1');
		exit();
	}

}

?>