import React, { useEffect, useState } from 'react';
import { MapContainer, TileLayer, Marker, Popup, useMap } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import './MapComponents.css';
import L from 'leaflet';
import InfoModal from './InfoModal'; // Importation de ta nouvelle modal

// --- CONFIGURATION DES ICÔNES ---

const restaurantIcons = {
    standard: new L.Icon({
        iconUrl: '/images/markers/resto_gris.png',
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -40],
    }),
    soon: new L.Icon({
        iconUrl: '/images/markers/resto_bleu.png',
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -40],
    }),
    today: new L.Icon({
        iconUrl: '/images/markers/resto_vert.png',
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
 * COMPOSANT INTERNE : Gère les événements de la carte
 */
const LocationHandler = ({ setUserPos }) => {
    const map = useMap();

    useEffect(() => {
        const handleLocationUpdate = (event) => {
            const { lat, lng } = event.detail;
            setUserPos([lat, lng]);
            map.flyTo([lat, lng], 13.5, {
                animate: true,
                duration: 1.5
            });
        };

        window.addEventListener('location-updated', handleLocationUpdate);
        return () => window.removeEventListener('location-updated', handleLocationUpdate);
    }, [map, setUserPos]);

    return null;
};

const MapComponent = ({ magasins: magasinsInitiaux }) => {
    const [listeMagasins, setListeMagasins] = useState(magasinsInitiaux);
    const [userPos, setUserPos] = useState(null);

    // --- ÉTAT POUR LA MODAL ---
    const [isModalOpen, setIsModalOpen] = useState(false);

    useEffect(() => {
        // --- ONBOARDING (localStorage) ---
        const hasVisited = localStorage.getItem('miamup_has_visited');
        if (!hasVisited) {
            setIsModalOpen(true);
            localStorage.setItem('miamup_has_visited', 'true');
        }

        //fonction qui vient receptionner les nouveaux resultats
        const handleRestaurantsUpdate = (event) => {
            //mise à jour de la liste avec les données filtrées
            setListeMagasins(event.detail.restaurants);
        };
        //ici on ecoute le signal envoyé dans la barre de recherche
        window.addEventListener('restaurants-updated', handleRestaurantsUpdate);
        //enfin on nettoie l'ecouteur lorsqu'on quitte
        return () => window.removeEventListener('restaurants-updated', handleRestaurantsUpdate);
    }, []);

    const center = listeMagasins.length > 0
        ? [listeMagasins[0].lat, listeMagasins[0].lng]
        : [45.78, 4.83];

    return (
        <div style={{ position: 'relative', width: '100%', height: '500px' }}>

            {/* BOUTON INFO FLOTTANT */}
            <button
                onClick={() => setIsModalOpen(true)}
                title="Aide et légende"
                style={{
                    position: 'absolute',
                    top: '15px',
                    right: '15px',
                    zIndex: 1000,
                    backgroundColor: 'white',
                    border: '2px solid #4f46e5',
                    borderRadius: '50%',
                    width: '35px',
                    height: '35px',
                    fontWeight: 'bold',
                    color: '#4f46e5',
                    cursor: 'pointer',
                    boxShadow: '0 2px 8px rgba(0,0,0,0.2)',
                    display: 'flex',
                    justifyContent: 'center',
                    alignItems: 'center',
                    fontSize: '18px'
                }}
            >
                i
            </button>

            {/* FENÊTRE MODALE */}
            <InfoModal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} />

            <MapContainer center={center} zoom={6} scrollWheelZoom={true} style={{ height: '100%', width: '100%' }}>
                <TileLayer
                    attribution='&copy; OSM contributors'
                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                />

                <LocationHandler setUserPos={setUserPos} />

                {userPos && (
                    <Marker position={userPos} icon={userIcon}>
                        <Popup>📍 Vous êtes ici</Popup>
                    </Marker>
                )}

                {listeMagasins.map((magasin, index) => (
                    <Marker
                        key={index}
                        position={[magasin.lat, magasin.lng]}
                        icon={getMarkerIcon(magasin.event_status)}
                    >
                        <Popup>
                            <div style={{ display: 'flex', flexDirection: 'column', gap: '10px', minWidth: '200px' }}>
                                <b style={{ fontSize: '1.1rem', color: '#1f2937' }}>
                                    {magasin.nom} (ID: {magasin.id} | Statut: {magasin.event_status})
                                </b>

                                <span style={{ color: '#4f46e5', fontWeight: '600', fontSize: '0.85rem' }}>
            📍 {(magasin.distance_km !== undefined && magasin.distance_km !== null)
                                    ? `à ${Number(magasin.distance_km).toFixed(1)} km`
                                    : 'Distance non calculée'
                                }
        </span>

                                {/* --- SECTION ÉVÉNEMENT (Si présent) --- */}
                                {magasin.events && magasin.events.length > 0 && (
                                    <div style={{ borderTop: '1px solid #eee', pt: '8px', marginTop: '5px' }}>
                                        {magasin.events.map((event) => (
                                            <div key={event.id} style={{ marginBottom: '10px' }}>
                                                <div style={{ fontWeight: 'bold', fontSize: '0.9rem' }}>🔥 {event.titre}</div>

                                                {/* Liste des participants */}
                                                <ul style={{ listStyle: 'none', padding: 0, margin: '5px 0', fontSize: '0.8rem' }}>
                                                    {event.participants.map(p => (
                                                        <li key={p.id} style={{ display: 'flex', justifyContent: 'space-between', borderBottom: '1px dashed #eee' }}>
                                                            <span>{p.prenom}</span>
                                                            {p.id === event.createur_id && (
                                                                <span style={{ color: '#d97706', fontWeight: 'bold' }}>⭐ Orga</span>
                                                            )}
                                                        </li>
                                                    ))}
                                                </ul>

                                                {/* Bouton Rejoindre/Quitter */}
                                                <a href={`/event/join/${event.id}`}
                                                   style={{
                                                       display: 'block',
                                                       textAlign: 'center',
                                                       padding: '4px',
                                                       borderRadius: '4px',
                                                       textDecoration: 'none',
                                                       fontSize: '0.8rem',
                                                       backgroundColor: event.is_joined ? '#fee2e2' : '#dbeafe',
                                                       color: event.is_joined ? '#dc2626' : '#2563eb',
                                                       fontWeight: 'bold',
                                                       border: '1px solid currentColor'
                                                   }}>
                                                    {event.is_joined ? 'Quitter' : 'Rejoindre'}
                                                </a>
                                            </div>
                                        ))}
                                    </div>
                                )}

                                {/* --- BOUTON DE TON COLLÈGUE (Gardé intact) --- */}
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
                                        textAlign: 'center',
                                        fontWeight: 'bold'
                                    }}
                                >
                                    Voir détails du Restaurant
                                </button>
                            </div>
                        </Popup>
                    </Marker>
                ))}
            </MapContainer>
        </div>
    );
};

export default MapComponent;
