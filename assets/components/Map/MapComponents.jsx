import React, { useEffect, useState } from 'react';
import { MapContainer, TileLayer, Marker, Popup, useMap } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import './MapComponents.css';
import L from 'leaflet';

// --- CONFIGURATION DES ICÔNES ---

const restaurantIcons = {
    standard: new L.Icon({
        iconUrl: '/images/markers/resto_gris.png', // Ton nouveau PNG gris
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -40],
    }),
    soon: new L.Icon({
        iconUrl: '/images/markers/resto_bleu.png', // Ton nouveau PNG bleu
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -40],
    }),
    today: new L.Icon({
        iconUrl: '/images/markers/resto_vert.png', // Ton nouveau PNG vert
        iconSize: [48, 48],
        iconAnchor: [24, 48],
        popupAnchor: [0, -48],
    })
};

const userIcon = new L.Icon({
    iconUrl: '/images/markers/user.png',
    iconSize: [50, 50],
    iconAnchor: [25, 50],
    popupAnchor: [0, -50],
});

const getMarkerIcon = (status) => {
    return restaurantIcons[status] || restaurantIcons.standard;
};

/**
 * COMPOSANT INTERNE : Ton LocationHandler qui gère le flyTo
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
            console.log("🚀 Données reçues dans React :", event.detail.restaurants);
            setListeMagasins(event.detail.restaurants);
        };

        window.addEventListener('restaurants-updated', handleRestaurantsUpdate);
        return () => window.removeEventListener('restaurants-updated', handleRestaurantsUpdate);
    }, []);

    const center = listeMagasins.length > 0
        ? [listeMagasins[0].lat, listeMagasins[0].lng]
        : [45.78, 4.83];

    return (
        <MapContainer center={center} zoom={6} scrollWheelZoom={true}>
            <TileLayer
                attribution='&copy; OSM contributors'
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />

            <LocationHandler setUserPos={setUserPos} />

            {/* MARQUEUR UTILISATEUR */}
            {userPos && (
                <Marker position={userPos} icon={userIcon}>
                    <Popup>📍 Vous êtes ici</Popup>
                </Marker>
            )}

            {/* BOUCLE RESTAURANTS */}
            {listeMagasins.map((magasin, index) => (
                <Marker
                    key={index}
                    position={[magasin.lat, magasin.lng]}
                    icon={getMarkerIcon(magasin.event_status)}
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
                                onClick={() => console.log("ID envoyé :", magasin.id)}
                                style={{
                                    marginTop: '5px',
                                    backgroundColor: '#4f46e5',
                                    color: 'white',
                                    border: 'none',
                                    padding: '8px',
                                    borderRadius: '4px',
                                    cursor: 'pointer',
                                    textAlign: 'center'
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
