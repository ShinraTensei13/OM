<?php
require_once "config.php";
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



$nom = $_SESSION['nom'];
$poste = $_SESSION['poste'];
$type = $_SESSION['type'];
$referto = $_SESSION['referto'];

// Initialisation des variables
$errors = [];
$success = false;


// Récupération des données existantes
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);
    try {
        $stmt = $db->prepare("SELECT * FROM ordred WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            header("Location: accueil.php?linkresetdwn=1");
            exit();
        }
    } catch (PDOException $e) {
        die("Erreur de base de données : " . $e->getMessage());
    }
}

// Traitement du formulaire de modification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    // Récupération des données du formulaire
    $id = $_POST["id"];
    $obj = trim($_POST["obj"]);
    $datem = trim($_POST["datem"]);
    $villem = trim($_POST["villem"]);
    $villed = trim($_POST["villed"]);
    $dated = trim($_POST["dated"]);
    $dater = trim($_POST["dater"]);
    $mtr = trim($_POST["mtr"]);
    $hotel = trim($_POST["hotel"]);
    $peracc = trim($_POST["peracc"]);
    $formation = trim($_POST["formation"]);
    $remboursement = trim($_POST["remboursement"]);
    $autre = trim($_POST["autre"]);
    $perdiem = trim($_POST["perdiem"]);

    // Validation des champs
    if (empty($obj)) {
        $errors['obj'] = "L'objectif de la mission est requis.";
    }
    if (empty($datem)) {
        $errors['datem'] = "La date de la mission est requise.";
    }
    if (empty($villem)) {
        $errors['villem'] = "La ville de la mission est requise.";
    }
    if (empty($villed)) {
        $errors['villed'] = "La ville de départ est requise.";
    }
    if (empty($dated)) {
        $errors['dated'] = "La date de départ est requise.";
    }
    if (empty($dater)) {
        $errors['dater'] = "La date de retour est requise.";
    }
    if ($dater < $dated) {
        $errors['date'] = "La date de retour ne peut pas être antérieure à la date de départ.";
    }

    // Si aucune erreur, procéder à la mise à jour
    if (empty($errors)) {
        try {
            // Calcul des frais
            $frais = calculateFrais($mtr, $remboursement, $villem, $villed, $db);

            // Calcul du total
            $total = $frais + (float)$autre + (float)$perdiem;

            // Mise à jour de la base de données
            $sql = "UPDATE ordred SET 
                obj = :obj,
                datem = :datem,
                villem = :villem,
                villed = :villed,
                dated = :dated,
                dater = :dater,
                mtr = :mtr,
                remboursement = :remboursement,
                frais = :frais,
                hotel = :hotel,
                peracc = :peracc,
                formation = :formation,
                autre = :autre,
                perdiem = :perdiem,
                total = :total,
                statut = 'En attente',
                dateupdate = NOW()
                WHERE id = :id";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':obj' => $obj,
                ':datem' => $datem,
                ':villem' => $villem,
                ':villed' => $villed,
                ':dated' => $dated,
                ':dater' => $dater,
                ':mtr' => $mtr,
                ':remboursement' => $remboursement,
                ':frais' => $frais,
                ':hotel' => $hotel,
                ':peracc' => $peracc,
                ':formation' => $formation,
                ':autre' => $autre,
                ':perdiem' => $perdiem,
                ':total' => $total,
                ':id' => $id
            ]);

            // Envoi de notification
            sendNotificationEmail(
                generateIdOM($db),
                $nom,
                $referto,
                $obj,
                ceil((strtotime($dater) - strtotime($dated)) / 86400),
                $perdiem,
                $frais,
                $total,
                $autre
            );

            $success = true;
            $_SESSION['success_message'] = "L'ordre de mission a été mis à jour avec succès.";
            header("Location: accueil.php");
            exit();

        } catch (PDOException $e) {
            $errors['database'] = "Erreur de base de données : " . $e->getMessage();
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

function generateIdOM($db) {
    $stmt = $db->query("SELECT MAX(id) FROM ordred");
    return 'OM-' . date('y') . '-' . str_pad($stmt->fetchColumn() + 1, 4, '0', STR_PAD_LEFT);
}

function sendNotificationEmail($idom, $nom, $referto, $obj, $days, $perdiem, $frais, $total, $autre)
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
        $mail->Subject = "Mise à jour $idom";
        $mail->Body = "
        <html>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
        </head>
        <body>
            <br>
            <h3>Vous avez un ordre de mission en attente d'approbation</h3>
            <p>Le coordinateur&nbsp;<strong>$nom</strong>&nbsp;vient de modifier sa demande d'ordre de mission<br>
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

function getRefertoEmail($referto) {
    global $db;
    $stmt = $db->prepare("SELECT email FROM usersom WHERE nom = ?");
    $stmt->execute([$referto]);
    return $stmt->fetchColumn() ?? 'admin@omct.org';
}
?>
<?php
if($data['statut'] == 'Approuvé'){
	header("Location: accueil.php?omapprouved=1");
            exit();
} else {
 
?>
<div class="wrapper noPrint"> 
	<b><p  align="right"><?php $currentDateTime = date('d-m-Y'); echo $currentDateTime;?></p></b>
<h2 class="mt-5">Remplir le formulaire pour enregistrer l'ordre de mission</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
			

	<input type="hidden" name="id" value="<?= htmlspecialchars($data['id'] ?? '') ?>">

                        
					<tr class="textbox">
                    <td><b>Objectif de la mission</b><input  type='text' name="obj" class="form-control" value="<?= htmlspecialchars($data['obj'] ?? '') ?>" required></td>
					</tr>
	<br>
					<tr class="textbox">
                            <td><b>Date de la mission</b><input type="text" name="datem" class="form-control" value="<?= htmlspecialchars($data['datem'] ?? '') ?>" required></td>
					</tr>
	<br>				
					<tr class="textbox">
                            <td><b>Ville de la mission</b><select Type="list" class="form-control" name="villem" id="villem-select"  required>
							    <option value="<?= htmlspecialchars($data['villem'] ?? '') ?>"><?= htmlspecialchars($data['villem'] ?? '') ?></option>
								<option value="Grand Tunis">Grand Tunis</option>
								<option value="Bizerte">Bizerte</option>
								<option value="Beja">Beja</option>
								<option value="Siliana">Siliana</option>
								<option value="Jendouba">Jendouba</option>
								<option value="Zaghouane">Zaghouane</option>
								<option value="Kef">Kef</option>
								<option value="Gafsa">Gafsa</option>
								<option value="Kasserine">Kasserine</option>
								<option value="Sidi Bouzid">Sidi Bouzid</option>
								<option value="Kairouan">Kairouan</option>
								<option value="Nabeul">Nabeul</option>
								<option value="Hammamet">Hammamet</option>
								<option value="Monastir">Monastir</option>
								<option value="Mahdia">Mahdia</option>
								<option value="Sousse">Sousse</option>
								<option value="Sfax">Sfax</option>
								<option value="Gabes">Gabes</option>
								<option value="Tozeur">Tozeur</option>
								<option value="Kébili">Kébili</option>
								<option value="TataOuine">TataOuine</option>
								<option value="Mednine">Mednine</option>
							</select></td>
					</tr>
	<br>				
					<tr class="textbox">
                             <td><b>Ville de départ</b><select Type="list" class="form-control" name="villed" id="villed-select"  required>
							    <option value="<?= htmlspecialchars($data['villed'] ?? '') ?>"><?= htmlspecialchars($data['villed'] ?? '') ?></option>
								<option value="Grand Tunis">Grand Tunis</option>
								<option value="Bizerte">Bizerte</option>
								<option value="Beja">Beja</option>
								<option value="Siliana">Siliana</option>
								<option value="Jendouba">Jendouba</option>
								<option value="Zaghouane">Zaghouane</option>
								<option value="Kef">Kef</option>
								<option value="Gafsa">Gafsa</option>
								<option value="Kasserine">Kasserine</option>
								<option value="Sidi Bouzid">Sidi Bouzid</option>
								<option value="Kairouan">Kairouan</option>
								<option value="Nabeul">Nabeul</option>
								<option value="Hammamet">Hammamet</option>
								<option value="Monastir">Monastir</option>
								<option value="Mahdia">Mahdia</option>
								<option value="Sousse">Sousse</option>
								<option value="Sfax">Sfax</option>
								<option value="Gabes">Gabes</option>
								<option value="Tozeur">Tozeur</option>
								<option value="Kébili">Kébili</option>
								<option value="TataOuine">TataOuine</option>
								<option value="Mednine">Mednine</option>
							</select></td>
					</tr>
	<br>				
					<tr class="textbox">
                            <td><b>Date de départ</b><?php include "erreur.php";?> <input type="date" id="dated" name="dated" class="form-control" value="<?= htmlspecialchars($data['dated'] ?? '') ?>" required></td>
					</tr>

					<tr class="textbox">
                             <td><b>Date de retour</b><input type="date" id="dater" name="dater" class="form-control" placeholder="Date de retour" value="<?= htmlspecialchars($data['dater'] ?? '') ?>" required ></td>
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
					<tr class="textbox">
					<td><b>Moyen de déplacement</b>
                             <select Type="list" class="form-control" name="mtr" id="mtr-select" required >
								<option value="Transport en commun">Transport en commun</option>
								<option value="Véhicule de location">Véhicule de location</option>
								<option value="Véhicule personnel">Véhicule personnel</option>
								<option value="Taxi">Taxi</option>
								<option value="Non">Non</option>
							</select></td>
					</tr>
					<tr class="textbox remboursement-section" style="display: None;">
					<td><b>Remboursement des frais de déplacement pour Aller-retour</b>
                             <select Type="list" class="form-control" name="remboursement" id="remboursement-select" required >
								<option value="Oui">Oui</option>
								<option value="Non">Non</option>
							</select></td>
					</tr>


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
					<td><b>Frais de carburant, Taxi ou autres :<h4>Si vous n'avez aucune autre dépenses veuillez mettre "0"</h4></b><input type="number_format" name="autre" class="form-control" required></td>
					</tr>
					

<table>					
                    <tr class="textbox">
						<td><center><input type="submit" class="btn btn-primary m1-2" value="Enregistrer"></td>
						<td><button type="button" class="btn btn-primary ml-2" onclick="history.back();">Annuler</button></td>

					</tr>
</table>									
</form>
</div>
<?php } ?>