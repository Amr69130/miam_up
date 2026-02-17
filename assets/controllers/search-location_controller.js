import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["button", "iconTarget", "iconSpinner", "label", "message"];

    connect() {
        console.log("Controller search-location : CONNECTÉ ✅");
    }

    // C'est cette fonction que Stimulus cherche lors du clic
    findMe() {
        console.log("Clic détecté ! Début de la géolocalisation...");

        // 1. On change l'apparence du bouton pour montrer que ça charge
        this.setLoading(true);

        // 2. On vérifie si le navigateur supporte la géolocalisation
        if (!navigator.geolocation) {
            this.showMessage("Désolé, votre navigateur ne supporte pas la géolocalisation.", "error");
            this.setLoading(false);
            return;
        }

        // 3. On demande la position
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                console.log("Coordonnées trouvées :", lat, lng);
                this.showMessage(`Position trouvée : ${lat.toFixed(4)}, ${lng.toFixed(4)}`, "success");
                this.setLoading(false);
            },
            (error) => {
                console.error("Erreur GPS :", error);
                this.showMessage("Impossible de récupérer votre position.", "error");
                this.setLoading(false);
            }
        );
    }

    // Petite fonction utilitaire pour changer l'état du bouton
    setLoading(isLoading) {
        this.buttonTarget.disabled = isLoading;
        this.iconTargetTarget.classList.toggle('hidden', isLoading);
        this.iconSpinnerTarget.classList.toggle('hidden', !isLoading);
        this.labelTarget.textContent = isLoading ? "Recherche en cours..." : "Autour de moi";
    }

    // Petite fonction utilitaire pour afficher un message
    showMessage(text, type) {
        this.messageTarget.textContent = text;
        this.messageTarget.classList.remove('hidden', 'bg-red-100', 'text-red-700', 'bg-green-100', 'text-green-700');

        const colors = type === "error"
            ? ['bg-red-100', 'text-red-700', 'border-red-200']
            : ['bg-green-100', 'text-green-700', 'border-green-200'];

        this.messageTarget.classList.add(...colors);
    }
}
