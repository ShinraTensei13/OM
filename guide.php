<?php
echo '<title>Guide d’utilisation – Ordres de Mission</title>';
include 'include/head.php';
?>
  <style>
	.background {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-image: url('img/OMCT.png'); /* Remplace par ton image */
      background-size: cover;
      background-position: center;
      filter: blur(8px);
      opacity: 0.1;
      z-index: -1;
    }
  </style>
  <header><h1><b>Guide d’utilisation de l’application des Ordres de mission</b></h1></header>  
<body>

	 <div class="background"></div>

  <div class="section">
    <h2>I. Page de connexion</h2>
    <ol>
      <li>Veuillez saisir votre adresse e-mail (ex. : exemple@omct.org)</li>
      <li>Veuillez saisir votre mot de passe</li>
      <li>Si cette case est cochée, votre session restera active pendant 30 jours</li>
      <li>En cas d’oubli de mot de passe, vous pouvez le récupérer en cliquant sur <a href="https://jorani.omct-tunisie.org/ordre/reset_request.php"><b>ce lien</b><a></li>
    </ol>
  </div>

  <div class="section">
    <h2>II. Réinitialisation du mot de passe</h2>
    <ol>
      <li>Cliquez sur le lien « Mot de passe oublié » situé sur la page d’accueil (index).</li>
      <li>Vous serez redirigé vers la page de demande de réinitialisation de mot de passe.</li>
      <li>Saisissez votre adresse e-mail, puis cliquez sur le bouton « Réinitialiser le mot de passe ».</li>
	  <img src="img/01.png" alt="Reset password" style="width:600px;">
      <li>Vous recevrez un e-mail contenant un lien pour réinitialiser votre mot de passe.</li>
      <li>Cliquez sur le lien pour être redirigé vers la page de réinitialisation de votre mot de passe.</li>
      <li>Comme illustré dans l’image ci-dessous, vous devez respecter toutes les exigences de sécurité pour créer un nouveau mot de passe.</li>
	  <img src="img/02.png" alt="Reset password" style="width:600px;">
      <li>Une fois le mot de passe réinitialisé, vous serez automatiquement redirigé vers la page d’authentification afin de vous connecter à l’application.</li>
    </ol>
  </div>

  <div class="section">
    <h2>III. Page d’accueil</h2>
    <ol>
      <li>La page d’accueil est structurée en deux sections principales :
        <ul>
          <li>Liste des ordres de mission en cours de traitement (section 1)</li>
          <li>Liste des ordres de mission approuvés (section 2)</li>
        </ul>
		<img src="img/03.png" alt="Reset password" style="width:600px;">
      </li>
      <li>Dans la section des ordres en cours de traitement, vous avez la possibilité de consulter, modifier ou supprimer votre ordre de mission, comme illustré dans la partie 3 de l’image.</li>
      <li>Une fois votre ordre de mission approuvé, il apparaît uniquement dans la liste des ordres de mission approuvés, où vous pouvez le consulter, comme indiqué dans la partie 4 de l’image.</li>
    </ol>
  </div>

  <div class="section">
    <h2>IV. Nouvelle demande d’OM</h2>
    <ol>
      <li><strong>Champs obligatoires :</strong> Tous les champs du formulaire doivent obligatoirement être remplis.</li>
      <li><strong>Période de mission :</strong> Un champ libre est prévu pour indiquer la période effective de la mission, qui peut différer de la date de départ et de retour.  
        <br><em>Exemple : La période de la mission est du 02/01 au 05/01. Toutefois, le départ a lieu le 01/01 dans l’après-midi et le retour le 06/01 dans la matinée.</em></li>
      <li><strong>Répartition des frais de Perdiem :</strong><br>
        Petit déjeuner : 15 TND&nbsp;&nbsp;&nbsp;Déjeuner : 25 TND&nbsp;&nbsp;&nbsp;Dîner : 30 TND</li>
      <li><strong>Hébergement et petit déjeuner :</strong> Si vous bénéficiez d’une réservation d’hôtel, les frais de petit déjeuner ne sont pas pris en compte pendant la mission, sauf pour le premier jour, considéré comme jour de déplacement.</li>
      <li><strong>Week-ends :</strong>
        <ul>
          <li>L’application ne prend pas en compte les week-ends dans le calcul des perdiems.</li>
          <li>Si vous participez à une formation durant un week-end et que vous bénéficiez d’un perdiem de la part de l’OMCT, sélectionnez <strong>Oui</strong> à la question « Seriez-vous en mission pendant le week-end ? ».</li>
          <li>Vous pourrez ensuite sélectionner les frais de Perdiem correspondant aux jours concernés.</li>
        </ul>
      </li>
      <li><strong>Conditions d’affichage du tableau de Perdiem :</strong>
        <ul>
          <li>a. Si vous avez sélectionné <strong>Non</strong> à « Veuillez confirmer si vous bénéficiez du Perdiem » :<br>→ Montant du Perdiem = 0</li>
          <li>b. Si vous avez sélectionné <strong>Oui</strong> :
            <ul>
              <li>Si vous avez également sélectionné <strong>Oui</strong> à « Avez-vous une réservation d’hôtel ? » :<br>→ Vous ne pouvez pas sélectionner les petits déjeuners, sauf celui du premier jour.</li>
              <li>Si vous avez sélectionné <strong>Non</strong> à la réservation d’hôtel :<br>→ Vous pouvez sélectionner toutes les options dans le tableau du Perdiem.</li>
            </ul>
          </li>
          <li>c. Si vous êtes en mission durant le week-end :
            <ul>
              <li>Sélectionnez <strong>Oui</strong> à la question « Seriez-vous en mission pendant le week-end ? »</li>
              <li class="note">NB : Les mêmes conditions que celles décrites en (b) s’appliquent.</li>
            </ul>
          </li>
        </ul>
      </li>
      <li><strong>Frais de déplacement :</strong> Les frais de déplacement sont remboursés selon le mode de transport sélectionné dans le champ « Moyen de déplacement ».
        <br><span class="note">NB : La grille de remboursement est établie par le département financier. Le calcul est automatique et basé sur cette grille.</span>
        <ul>
          <li>a. <strong>Transport en commun / Véhicule personnel :</strong><br>→ Le montant est calculé selon la ville de départ et la ville de mission.</li>
          <li>b. <strong>Véhicule de location :</strong><br>→ Vous devez saisir manuellement les frais de carburant dans le champ « Frais de carburant, taxi ou autres », selon la facture.</li>
          <li>c. <strong>Aller-retour :</strong>
            <ul>
              <li>Si vous sélectionnez <strong>Oui</strong> à « Remboursement des frais de déplacement aller-retour ? » → vous bénéficiez du remboursement total.</li>
              <li>Si vous sélectionnez <strong>Non</strong> → vous ne recevez que la moitié des frais.</li>
              <li><em>Exemple : Kef–Tunis = 90 TND. Si vous cochez « Non », le remboursement sera de 45 TND.</em></li>
            </ul>
          </li>
        </ul>
      </li>
      <li><strong>Soumission de la demande :</strong> En cliquant sur <strong>Enregistrer</strong>, une demande d’approbation est automatiquement envoyée par e-mail à votre supérieur hiérarchique, avec les détails de votre ordre de mission.</li>
      <li><strong>Notification d’approbation :</strong> Si votre demande est acceptée, vous recevrez une notification par e-mail.</li>
    </ol>
    <p class="note"><strong>NB :</strong> Ces remarques s’appliquent également lorsque vous modifiez un ordre de mission déjà soumis mais non encore validé.</p>
  </div>

<div class="mt-5 mb-3 d-flex justify-content-between">
<a href="index.php"><button class="btn btn-primary" style="color:white">Retour</button></a>
</div>
<br>
</body>

</html>
