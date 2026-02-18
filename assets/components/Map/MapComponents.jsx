import React, { useEffect, useState } from 'react';
import { MapContainer, TileLayer, Marker, Popup, useMap } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import './MapComponents.css';
import L from 'leaflet';
import icon from 'leaflet/dist/images/marker-icon.png';
import iconShadow from 'leaflet/dist/images/marker-shadow.png';

// Configuration de l'icône par défaut (Bleue)
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
 */
const LocationHandler = ({ setUserPos }) => {
    const map = useMap();

    useEffect(() => {
        const handleLocationUpdate = (event) => {
            const { lat, lng } = event.detail;
            console.log("📍 React a capturé la position :", lat, lng);

            setUserPos([lat, lng]);

            map.flyTo([lat, lng], 15, {
                animate: true,
                duration: 1.5
            });
        };

        window.addEventListener('location-updated', handleLocationUpdate);
        return () => {
            window.removeEventListener('location-updated', handleLocationUpdate);
        };
    }, [map, setUserPos]);

    return null;
};

const MapComponent = ({ magasins }) => {
    const [userPos, setUserPos] = useState(null);

    const center = magasins.length > 0
        ? [magasins[0].lat, magasins[0].lng]
        : [48.8566, 2.3522];

    return (
        <MapContainer center={center} zoom={6} scrollWheelZoom={true}>
            <TileLayer
                attribution='&copy; OSM contributors'
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />

            <LocationHandler setUserPos={setUserPos} />

            {/* MARQUEUR ROUGE POUR L'UTILISATEUR */}
            {userPos && (
                <Marker
                    position={userPos}
                    icon={L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })}
                >
                    <Popup>📍 Vous êtes ici</Popup>
                </Marker>
            )}

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
