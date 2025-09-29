
<header class="noPrint">
<h1><b><a href='profile.php' style="text-decoration:none"  class="profile-link"><?php echo $_SESSION['nom'] ?></a></b></h1>
		<div class="menu">
				<div class="mt-5 mb-3 d-flex justify-content-between" >
						<?php
						echo '<b><a href="accueil.php">Accueil</a></b>'; 
						echo '<b><a href="create.php">Nouveau</a></b>'; 
						
						if($_SESSION['caisse'] == 'Oui') {
								echo '<b><a href="liste.php">Liste OM des collègues</a></b>';
														}
						else{
							echo '';
						} 
						if($_SESSION['type'] == 'Super user'){
							echo '<b><a href="approve.php">Pour approbation</a></b>';
							echo '<b><a href="users.php">Consulter les utilisateurs</a></b>';
							if($_SESSION['nom'] == 'Administrateur'){
							echo '<b><a href="adminsite.php">Administration du site</a></b>';
							}									}
							elseif($_SESSION['type'] == 'Administrateur') {
							echo '<b><a href="approve.php">Pour approbation</a></b>';
																			}
						else{
							echo '';
						}
						
						echo '<b><a href="dix.php">Déconnexion</b></a></b>';
						?>
				</div>
			</div>
</header>
