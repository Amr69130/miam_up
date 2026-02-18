import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["button", "iconTarget", "iconSpinner", "label", "message"];

    findMe() {
        this.setLoading(true);
        this.messageTarget.classList.add('hidden'); // On cache les anciens messages

        if (!navigator.geolocation) {
            this.handleError({ code: 0, message: "Navigateur non compatible" });
            return;
        }

        // --- OPTIMISATION 2.3 (Batterie & Fiabilité) ---
        const options = {
            enableHighAccuracy: true, // Précision maximale
            timeout: 10000,           // 2.3.B : 10 sec max avant d'abandonner
            maximumAge: 300000        // 2.3.A : Cache de 5 min pour économiser la batterie
        };

        navigator.geolocation.getCurrentPosition(
            (position) => this.handleSuccess(position),
            (error) => this.handleError(error),
            options
        );
    }

    handleSuccess(position) {
        const { latitude, longitude } = position.coords;

        // On envoie le "SMS" (l'événement)
        const event = new CustomEvent("location-updated", {
            detail: { lat: latitude, lng: longitude }
        });
        window.dispatchEvent(event);

        this.showMessage("Position trouvée !", "success");
        this.setLoading(false);
    }

    // --- GESTION DES ERREURS 2.2 (Permissions & Réseau) ---
    handleError(error) {
        let msg = "Une erreur est survenue lors de la localisation.";

        switch (error.code) {
            case error.PERMISSION_DENIED: // 2.2.A
                msg = "Accès refusé. Veuillez autoriser la géolocalisation dans vos paramètres.";
                break;
            case error.POSITION_UNAVAILABLE: // 2.2.B
                msg = "Signal GPS trop faible ou position indisponible.";
                break;
            case error.TIMEOUT: // 2.3.B
                msg = "Délai d'attente dépassé (vérifiez votre connexion).";
                break;
        }

        this.showMessage(msg, "error");
        this.setLoading(false);
    }

    setLoading(isLoading) {
        this.buttonTarget.disabled = isLoading;
        this.iconTargetTarget.classList.toggle('hidden', isLoading);
        this.iconSpinnerTarget.classList.toggle('hidden', !isLoading);
        this.labelTarget.textContent = isLoading ? "Recherche..." : "Autour de moi";
    }

    showMessage(text, type) {
        this.messageTarget.textContent = text;
        this.messageTarget.classList.remove('hidden', 'bg-red-100', 'text-red-700', 'bg-green-100', 'text-green-700', 'border-red-200', 'border-green-200');

        const colors = type === "error"
            ? ['bg-red-100', 'text-red-700', 'border-red-200']
            : ['bg-green-100', 'text-green-700', 'border-green-200'];

        this.messageTarget.classList.add(...colors);
    }
}
