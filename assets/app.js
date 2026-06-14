import './styles/app.css';
import '@hotwired/turbo';
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

// Turbo Drive : on stocke les racines React pour le montage/démontage entre navigations
let mapRoot = null;
let chatRoot = null;
let unreadInterval = null;

/**
 * Fonction d'initialisation appelée à chaque navigation Turbo
 */
function initReactComponents() {

    /**
     * COMPOSANT : CARTE (MAP)
     */
    const mapElement = document.getElementById('react-map-root');
    if (mapElement) {
        const data = mapElement.getAttribute('data-items');
        const restaurants = data ? JSON.parse(data) : [];

        mapRoot = ReactDOM.createRoot(mapElement);
        mapRoot.render(
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
        // On récupère TOUTES les données passées par Twig (Ami OU Groupe)
        const idAmi = chatElement.getAttribute('data-id-ami');
        const idGroupe = chatElement.getAttribute('data-id-groupe');
        const prenomAmi = chatElement.getAttribute('data-prenom-ami');
        const nomGroupe = chatElement.getAttribute('data-nom-groupe');

        chatRoot = ReactDOM.createRoot(chatElement);
        chatRoot.render(
            <React.StrictMode>
                <Chat
                    idAmi={idAmi}
                    idGroupe={idGroupe}
                    prenomAmi={prenomAmi}
                    nomGroupe={nomGroupe}
                />
            </React.StrictMode>
        );
    }

    // Pastille de messages non lus
    window.updateUnreadBadge();
    unreadInterval = setInterval(() => window.updateUnreadBadge(), 10000);
}

/**
 * Nettoyage avant chaque navigation Turbo (évite les fuites mémoire)
 */
function cleanupReactComponents() {
    if (mapRoot) {
        mapRoot.unmount();
        mapRoot = null;
    }
    if (chatRoot) {
        chatRoot.unmount();
        chatRoot = null;
    }
    if (unreadInterval) {
        clearInterval(unreadInterval);
        unreadInterval = null;
    }
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
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }
    } catch (error) {
        console.error("Erreur lors de la récupération des messages non lus", error);
    }
}

// Turbo : init des composants React à chaque navigation (y compris le premier chargement)
document.addEventListener('turbo:load', () => {
    initReactComponents();
});

// Turbo : nettoyage avant de quitter la page
document.addEventListener('turbo:before-visit', () => {
    cleanupReactComponents();
});
