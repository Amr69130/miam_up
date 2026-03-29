import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["button", "icon", "spinner", "label", "message"];

    /**
     * NOUVELLE FONCTION : Filtrage par nom (Bouton OK)
     */
    filter() {
        const input = document.getElementById('search-input');
        const query = input.value.toLowerCase().trim();
        const mapRoot = document.getElementById('react-map-root');

        if (!mapRoot) return;

        // On récupère les données initiales injectées par Symfony
        const allRestaurants = JSON.parse(mapRoot.dataset.items);

        console.log("🔍 Filtrage en cours pour :", query);

        // Filtrage local
        const filtered = allRestaurants.filter(resto =>
            resto.nom.toLowerCase().includes(query)
        );

        // Envoi à la carte React via l'événement qu'elle écoute déjà
        const event = new CustomEvent('restaurants-updated', {
            detail: { restaurants: filtered }
        });
        window.dispatchEvent(event);
    }

    /**
     * FONCTION EXISTANTE : Géolocalisation (Bouton Orange)
     */
    findMe() {
        this.setLoading(true);
        this.messageTarget.classList.add('hidden');

        if (!navigator.geolocation) {
            this.handleError({ code: 0, message: "Navigateur non compatible" });
            return;
        }

        const options = {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        };

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
            case 1: msg = "Accès refusé. Autorisez la géoloc."; break;
            case 2: msg = "Signal GPS trop faible."; break;
            case 3: msg = "Délai dépassé."; break;
        }
        this.showMessage(msg, "error");
        this.setLoading(false);
    }

    setLoading(isLoading) {
        this.buttonTarget.disabled = isLoading;
        if (this.hasIconTarget) this.iconTarget.classList.toggle('hidden', isLoading);
        if (this.hasSpinnerTarget) this.spinnerTarget.classList.toggle('hidden', !isLoading);
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
