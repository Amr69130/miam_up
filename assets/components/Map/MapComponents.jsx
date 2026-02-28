import React, { useEffect, useState } from 'react';
import { MapContainer, TileLayer, Marker, Popup, useMap } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import './MapComponents.css';
import L from 'leaflet';
import icon from 'leaflet/dist/images/marker-icon.png';
import iconShadow from 'leaflet/dist/images/marker-shadow.png';

// --- CONFIGURATION DES ICÔNES ---

// 1. Icône par défaut pour les restaurants (Bleue standard)
let DefaultIcon = L.icon({
    iconUrl: icon,
    shadowUrl: iconShadow,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34]
});
L.Marker.prototype.options.icon = DefaultIcon;

// icône "MOI"
const userIcon = new L.Icon({
    iconUrl: '/images/markers/user.png',
    iconSize: [50, 50],
    iconAnchor: [25, 50],
    popupAnchor: [0, -50],
});

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
    const [listeMagasins, setListeMagasins] = useState(magasinsInitiaux);
    const [userPos, setUserPos] = useState(null);

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

            {/* PHASE 1 : MARQUEUR PERSONNALISÉ POUR L'UTILISATEUR */}
            {userPos && (
                <Marker
                    position={userPos}
                    icon={userIcon} // On utilise notre constante définie plus haut
                >
                    <Popup>📍 Vous êtes ici</Popup>
                </Marker>
            )}

            {/* Boucle sur les restaurants */}
            {listeMagasins.map((magasin, index) => (
                <Marker
                    key={index}
                    position={[magasin.lat, magasin.lng]}
                    // Ici, pour l'instant, ça utilise l'icône par défaut (DefaultIcon)
                >
                    <Popup>
                        <div style={{ display: 'flex', flexDirection: 'column', gap: '8px', minWidth: '150px' }}>
                            <b style={{ fontSize: '1.1rem', color: '#1f2937' }}>{magasin.nom}</b>

                            <span style={{ color: '#4f46e5', fontWeight: '600', fontSize: '0.9rem' }}>
                                📍 {(magasin.distance_km !== undefined && magasin.distance_km !== null)
                                ? `à ${Number(magasin.distance_km).toFixed(1)} km`
                                : 'Distance non calculée'
                            }
                            </span>

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
