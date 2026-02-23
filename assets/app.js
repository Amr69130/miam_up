import './styles/app.css';
import React from 'react';
import ReactDOM from 'react-dom/client';
import MapComponent from './components/Map/MapComponents';

import { Application } from '@hotwired/stimulus';
import SearchLocationController from './controllers/search-location_controller';

// On initialise manuellement si le bootstrap fait défaut
if (!window.Stimulus) {
    window.Stimulus = Application.start();
}
window.Stimulus.register('search-location', SearchLocationController);

const mapElement = document.getElementById('react-map-root');

if (mapElement) {
    // On récupère les données envoyées par Symfony
    const data = mapElement.getAttribute('data-items');

    // Ton MapComponent attend une prop nommée "magasins"
    const restaurants = data ? JSON.parse(data) : [];

    const root = ReactDOM.createRoot(mapElement);
    root.render(
        <React.StrictMode>
            <MapComponent magasins={restaurants} />
        </React.StrictMode>
    );
}

console.log('Système React activé !');
