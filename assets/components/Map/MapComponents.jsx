import React, { useEffect } from 'react';
import { MapContainer, TileLayer, Marker, Popup, useMap } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import './MapComponents.css';
import L from 'leaflet';
import icon from 'leaflet/dist/images/marker-icon.png';
import iconShadow from 'leaflet/dist/images/marker-shadow.png';

// Configuration de l'icône par défaut
let DefaultIcon = L.icon({
    iconUrl: icon,
    shadowUrl: iconShadow,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34]
});
L.Marker.prototype.options.icon = DefaultIcon;

/**
 * COMPOSANT INTERNE : Gère les événements de la carte
 * (Nécessaire pour utiliser le hook useMap)
 */
const LocationHandler = () => {
    const map = useMap();

    useEffect(() => {
        const handleLocationUpdate = (event) => {
            const { lat, lng } = event.detail;
            console.log("📍 React a capturé la position :", lat, lng);

            // ÉTAPE 2.4.C : On fait voler la carte vers la position
            map.flyTo([lat, lng], 15, {
                animate: true,
                duration: 1.5 // Durée du voyage en secondes
            });
        };

        // On écoute le signal "location-updated" lancé par Stimulus
        window.addEventListener('location-updated', handleLocationUpdate);

        // Nettoyage de l'écouteur si le composant est démonté
        return () => {
            window.removeEventListener('location-updated', handleLocationUpdate);
        };
    }, [map]);

    return null;
};

const MapComponent = ({ magasins }) => {
    const center = magasins.length > 0
        ? [magasins[0].lat, magasins[0].lng]
        : [48.8566, 2.3522];

    return (
        <MapContainer center={center} zoom={6} scrollWheelZoom={true}>
            <TileLayer
                attribution='&copy; OSM contributors'
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />

            {/* --- ÉTAPE 2.4.B : Activation du gestionnaire d'événements --- */}
            <LocationHandler />

            {magasins.map((magasin, index) => (
                <Marker
                    key={index}
                    position={[magasin.lat, magasin.lng]}
                >
                    <Popup>
                        <b>{magasin.nom}</b> <br />
                        Super resto !
                    </Popup>
                </Marker>
            ))}

        </MapContainer>
    );
};

export default MapComponent;
