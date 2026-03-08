import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    // Correction ici : on simplifie les noms pour éviter les erreurs Stimulus
    static targets = ["button", "icon", "spinner", "label", "message"];

    findMe() {
        this.setLoading(true);
        this.messageTarget.classList.add('hidden');

        if (!navigator.geolocation) {
            this.handleError({ code: 0, message: "Navigateur non compatible" });
            return;
        }

        // --- ON FORCE UNE LECTURE FRAICHE ---
        const options = {
            enableHighAccuracy: true, // Demande le GPS réel, pas juste l'IP
            timeout: 15000,           // On laisse 15 secondes au GPS pour se réveiller
            maximumAge: 0             // INTERDIT d'utiliser la position en cache (René Cassin)
        };

        // Optionnel mais efficace : On "watch" la position pour forcer le rafraîchissement
        navigator.geolocation.getCurrentPosition(
            (position) => {
                console.log("📍 GPS Fix obtenu !");
                this.handleSuccess(position);
            },
            (error) => this.handleError(error),
            options
        );
    }

    handleSuccess(position) {
        const { latitude, longitude } = position.coords;
        console.log("📍 Position réelle (Crolles?) :", latitude, longitude);

        const event = new CustomEvent("location-updated", {
            detail: { lat: latitude, lng: longitude }
        });
        window.dispatchEvent(event);

        this.fetchRestaurants(latitude, longitude);
        this.showMessage("Position trouvée ! Chargement des restaurants...", "success");
    }

    fetchRestaurants(lat, lng) {
        const url = `/api/restaurants/search?lat=${lat}&lng=${lng}`;

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error("Erreur serveur");
                return response.json();
            })
            .then(restaurants => {
                const event = new CustomEvent("restaurants-updated", {
                    detail: { restaurants: restaurants }
                });
                window.dispatchEvent(event);

                this.setLoading(false);
                this.showMessage(`${restaurants.length} restaurants trouvés autour de vous !`, "success");
            })
            .catch(error => {
                console.error("Erreur :", error);
                this.showMessage("Impossible de charger les restaurants.", "error");
                this.setLoading(false);
            });
    }

    handleError(error) {
        let msg = "Une erreur est survenue lors de la localisation.";
        switch (error.code) {
            case error.PERMISSION_DENIED:
                msg = "Accès refusé. Autorisez la géoloc dans le navigateur.";
                break;
            case error.POSITION_UNAVAILABLE:
                msg = "Signal GPS trop faible.";
                break;
            case error.TIMEOUT:
                msg = "Délai dépassé.";
                break;
        }
        this.showMessage(msg, "error");
        this.setLoading(false);
    }

    setLoading(isLoading) {
        this.buttonTarget.disabled = isLoading;
        // Correction des noms de targets ici
        if (this.hasIconTarget) this.iconTarget.classList.toggle('hidden', isLoading); // Celle-là est OK
        if (this.hasSpinnerTarget) this.spinnerTarget.classList.toggle('hidden', !isLoading); // OK aussi
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
