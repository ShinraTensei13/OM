<?php

		if(isset($_GET['errorcnx'])){
			echo'<p id="error"><b>Login ou mot de passe incorrect.<br>Veuillez réessayer plus tard ou <br><a href="reset_request.php" style="color:rgb(0,0,0);">Réinitialier vote mot de passe</a></b></p>';
										}
		else 
		if(isset($_GET['successcnx'])){
			echo'<p id="success"><b>Vous êtes maintenant connecté à votre espace des ordres des missions.</b></p>';
										}
		else
		if(isset($_GET['erroraddom'])){
			echo'<p id="error"><b>Une erreur s\'est produite.<br>Veuillez réessayer plus tard ou contacter l\'administrateur du site.</b></p>';
										}
		else 
		if(isset($_GET['successaddom'])){
			echo'<p id="success"><marquee scrollamount="10" scrolldelay="1" loop="1"><b>Votre demande a été envoyée avec succès.</b></marquee></p>';
										}
		else
		if(isset($_GET['erroraacceptom'])){
			echo'<p id="error"><b>Une erreur s\'est produite.<br>Veuillez réessayer plus tard ou contacter l\'administrateur du site.</b></p>';
										}
		else 
		if(isset($_GET['successacceptom'])){
					echo'<p id="success"><b>L\'ordre de mission a été accepté avec succès.<br> Un email a été envoyé vers le coordinateur.</b></p>';
											}
		else 
		if(isset($_GET['updateomsuccess'])){
					echo'<p id="success"><b>L\'ordre de mission a été modifié avec succès.<br> Un email de notification a été envoyé vers le responsable.</b></p>';
											}
		else
		if(isset($_GET['errordeleteom'])){
			echo'<p id="error"><b>Une erreur s\'est produite.<br>Veuillez réessayer plus tard.</b></p>';
										}
		else 
		if(isset($_GET['successdeleteom'])){
					echo'<p id="success"><b>La suppression de l\'ordre de mission a été effectuée avec succès.</b></p>';
											}
		else
		if(isset($_GET['linkresetdwn'])){
			echo'<p id="error"><b>Une erreur s\'est produite. Veuillez réessayer plus tard.</b></p>';
										}
		else 
		if(isset($_GET['linkresetok'])){
					echo'<p id="success"><b>La demande de réinitialisation a été envoyé</b></p>';
											}
		else
		if(isset($_GET['nomail'])){
					echo'<p id="error"><b>Aucun compte trouvé avec cet e-mail.</b></p>';
		}
		else
		if(isset($_GET['restpwdok'])){
					echo '<p id="success"><b>Mot de passe réinitialisé avec succès.</b></p>';
		}
		else
		if(isset($_GET['missinfo'])){
					echo "<p id='error'><b>Informations manquantes.</b></p>";
		}
		else
		if(isset($_GET['pwdrest'])){
					echo "<p id='success'><b>Mot de passe réinitialié avec succès</b></p>";
		}
		else
		if(isset($_GET['pwderror'])){
					echo "<p id='error'><b>Lien de réinitialisation invalide ou expiré.</b></p>";
		}
		else
		if(isset($_GET['redirecterror'])){
					echo "<p id='error'><b>Veuillez vous connecter pour accéder à cette page</b></p>";
		}
		else
		if(isset($_GET['omdemsent'])){
					echo "<p id='success'><b>Votre demande a été envoyé avec succès</b></p>";
		}
		else
		if(isset($_GET['logoutsuccess'])){
					echo "<p id='success'><b>Vous êtes déconnecté avec succès</b></p>";
		}
		else
		if(isset($_GET['invaliddates'])){
					echo "<p id='error'><b>Veuillez s'assurer que la date du départ est inférieure ou égale à la date du retour</b></p>";
		}
		else
		if(isset($_GET['adminlog'])){
					echo "<p id='error'><b>Vous devez avoir un accès Administrateur pour consulter cette page</b></p>";
		}
		else
		if(isset($_GET['authcaisse'])){
					echo "<p id='error'><b>Vous n'êtes pas autorisé à accéder à cette page.</b></p>";
		}
		else 
		if(isset($_GET['dexok'])){
					echo'<p id="success"><b>Vous avez été déconnecté(e) avec succès</b></p>';
		}
		else
		if(isset($_GET['accessom'])){
					echo "<p id='error'><b>Vous ne pouvez pas accèder à cet ordre de mission ou il n'existe pas.</b></p>";
		}
		else
		if(isset($_GET['omapprouved'])){
					echo "<p id='error'><b>Vous ne pouvez pas modifier un ordre de mission déjà approuvé</b></p>";
		}
		else
		if(isset($_GET['omperso'])){
					echo "<p id='error'><b>Vous ne pouvez pas modifier les ordres missions de vos collègues</b></p>";
		}
		else
		if(isset($_GET['accountstatut'])){
					echo "<p id='success'><b>Le statut du compte a été changé avec succès</b></p>";
		}
		else
		if(isset($_GET['deleteuser'])){
					echo "<p id='error'><b>Une erreur est survenue lors de la désactivation du compte.<br> Veuillez réessayer plus tard.</b></p>";
		}
		else
		if(isset($_GET['inactif'])){
					echo "<p id='error'><b>Votre compte a été désactivé.<br> Veuillez contacter l'administrateur.</b></p>";
		}
		else
		if(isset($_GET['blocked'])){
					echo "<p id='error'><b>Pour des raisons de sécurité votre accès a été bloqué.<br> Veuillez réessayer après 15 minutes.</b></p>";
		}
		else
		if(isset($_GET['session_expired'])){
					echo "<p id='error'><b>votre session a expiré veuillez vous reconnecter</b></p>";
		}
		else
		if (isset($_GET['successpwd'])) {
                    echo '<p id="success"><b>Utilisateur modifié avec succès.</b></p>';
        }
		else
		if (isset($_GET['InvalidID'])) {
                    echo "<p id='error'><b>Auccun enregistrement n'a été identifié avec cet ID.</b></p>";
        }
		else
		if (isset($_GET['updtrerror'])) {
                    echo "<p id='error'><b>Une erreur est survenue lors de la mise à jour des données.</b></p>";
        }
		else
		if (isset($_GET['updtrsuccess'])) {
                    echo "<p id='success'><b>La mise à jour des données a été effectuée avec succès.</b></p>";
        }
		else
		if (isset($_GET['adddepartok'])) {
                    echo "<p id='success'><b>Le département a été ajouté avec succès.</b></p>";
        }
		else
		if (isset($_GET['departexist'])) {
                    echo "<p id='error'><b>Ce département existe déjà. Veuillez vérifier de nouveau.</b></p>";
        }
		else
		if (isset($_GET['successdeletedepart'])) {
                    echo "<p id='success'><b>Le département a été supprimé avec succès.</b></p>";
        }
		else
		if (isset($_GET['errordeletedepart'])) {
                    echo "<p id='error'><b>Une erreur est survenue lors de la supression du département.</b></p>";
        }	
		else
		if (isset($_GET['existdeletedepart'])) {
                    echo "<p id='error'><b>Ce département n'existe pas ou il a été supprimé.</b></p>";
        }
?>

