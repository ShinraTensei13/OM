<?php
echo '<title>Réinitialisation du mot de passe</title>';
include 'include/head.php';
?>
<body class="noPrint">
    <header>
        <h1><b>Ordre de mission</b></h1>
    </header>

    <div class="main-content">
        <div class="login-container">


	<form action='send_reset_email.php' method='POST'>
	<h2>Bienvenue</h2>
	    <?php 
			include ('include/erreur.php');
		?>
					
						<b><p id='info'>Un email avec un lien de réinitialisation de mot de passe<br> va être envoyer à votre adresse inserrée ci-dessous</p></b><br>
				<div class='textbox'>
					<input type='email' id='email' name='email' required placeholder='Entrez votre email' />
				</div>
		
		<br>
		<button class='btn' type='submit'b>Réinitialiser le mot de passe</b></button><br>
		<p align='center'><a href='index.php'>Retour à la page d'accueil</a></p>
	</form>
</div>
</div>
<?php
include 'include/footer.php';
?>

</body>
</html>
