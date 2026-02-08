import React from 'react';
import { createRoot } from 'react-dom/client';
import MapComponent from './components/Map/MapComponents.jsx';

document.addEventListener('DOMContentLoaded', () => {
    const rootElement = document.getElementById('react-map-root');

    if (rootElement) {
        // 1. On récupère le JSON caché dans le HTML
        const items = JSON.parse(rootElement.dataset.items);

        const root = createRoot(rootElement);
        
        // 2. On passe les items au composant React
        root.render(<MapComponent magasins={items} />);
    }
});