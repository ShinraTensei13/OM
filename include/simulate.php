<div class="container">
    <h2 class="text-center mb-4">Simulateur des Frais de Transport</h2>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <form id="transportForm">
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label for="villeDepart" class="form-label"><b>Ville de départ</b></label>
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
                                <label for="villeArrivee" class="form-label"><b>Ville d'arrivée</b></label>
                                <select class="form-select" id="villeArrivee" required>
                                    <option value="" selected disabled>Choisissez une ville</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mx-auto">
                                <label for="moyenTransport" class="form-label"><b>Moyen de transport</b></label>
                                <select class="form-select" id="moyenTransport" required>
                                    <option value="fraistr">Transport en commun</option>
                                    <option value="fxcar">Voiture personnelle</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div id="resultat" class="card shadow mt-4 d-none">
                <div class="card-body text-center">
                    <div id="montant" class="display-6">0 DT</div>
					<br>
                    <p id="details" class="text-muted"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const villeDepartSelect = document.getElementById('villeDepart');
    const villeArriveeSelect = document.getElementById('villeArrivee');
    const transportForm = document.getElementById('transportForm');
    const swapBtn = document.getElementById('swapCities');
    const resultDiv = document.getElementById('resultat');
    const montantDiv = document.getElementById('montant');
    const detailsP = document.getElementById('details');
    const moyenTransport = document.getElementById('moyenTransport');

    function chargerVilles() {
        fetch('get_villes.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
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

    function getFraisTransport(villed, villem, type) {
        return fetch(`get_tarif.php?villed=${encodeURIComponent(villed)}&villem=${encodeURIComponent(villem)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let montant = type === 'fraistr' ? data.fraistr : data.fxcar;
                    return {
                        montant: montant,
                        villed: villed,
                        villem: villem,
                        type: type
                    };
                } else {
                    throw new Error(data.message || "Aucun tarif trouvé pour ce trajet");
                }
            });
    }

    function calculerFrais() {
        const villed = villeDepartSelect.value;
        const villem = villeArriveeSelect.value;
        const typeTransport = moyenTransport.value;

        if (!villed || !villem || !typeTransport) return;

        getFraisTransport(villed, villem, typeTransport)
            .then(frais => {
                let montantPersonnel = frais.montant;
                let montantBeneficiaire = (villed !== villem) ? Math.max(montantPersonnel - 20, 0) : montantPersonnel;

                montantDiv.innerHTML = `
                    <div><b>Personnel :</b> ${montantPersonnel} DT</div>
                    <div><b>Bénéficiaire :</b> ${montantBeneficiaire} DT</div>
                `;

                detailsP.textContent = `De ${frais.villed} à ${frais.villem} (${typeTransport === 'fraistr' ? 'Transport en commun' : 'Voiture personnelle'})`;
                resultDiv.classList.remove('d-none');
            })
            .catch(error => {
                alert(error.message);
                resultDiv.classList.add('d-none');
            });
    }

    swapBtn.addEventListener('click', function() {
        const temp = villeDepartSelect.value;
        villeDepartSelect.value = villeArriveeSelect.value;
        villeArriveeSelect.value = temp;
        calculerFrais();
    });

    villeDepartSelect.addEventListener('change', calculerFrais);
    villeArriveeSelect.addEventListener('change', calculerFrais);
    moyenTransport.addEventListener('change', calculerFrais);

    transportForm.addEventListener('submit', function(e) {
        e.preventDefault();
        calculerFrais();
    });

    chargerVilles();
});
</script>
