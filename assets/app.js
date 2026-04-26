import './styles/app.css';
import React from 'react';
import ReactDOM from 'react-dom/client';
import MapComponent from './components/Map/MapComponents';
// Import du nouveau composant Chat
import Chat from './components/Chat';

import { Application } from '@hotwired/stimulus';
import SearchLocationController from './controllers/search-location_controller';
import RestaurantModalController from './controllers/restaurant_modal_controller';

// On initialise manuellement si le bootstrap fait défaut
if (!window.Stimulus) {
    window.Stimulus = Application.start();
}
window.Stimulus.register('search-location', SearchLocationController);
window.Stimulus.register('restaurant-modal', RestaurantModalController);

/**
 * COMPOSANT : CARTE (MAP)
 */
const mapElement = document.getElementById('react-map-root');
if (mapElement) {
    const data = mapElement.getAttribute('data-items');
    const restaurants = data ? JSON.parse(data) : [];

    const root = ReactDOM.createRoot(mapElement);
    root.render(
        <React.StrictMode>
            <MapComponent magasins={restaurants} />
        </React.StrictMode>
    );
}

/**
 * COMPOSANT : CHAT MESSENGER
 */
const chatElement = document.getElementById('react-chat-root');
if (chatElement) {
    // On récupère l'ID et le Prénom de l'ami passés via le Twig
    const idAmi = chatElement.getAttribute('data-id-ami');
    const prenomAmi = chatElement.getAttribute('data-prenom-ami');

    const root = ReactDOM.createRoot(chatElement);
    root.render(
        <React.StrictMode>
            <Chat idAmi={idAmi} prenomAmi={prenomAmi} />
        </React.StrictMode>
    );
}

// Fonction pour mettre à jour la pastille de messages
window.updateUnreadBadge = async function() {
    try {
        const response = await fetch('/api/chat/unread-count');
        const data = await response.json();
        const badge = document.getElementById('unread-badge');

        if (badge) {
            if (data.unreadCount > 0) {
                badge.innerText = data.unreadCount;
                badge.style.display = 'block'; // On utilise style.display au lieu de classList
            } else {
                badge.style.display = 'none';
            }
        }
    } catch (error) {
        console.error("Erreur lors de la récupération des messages non lus", error);
    }
}

// On l'exécute au chargement de la page
updateUnreadBadge();

// On vérifie toutes les 10 secondes pour que la pastille se mette à jour "en direct"
setInterval(() => window.updateUnreadBadge(), 10000);
console.log('Système React activé !');
