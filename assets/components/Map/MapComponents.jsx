import React, { useEffect, useState } from 'react';
import { MapContainer, TileLayer, Marker, Tooltip, useMap } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import './MapComponents.css';
import L from 'leaflet';
import InfoModal from './InfoModal'; // Importation de ta nouvelle modal

// --- CONFIGURATION DES ICÔNES ---

const MARKER_COLORS = {
    standard: { fill: "#99A1AF", ring: "#FFFFFF" },
    today:    { fill: "#00A9A5", ring: "#FFFFFF" },
    soon:     { fill: "#3B82F6", ring: "#FFFFFF" },
};

const MARKER_ICONS_SVG = {
    standard: (fill) => `
        <g stroke="${fill}" stroke-width="2.6" stroke-linecap="round">
            <line x1="24" y1="17" x2="24" y2="27"/>
            <line x1="19" y1="22" x2="29" y2="22"/>
        </g>`,
    today: (fill) => `
        <path d="M19 22.5l3.5 3.5L29.5 19"
              stroke="${fill}" stroke-width="2.8" stroke-linecap="round"
              stroke-linejoin="round" fill="none"/>`,
    soon: (fill) => `
        <g fill="none" stroke="${fill}" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="24" cy="22" r="5.5"/>
            <polyline points="24,18.5 24,22 26.5,23.5"/>
        </g>`,
};

const makeMarkerIcon = (variant) => {
    const { fill, ring } = MARKER_COLORS[variant] || MARKER_COLORS.standard;
    const icon = (MARKER_ICONS_SVG[variant] || MARKER_ICONS_SVG.standard)(fill);
    const w = 40, h = 54;
    const svg = `<svg width="${w}" height="${h}" viewBox="0 0 48 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <ellipse cx="24" cy="60" rx="7" ry="2" fill="#000" opacity="0.18"/>
        <path d="M24 2C12.4 2 3 11.2 3 22.6c0 6.3 2.9 11.9 7.5 16.4 4.9 4.8 10.2 10.6 12.4 17.2.4 1.2 2 1.2 2.4 0 2.2-6.6 7.5-12.4 12.4-17.2C40.1 34.5 43 28.9 43 22.6 43 11.2 33.6 2 24 2Z"
              fill="${fill}" stroke="${ring}" stroke-width="2.5"/>
        <circle cx="24" cy="22" r="10" fill="${ring}"/>
        ${icon}
    </svg>`;
    return L.divIcon({
        html: svg,
        className: '',
        iconSize: [w, h],
        iconAnchor: [w / 2, h],
        popupAnchor: [0, -h],
    });
};

const restaurantIcons = {
    standard: makeMarkerIcon('standard'),
    soon:     makeMarkerIcon('soon'),
    today:    makeMarkerIcon('today'),
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
        <div style={{ position: 'relative', width: '100%', height: '100%' }}>

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
                        <Tooltip permanent direction="top" offset={[0, -54]} className="map-tooltip user-tooltip">
                            📍 Vous êtes ici
                        </Tooltip>
                    </Marker>
                )}

                {listeMagasins.map((magasin, index) => (
                    <Marker
                        key={index}
                        position={[magasin.lat, magasin.lng]}
                        icon={getMarkerIcon(magasin.event_status)}
                        eventHandlers={{
                            click: () => {
                                window.dispatchEvent(new CustomEvent('open-restaurant-modal', { 
                                    detail: { id: magasin.id, distance_km: magasin.distance_km } 
                                }));
                            },
                        }}
                    >
                        <Tooltip direction="top" offset={[0, -54]} opacity={1} className="map-tooltip restaurant-tooltip">
                            <div style={{ fontWeight: 600, fontSize: '13px', color: '#4A4A4A', lineHeight: '1.2' }}>
                                {magasin.nom}
                            </div>
                            {(magasin.note || magasin.type) && (
                                <div style={{ display: 'flex', alignItems: 'center', gap: '4px', marginTop: '2px', fontSize: '12px', color: '#4A4A4A' }}>
                                    {magasin.note && (
                                        <>
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="#4A4A4A">
                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                            </svg>
                                            <span>{Number(magasin.note).toFixed(1)}</span>
                                        </>
                                    )}
                                    {magasin.note && magasin.type && <span style={{ color: '#99A1AF' }}>·</span>}
                                    {magasin.type && <span style={{ color: '#99A1AF' }}>{magasin.type}</span>}
                                </div>
                            )}
                        </Tooltip>
                    </Marker>
                ))}
            </MapContainer>
        </div>
    );
};

export default MapComponent;
