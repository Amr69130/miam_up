import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["input"]

    // Cette fonction se lance dès qu'on clique sur "OK" ou "Entrée"
    filter() {
        const input = document.getElementById('search-input');
        const query = input.value.toLowerCase().trim();

        // On récupère la liste complète des restos qui est stockée dans la carte
        const mapRoot = document.getElementById('react-map-root');
        const allRestaurants = JSON.parse(mapRoot.dataset.items);

        // Si le champ est vide, on réaffiche tout
        if (query === "") {
            this.dispatchUpdate(allRestaurants);
            return;
        }

        // On filtre : on ne garde que ceux qui contiennent les lettres tapées
        const filtered = allRestaurants.filter(resto =>
            resto.nom.toLowerCase().includes(query)
        );

        // On envoie la liste filtrée à React pour mettre à jour les marqueurs
        this.dispatchUpdate(filtered);
    }

    dispatchUpdate(data) {
        const event = new CustomEvent('restaurants-updated', {
            detail: { restaurants: data }
        });
        window.dispatchEvent(event);
    }
}
