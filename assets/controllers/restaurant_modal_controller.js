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

    switchTab(event) {
        event.preventDefault();
        const tabName = event.currentTarget.dataset.tab;
        
        // Masquer tous les contenus
        this.element.querySelectorAll('[data-tab-content]').forEach(el => el.classList.add('hidden'));
        
        // Afficher le contenu ciblé
        const target = this.element.querySelector(`[data-tab-content="${tabName}"]`);
        if (target) {
            target.classList.remove('hidden');
        }

        // Réinitialiser le style de tous les onglets
        this.element.querySelectorAll('[data-tab]').forEach(el => {
            el.classList.remove('text-[#00A9A5]', 'border-[#00A9A5]', 'text-[#20B2AA]', 'border-[#20B2AA]');
            el.classList.add('text-gray-500', 'border-transparent', 'hover:text-gray-800');
        });

        // Appliquer le style actif
        event.currentTarget.classList.remove('text-gray-500', 'border-transparent', 'hover:text-gray-800');
        event.currentTarget.classList.add('text-[#00A9A5]', 'border-[#00A9A5]');
    }

    openEventModal(event) {
        const id = event.currentTarget.dataset.eventId;
        if (!id) return;
        const modal = document.getElementById(`event-modal-${id}`);
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    closeEventModal(event) {
        const id = event.currentTarget.dataset.eventId;
        if (!id) return;
        const modal = document.getElementById(`event-modal-${id}`);
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    closeEventModalOutside(event) {
        if (event.target === event.currentTarget) {
            event.currentTarget.classList.add('hidden');
        }
    }
}
