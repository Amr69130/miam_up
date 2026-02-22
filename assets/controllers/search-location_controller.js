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

        // On garde l'événement pour la carte
        const event = new CustomEvent("location-updated", {
            detail: { lat: latitude, lng: longitude }
        });
        window.dispatchEvent(event);

        // --- NOUVEAU : POINT 3.2 ---
        // On appelle la fonction qui va chercher les restaurants sur le serveur
        this.fetchRestaurants(latitude, longitude);

        this.showMessage("Position trouvée ! Chargement des restaurants...", "success");
    }


    fetchRestaurants(lat, lng) {
        // [3.2.1] URL Dynamique
        const url = `/api/restaurants/search?lat=${lat}&lng=${lng}`;

        // [3.2.2] Appel Asynchrone
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error("Erreur serveur");
                return response.json(); // [3.2.3] Traitement JSON
            })
            .then(restaurants => {
                console.log("Liste des restos reçue :", restaurants);

                const event = new CustomEvent("restaurants-updated", {
                    detail: { restaurants: restaurants }
                });
                window.dispatchEvent(event);

                // On arrête le chargement ici une fois que les données sont là
                this.setLoading(false);
                this.showMessage(`${restaurants.length} restaurants trouvés autour de vous !`, "success");

                // Ici on ajoutera plus tard le point 3.3 pour mettre à jour l'interface
            })
            .catch(error => {
                console.error("Erreur :", error);
                this.showMessage("Impossible de charger les restaurants.", "error");
                this.setLoading(false);
            });
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
