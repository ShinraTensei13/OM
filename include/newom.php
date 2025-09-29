<?php
require_once "config.php";
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if ($_SERVER["REQUEST_METHOD"] == "POST") {
$nom     = $_SESSION['nom'];
$poste   = $_SESSION['poste'];
$type    = $_SESSION['type'];
$referto = $_SESSION['referto'];
    // Initialisation des variables et validation des entrées
    $errors = [];
    $fields = ['obj', 'datem', 'villem', 'villed', 'dated', 'dater', 'mtr', 'hotel', 'peracc', 'formation', 'remboursement'];
    foreach ($fields as $field) {
        $$field = trim($_POST[$field] ?? '');
        if (empty($$field)) {
            $errors[$field] = "Veuillez remplir le champ $field.";
        }
    }
    $autre = trim($_POST['autre'] ?? '');
    $partiel = trim($_POST['partiel'] ?? '');

    // Validation des dates et calcul du nombre de jours
    $start_date = strtotime($dated);
    $end_date = strtotime($dater);

    if ($end_date < $start_date) {
        header("location: create.php?invaliddates=1");
        exit();
    }

    $diff_in_days = floor(($end_date - $start_date) / (60 * 60 * 24));
    if ($diff_in_days < 0) {
        header("location: create.php?invaliddates=1");
        exit();
    } elseif ($diff_in_days === 0) {
        $diff_in_days = 1; // Si les deux dates sont identiques, c'est une journée
    } else {
        $diff_in_days += 1; // Ajout d'un jour car les dates incluent la première et la dernière journée
    }

    if (empty($errors)) {
        try {
            // Calcul des frais et des perdiems
            $perdiem = (float)$_POST['perdiem'];
            $frais = calculateFrais($mtr, $remboursement, $villem, $villed, $db);
            $total = $perdiem + $frais + (float)$autre;

            // Générer un ID unique pour l'OM
            $idom = generateIdOM($db);
			$id = generateId($db);

            // Préparation et exécution de la requête SQL
            $sql = "INSERT INTO ordred (id, idom, dateom, nom, poste, type, referto, obj, datem, villem, villed, dated, dater, mtr, remboursement, frais, hotel, peracc, perdiem, formation, autre, total, statut)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'En attente')";
            $stmt = $db->prepare($sql);

            $stmt->bindValue(1, $id, PDO::PARAM_STR);
			$stmt->bindValue(2, $idom, PDO::PARAM_STR);
            $stmt->bindValue(3, date('Y-m-d'), PDO::PARAM_STR);
            $stmt->bindValue(4, $_SESSION['nom'], PDO::PARAM_STR);
            $stmt->bindValue(5, $_SESSION['poste'], PDO::PARAM_STR);
            $stmt->bindValue(6, $_SESSION['type'], PDO::PARAM_STR);
            $stmt->bindValue(7, $_SESSION['referto'], PDO::PARAM_STR);
            $stmt->bindValue(8, $obj, PDO::PARAM_STR);
            $stmt->bindValue(9, $datem, PDO::PARAM_STR);
            $stmt->bindValue(10, $villem, PDO::PARAM_STR);
            $stmt->bindValue(11, $villed, PDO::PARAM_STR);
            $stmt->bindValue(12, $dated, PDO::PARAM_STR);
            $stmt->bindValue(13, $dater, PDO::PARAM_STR);
            $stmt->bindValue(14, $mtr, PDO::PARAM_STR);
			$stmt->bindValue(15, $remboursement, PDO::PARAM_STR);
            $stmt->bindValue(16, $frais, PDO::PARAM_STR);
            $stmt->bindValue(17, $hotel, PDO::PARAM_STR);
            $stmt->bindValue(18, $peracc, PDO::PARAM_STR);
            $stmt->bindValue(19, $perdiem, PDO::PARAM_STR);
			$stmt->bindValue(20, $formation, PDO::PARAM_STR);
            $stmt->bindValue(21, $autre, PDO::PARAM_STR);
            $stmt->bindValue(22, $total, PDO::PARAM_STR);


            if ($stmt->execute()) {
                // Envoi de l'email
                sendNotificationEmail($idom, $_SESSION['nom'], $_SESSION['referto'], $obj, $diff_in_days, $perdiem, $frais, $autre, $total);
                header("location: accueil.php?successaddom=1");
                exit();
            } else {
                header("location: accueil.php?erroraddom=1");
                exit();
            }
        } catch (PDOException $e) {
            echo "Erreur lors de l'insertion : " . $e->getMessage();
            exit();
        }
    }
}

