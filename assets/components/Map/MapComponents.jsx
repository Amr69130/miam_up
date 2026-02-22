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

            map.flyTo([lat, lng], 13.5, {
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

const MapComponent = ({ magasins: magasinsInitiaux }) => {
    // MODIFICATION ICI : On utilise un state pour pouvoir mettre à jour la liste
    const [listeMagasins, setListeMagasins] = useState(magasinsInitiaux);
    const [userPos, setUserPos] = useState(null);

    // MODIFICATION ICI : On écoute l'événement de mise à jour des restos
    useEffect(() => {
        const handleRestaurantsUpdate = (event) => {
            console.log("🚀 Mise à jour des magasins avec distances :", event.detail.restaurants);
            setListeMagasins(event.detail.restaurants);
        };

        window.addEventListener('restaurants-updated', handleRestaurantsUpdate);
        return () => window.removeEventListener('restaurants-updated', handleRestaurantsUpdate);
    }, []);

    const center = listeMagasins.length > 0
        ? [listeMagasins[0].lat, listeMagasins[0].lng]
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

            {/* On boucle sur listeMagasins au lieu de magasins */}
            {listeMagasins.map((magasin, index) => (
                <Marker
                    key={index}
                    position={[magasin.lat, magasin.lng]}
                >
                    <Popup>
                        <div style={{ display: 'flex', flexDirection: 'column', gap: '8px', minWidth: '150px' }}>
                            {/* 1. NOM DU RESTO */}
                            <b style={{ fontSize: '1.1rem', color: '#1f2937' }}>{magasin.nom}</b>

                            {/* 2. DISTANCE */}
                            <span style={{ color: '#4f46e5', fontWeight: '600', fontSize: '0.9rem' }}>
                                📍 {(magasin.distance_km !== undefined && magasin.distance_km !== null)
                                ? `à ${Number(magasin.distance_km).toFixed(1)} km`
                                : 'Distance non calculée'
                            }
                            </span>

                            {/* 3. BOUTON POUR TON COLLEGUE */}
                            <button
                                onClick={() => console.log("ID envoyé à la card :", magasin.id)}
                                style={{
                                    marginTop: '5px',
                                    backgroundColor: '#4f46e5',
                                    color: 'white',
                                    border: 'none',
                                    padding: '8px',
                                    borderRadius: '4px',
                                    cursor: 'pointer',
                                    textAlign: 'center',
                                    fontSize: '0.8rem'
                                }}
                            >
                                Voir détails
                            </button>
                        </div>
                    </Popup>
                </Marker>
            ))}

        </MapContainer>
    );
};

export default MapComponent;
