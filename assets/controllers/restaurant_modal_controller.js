import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["container"]

    connect() {
        this.openHandler = this.open.bind(this);
        window.addEventListener('open-restaurant-modal', this.openHandler);
    }

    disconnect() {
        window.removeEventListener('open-restaurant-modal', this.openHandler);
    }

    async open(event) {
        const { id, distance_km } = event.detail;
        if (!id) return;

        try {
            const url = new URL(`/restaurant/${id}/modal`, window.location.origin);
            if (distance_km) {
                url.searchParams.append('distance', distance_km);
            }
            
            const response = await fetch(url);
            if (!response.ok) throw new Error('Failed to fetch modal');
            
            const html = await response.text();
            this.containerTarget.innerHTML = html;
        } catch (error) {
            console.error('Error fetching restaurant modal:', error);
        }
    }

    close() {
        this.containerTarget.innerHTML = '';
    }
}