// Fonction de calcul des frais
function calculateFrais($mtr, $remboursement, $villem, $villed, $db)
{
    $frais = 0;
if($remboursement === 'Oui'){
    if ($mtr === 'Transport en commun') {
        $stmt = $db->prepare("SELECT fraistr FROM trpub WHERE (villem = ? AND villed = ?) OR (villed = ? AND villem = ?)");
        $stmt->bindValue(1, $villem, PDO::PARAM_STR);
        $stmt->bindValue(2, $villed, PDO::PARAM_STR);
        $stmt->bindValue(3, $villem, PDO::PARAM_STR);
        $stmt->bindValue(4, $villed, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $frais = $result['fraistr'] ?? 0;
    } elseif ($mtr === 'Véhicule personnel') {
        $stmt = $db->prepare("SELECT fxcar FROM trpub WHERE (villem = ? AND villed = ?) OR (villed = ? AND villem = ?)");
        $stmt->bindValue(1, $villem, PDO::PARAM_STR);
        $stmt->bindValue(2, $villed, PDO::PARAM_STR);
        $stmt->bindValue(3, $villem, PDO::PARAM_STR);
        $stmt->bindValue(4, $villed, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $frais = $result['fxcar'] ?? 0;
    }
} elseif($remboursement === 'Non') { 
	if ($mtr === 'Transport en commun') {
        $stmt = $db->prepare("SELECT fraistr FROM trpub WHERE (villem = ? AND villed = ?) OR (villed = ? AND villem = ?)");
        $stmt->bindValue(1, $villem, PDO::PARAM_STR);
        $stmt->bindValue(2, $villed, PDO::PARAM_STR);
        $stmt->bindValue(3, $villem, PDO::PARAM_STR);
        $stmt->bindValue(4, $villed, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $frais = ($result['fraistr']/2) ?? 0;
    } elseif ($mtr === 'Véhicule personnel') {
        $stmt = $db->prepare("SELECT fxcar FROM trpub WHERE (villem = ? AND villed = ?) OR (villed = ? AND villem = ?)");
        $stmt->bindValue(1, $villem, PDO::PARAM_STR);
        $stmt->bindValue(2, $villed, PDO::PARAM_STR);
        $stmt->bindValue(3, $villem, PDO::PARAM_STR);
        $stmt->bindValue(4, $villed, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $frais = ($result['fxcar']/2) ?? 0;
} }
    return $frais;
}

// Générer un ID unique pour l'OM
function generateIdOM($db)
{
    $stmt = $db->query("SELECT MAX(id) as max_id FROM ordred");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $id = $result['max_id'] + 1;
    return 'OM-' . date('y') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
}

function generateId($db)
{
    $stmt = $db->query("SELECT MAX(id) as max_id FROM ordred");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $id = $result['max_id'] + 1;
    return str_pad($id, 4, '0', STR_PAD_LEFT);
}

// Fonction d'envoi de l'email
function sendNotificationEmail($idom, $nom, $referto, $obj, $days, $perdiem, $frais, $autre, $total)
{
    $mail = new PHPMailer(true);

    try {
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mail.jorani.omct@gmail.com';
        $mail->Password = 'cdubxwvgxipizdje';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Encodage UTF-8
        $mail->CharSet = 'UTF-8'; // Définir l'encodage en UTF-8

        // Expéditeur et destinataire
        $mail->setFrom('mail.jorani.omct@gmail.com', $idom);
        $mail->addAddress(getRefertoEmail($referto), 'Administrateur');

        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = "Demande OM $nom";
        $mail->Body = "
        <html>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
        </head>
        <body>
            <br>
            <h3>Vous avez un ordre de mission en attente d'approbation</h3>
            <p>Le coordinateur&nbsp;<strong>$nom</strong>&nbsp;vient de demander l'approbation de son ordre de mission<br>
            Veuillez <a href='https://jorani.omct-tunisie.org/ordre/'><button id='btnOutlook'><b>Cliquez ici</b></button></a> 
            pour valider sa demande sur la plateforme</p>
            
            <p><b>Nom du coordinateur : </b>$nom</p>
            <p><b>Objectif de la mission : </b>$obj</p>
            <p><b>Durée de la mission : </b>$days jour(s)</p>
            <p><b>Frais du Perdiem : </b>$perdiem TnD</p>
            <p><b>Frais de déplacement : </b>$frais TnD</p>
            <p><b>Frais de carburant ou autres : </b>$autre TnD</p>
            <p><b>Total des frais : </b>$total TnD</p>
            <br>
            <br>
            <br>
            <p><font color='red'>*** Ceci est un message généré automatiquement, veuillez ne pas répondre à ce message ***</font></p>
        </body>
        </html>";

        // Envoyer l'email
        $mail->send();
    } catch (Exception $e) {
        error_log("Erreur email : {$mail->ErrorInfo}");
    }
}

// Récupérer l'email du référent
function getRefertoEmail($referto)
{
    global $db;
    $stmt = $db->prepare("SELECT email FROM usersom WHERE nom = ?");
    $stmt->execute([$referto]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['email'] ?? '';
}
?>




<div class="wrapper noPrint"> 
	<b><p  align="right"><?php $currentDateTime = date('d-m-Y'); echo $currentDateTime;?></p></b>
<h2 class="mt-5">Remplir le formulaire pour enregistrer l'ordre de mission</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

	
					<tr class="textbox">
                    <td><b>Objectif de la mission</b><input  type='text' name="obj" class="form-control" required></td>
					</tr>
	<br>
					<tr class="textbox">
                            <td><b>Date de la mission</b><input type="text" name="datem" class="form-control" required></td>
					</tr>
	<br>
					<tr class="textbox">
							<td><b>Ville de la mission</b><select Type="list" class="form-control" name="villem" id="villem-select"  required>
								<option value="">Veuillez sélectionner la ville de la mission</option>
								<?php
								// Récupération des villes distinctes depuis la table
								$sql_villes = "SELECT DISTINCT villem FROM trpub ORDER BY villem ASC";
								if ($result_villes = $link->query($sql_villes)) {
								while ($row_ville = $result_villes->fetch_assoc()) {
								$selected = ($row_ville['villem'] == $villem) ? "selected" : "";
								echo '<option value="'. htmlspecialchars($row_ville['villem']) .'" '. $selected .'>'. htmlspecialchars($row_ville['villem']) .'</option>';
									}
								}
								?>
							</select></td>
					</tr>	
	<br>				
					<tr class="textbox">
							<td><b>Ville de départ</b><select Type="list" class="form-control" name="villed" id="villed-select"  required>
								<option value="">Veuillez sélectionner la ville de la mission</option>
								<?php
								// Récupération des villes distinctes depuis la table
								$sql_villes = "SELECT DISTINCT villed FROM trpub ORDER BY villed ASC";
								if ($result_villes = $link->query($sql_villes)) {
								while ($row_ville = $result_villes->fetch_assoc()) {
								$selected = ($row_ville['villed'] == $villed) ? "selected" : "";
								echo '<option value="'. htmlspecialchars($row_ville['villed']) .'" '. $selected .'>'. htmlspecialchars($row_ville['villed']) .'</option>';
									}
								}
								?>
							</select></td>
					</tr>
	<br>				
					<tr class="textbox">
                            <td><b>Date de départ</b><?php include "erreur.php";?> <input type="date" id="dated" name="dated" class="form-control" required></td>
					</tr>
	<br>
					<tr class="textbox">
                             <td><b>Date de retour</b><input type="date" id="dater" name="dater" class="form-control" placeholder="Date de retour" required ></td>
					</tr>
	<br>				
					<tr class="textbox">
                            <td><b>Veuillez valider votre réservation d'hôtel</b><select Type="list" class="form-control" name="hotel" id="hotel" required >
								<option value="Non">Non</option>
								<option value="Oui">Oui</option>
							</select></td>
					</tr>
	<br>
					<tr class="textbox">
                             <td><b>Veuillez confirmer si vous bénéficiez du Perdiem </b><select Type="list" class="form-control" name="peracc" id="peracc" required >
								<option value="Oui">Oui</option>
								<option value="Non">Non</option>
							</select></td>
					</tr>
	<br>				
					<tr class="textbox">
                             <td><b>Seriez-vous en mission pendant le week-end ?</b><select Type="list" class="form-control" name="formation" id="formation" required >
								<option value="Non">Non</option>
								<option value="Oui">Oui</option>
							</select></td>
					</tr>
			
					<tr>
					<td>
					<h2 id="days"></h3>

					<!-- Option pour sélectionner tout -->
					<div id="select-all-container" style="display: None;">
						<label for="select-all">Sélectionner tout :</label>
						<input type="checkbox" id="select-all" onclick="toggleAllCheckboxes()">
					</div>
					<table id="choices-table" class="wrapper">
						<!-- Les colonnes seront ajoutées dynamiquement -->
					</table>

					<b><h2 id="total-frais"></h3></b>

    <script>
        // Prix des options
        const prixPetitDejeuner = 15;
        const prixDejeuner = 25;
        const prixDiner = 30;

        // Fonction pour calculer la différence en jours entre deux dates, excluant les samedis et dimanches
        function calculateDaysBetweenDates(dated, dater, includeWeekends) {
            const startDate = new Date(dated);
            const endDate = new Date(dater);
            let totalDays = 0;

            if (startDate.getTime() === endDate.getTime()) {
                return 1;
            }

            for (let currentDate = new Date(startDate); currentDate <= endDate; currentDate.setDate(currentDate.getDate() + 1)) {
                if (includeWeekends || isWeekday(currentDate)) {
                    totalDays++;
                }
            }

            return totalDays;
        }


        // Fonction pour vérifier si un jour est un jour de la semaine (lundi à vendredi)
        function isWeekday(date) {
            const day = date.getDay(); // 0: Dimanche, 6: Samedi
            return day !== 0 && day !== 6; // Retourne false si c'est un samedi ou un dimanche
        }

        // Fonction pour afficher les peracc en fonction du nombre de jours
        function displayChoices() {
            const dated = document.getElementById('dated').value;
            const dater = document.getElementById('dater').value;
            const peracc = document.getElementById('peracc').value;
            const test = document.getElementById('hotel').value;
            const includeWeekends = document.getElementById('formation').value === 'Oui';

            if (peracc === 'Oui' && dated && dater) {
                const totalDays = calculateDaysBetweenDates(dated, dater, includeWeekends);

                if (totalDays < 0) {
                    document.getElementById('days').innerHTML = "<span class='error'>La date de début ne peut pas être postérieure à la date de fin.</span>";
                    document.getElementById('choices-table').style.display = 'None';
                } else {
                    document.getElementById('days').innerHTML = `Nombre de jours : ${totalDays} jour(s)`;
                    generateTable(dated, dater, totalDays, test, includeWeekends);
                    document.getElementById('choices-table').style.display = 'table';
                    document.getElementById('select-all-container').style.display = 'block';
                }

                if (totalDays === 1) {
                    document.getElementById('test-container').style.display = 'None';
                } else {
                    document.getElementById('test-container').style.display = 'block';
                }
            } else if (peracc === 'Non') {
                document.getElementById('total-frais').innerHTML = "0";
                document.getElementById('choices-table').style.display = 'None';
                document.getElementById('select-all-container').style.display = 'None';
                document.getElementById('test-container').style.display = 'None';
            } else {
                document.getElementById('choices-table').style.display = 'None';
                document.getElementById('select-all-container').style.display = 'None';
            }
        }

        // Fonction pour générer le tableau avec les peracc
        function generateTable(dated, dater, totalDays, test, includeWeekends) {
            const table = document.getElementById('choices-table');
            table.innerHTML = "<thead><tr><th>Jour</th><th>Petit déjeuner</th><th>Déjeuner</th><th>Dîner</th></tr></thead>";

            const startDate = new Date(dated);
            const endDate = new Date(dater);
            let dayCount = 0;
            const rows = [];

            for (let currentDate = new Date(startDate); currentDate <= endDate; currentDate.setDate(currentDate.getDate() + 1)) {
                if (includeWeekends || isWeekday(currentDate)) {
                    dayCount++;

                    const formattedDate = currentDate.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                    const row = document.createElement('tr');

                    row.insertCell(0).textContent = formattedDate;

                    const option1Cell = row.insertCell(1);
                    if (dayCount === 1 || test === 'Non') {
                        const option1Checkbox = document.createElement('input');
                        option1Checkbox.type = 'checkbox';
                        option1Checkbox.name = `petit_dejeuner_${dayCount}`;
                        option1Checkbox.addEventListener('change', calculateFrais);
                        option1Cell.appendChild(option1Checkbox);
                    } else {
                        option1Cell.textContent = 'Non applicable';
                    }

                    const option2Cell = row.insertCell(2);
                    const option2Checkbox = document.createElement('input');
                    option2Checkbox.type = 'checkbox';
                    option2Checkbox.name = `dejeuner_${dayCount}`;
                    option2Checkbox.addEventListener('change', calculateFrais);
                    option2Cell.appendChild(option2Checkbox);

                    const option3Cell = row.insertCell(3);
                    const option3Checkbox = document.createElement('input');
                    option3Checkbox.type = 'checkbox';
                    option3Checkbox.name = `diner_${dayCount}`;
                    option3Checkbox.addEventListener('change', calculateFrais);
                    option3Cell.appendChild(option3Checkbox);

                    rows.push(row);
                }
            }

            rows.forEach(row => table.appendChild(row));
            calculateFrais();
        }

// Modifier la fonction calculateFrais
function calculateFrais() {
    let totalFrais = 0;
    
    document.querySelectorAll('#choices-table input[type="checkbox"]:checked').forEach(checkbox => {
        const parentCell = checkbox.closest('td');
        if (!parentCell) return;

        switch (parentCell.cellIndex) {
            case 1: totalFrais += prixPetitDejeuner; break;
            case 2: totalFrais += prixDejeuner; break;
            case 3: totalFrais += prixDiner; break;
        }
    });

    document.getElementById('perdiem-input').value = totalFrais;
    document.getElementById('total-frais').innerHTML = `Frais du Perdiem : ${totalFrais} TnD`;
}

        // Fonction pour sélectionner ou désélectionner toutes les cases
        function toggleAllCheckboxes() {
            const selectAllCheckbox = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('#choices-table input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            calculateFrais();
        }

        // Event listener pour les changements des dates et options
        document.getElementById('dated').addEventListener('change', displayChoices);
        document.getElementById('dater').addEventListener('change', displayChoices);
        document.getElementById('peracc').addEventListener('change', displayChoices);
        document.getElementById('hotel').addEventListener('change', displayChoices);
		document.getElementById('formation').addEventListener('change', displayChoices);
    </script>
	<input type="hidden" name="perdiem" id="perdiem-input">
	</td></tr>
	<br>
					<tr class="textbox">
					<td><b>Moyen de déplacement</b>
                             <select Type="list" class="form-control" name="mtr" id="mtr-select" required >
							    <option value="Transport en commun">Transport en commun</option>
								<option value="Véhicule personnel">Véhicule personnel</option>
								<option value="Véhicule de location">Véhicule de location</option>
								<option value="Taxi">Taxi</option>
								<option value="Non">Non</option>
							</select></td>
					</tr>
	<br>				
					<tr class="textbox remboursement-section" style="display: None;">
					<td><b>Remboursement des frais de déplacement pour Aller-retour</b>
                             <select Type="list" class="form-control" name="remboursement" id="remboursement-select" required >
								<option value="Oui">Oui</option>
								<option value="Non">Non</option>
							</select></td>
					</tr>
	<br>
<script>
  document.getElementById('mtr-select').addEventListener('change', function() {
    const selectedValue = this.value;
    const remboursementSection = document.querySelector('.remboursement-section');
    
    if (selectedValue === 'Véhicule personnel' || selectedValue === 'Transport en commun') {
      remboursementSection.style.display = 'table-row';
      // Rendre les radio-boutons obligatoires
      document.querySelectorAll('input[name="remboursement"]').forEach(input => {
        input.required = true;
      });
    } else {
      remboursementSection.style.display = 'None';
      // Désactiver la requirement quand caché
      document.querySelectorAll('input[name="remboursement"]').forEach(input => {
        input.required = false;
      });
    }
  });

  // Déclencher l'événement change au chargement de la page si valeur déjà sélectionnée
  document.getElementById('mtr-select').dispatchEvent(new Event('change'));
</script>
			
					<tr class="textbox">
					<td><b>Frais de carburant ou autres :<h4>Si vous n'avez aucune autre dépenses veuillez mettre [0]</h4></b><input type="number_format" name="autre" class="form-control" required></td>
					</tr>

<table>					
                    <tr class="textbox">
						<td><center><input type="submit" class="btn btn-primary m1-2" value="Enregistrer"></td>
						<td><button type="button" class="btn btn-primary ml-2" onclick="history.back();">Annuler</button></td>

					</tr>
</table>		
</form>
</div>