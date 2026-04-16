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
            el.className = "text-gray-700 font-medium text-sm hover:text-gray-900 pb-3 -mb-[13px] border-b-2 border-transparent transition-colors cursor-pointer";
        });

        // Appliquer le style actif
        event.currentTarget.className = "text-[#20B2AA] font-medium text-sm border-b-2 border-[#20B2AA] pb-3 -mb-[13px] transition-colors cursor-pointer";
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
