<div class="container">
        <h1 class="text-center mb-4">Calculateur de Frais de Transport</h1>
        
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body">
                        <form id="transportForm">
                            <div class="row mb-3">
                                <div class="col-md-5">
                                    <label for="villeDepart" class="form-label">Ville de départ</label>
                                    <select class="form-select" id="villeDepart" required>
                                        <option value="" selected disabled>Choisissez une ville</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-2 d-flex align-items-center justify-content-center">
                                    <button type="button" id="swapCities" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left-right"></i>
                                    </button>
                                </div>
                                
                                <div class="col-md-5">
                                    <label for="villeArrivee" class="form-label">Ville d'arrivée</label>
                                    <select class="form-select" id="villeArrivee" required>
                                        <option value="" selected disabled>Choisissez une ville</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6 mx-auto">
                                    <label for="moyenTransport" class="form-label">Moyen de transport</label>
                                    <select class="form-select" id="moyenTransport" required>
                                        <option value="fraistr">Transport en commun</option>
                                        <option value="fxcar">Voiture personnelle</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 col-md-6 mx-auto">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <span id="btnText">Calculer les frais</span>
                                    <span id="btnLoading" class="loading d-none"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div id="resultat" class="card shadow mt-4 d-none">
                    <div class="card-body text-center">
                        <h3>Frais de transport</h3>
                        <div id="montant" class="display-4 my-3">0 DT</div>
                        <p id="details" class="text-muted"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Récupérer les éléments du DOM
            const villeDepartSelect = document.getElementById('villeDepart');
            const villeArriveeSelect = document.getElementById('villeArrivee');
            const transportForm = document.getElementById('transportForm');
            const swapBtn = document.getElementById('swapCities');
            const resultDiv = document.getElementById('resultat');
            const montantDiv = document.getElementById('montant');
            const detailsP = document.getElementById('details');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnLoading = document.getElementById('btnLoading');
            
            // Charger la liste des villes depuis la base de données
            function chargerVilles() {
                fetch('get_villes.php')
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            // Trier les villes par ordre alphabétique
                            const villes = [...new Set(data.villes)].sort((a, b) => a.localeCompare(b));
                            
                            villes.forEach(ville => {
                                const option1 = document.createElement('option');
                                option1.value = ville;
                                option1.textContent = ville;
                                villeDepartSelect.appendChild(option1);
                                
                                const option2 = document.createElement('option');
                                option2.value = ville;
                                option2.textContent = ville;
                                villeArriveeSelect.appendChild(option2);
                            });
                        } else {
                            console.error("Erreur lors du chargement des villes");
                        }
                    })
                    .catch(error => console.error("Erreur:", error));
            }
            
            // Fonction pour récupérer les frais depuis la base de données (gère les trajets dans les deux sens)
            function getFraisTransport(villed, villem, type) {
                toggleLoading(true);
                
                return fetch(`get_tarif.php?villed=${encodeURIComponent(villed)}&villem=${encodeURIComponent(villem)}`)
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            return {
                                montant: type === 'fraistr' ? data.fraistr : data.fxcar,
                                villed: villed,
                                villem: villem,
                                type: type
                            };
                        } else {
                            throw new Error(data.message || "Aucun tarif trouvé pour ce trajet");
                        }
                    })
                    .finally(() => toggleLoading(false));
            }
            
            // Afficher/masquer l'indicateur de chargement
            function toggleLoading(show) {
                if(show) {
                    btnText.classList.add('d-none');
                    btnLoading.classList.remove('d-none');
                    submitBtn.disabled = true;
                } else {
                    btnText.classList.remove('d-none');
                    btnLoading.classList.add('d-none');
                    submitBtn.disabled = false;
                }
            }
            
            // Échanger les villes
            swapBtn.addEventListener('click', function() {
                const temp = villeDepartSelect.value;
                villeDepartSelect.value = villeArriveeSelect.value;
                villeArriveeSelect.value = temp;
                
                if(villeDepartSelect.value && villeArriveeSelect.value) {
                    calculerFrais();
                }
            });
            
            // Calculer les frais
            function calculerFrais() {
                const villed = villeDepartSelect.value;
                const villem = villeArriveeSelect.value;
                const type = document.getElementById('moyenTransport').value;
                
                if(!villed || !villem) return;
                
                getFraisTransport(villed, villem, type)
                    .then(frais => {
                        montantDiv.textContent = frais.montant + " DT";
                        detailsP.textContent = `De ${frais.villed} à ${frais.villem} (${type === 'fraistr' ? 'Public' : 'Spécial'})`;
                        resultDiv.classList.remove('d-none');
                    })
                    .catch(error => {
                        alert(error.message);
                        resultDiv.classList.add('d-none');
                    });
            }
            
            // Événements
            villeDepartSelect.addEventListener('change', calculerFrais);
            villeArriveeSelect.addEventListener('change', calculerFrais);
            document.getElementById('moyenTransport').addEventListener('change', calculerFrais);
            
            transportForm.addEventListener('submit', function(e) {
                e.preventDefault();
                calculerFrais();
            });
            
            // Initialisation
            chargerVilles();
        });
    </script>