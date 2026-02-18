import React from 'react';
import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import './MapComponents.css';
import L from 'leaflet';
import icon from 'leaflet/dist/images/marker-icon.png';
import iconShadow from 'leaflet/dist/images/marker-shadow.png';

let DefaultIcon = L.icon({
    iconUrl: icon,
    shadowUrl: iconShadow,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34]
});
L.Marker.prototype.options.icon = DefaultIcon;


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

            {magasins.map((magasin, index) => (
                <Marker 
                    key={index} 
                    position={[magasin.lat, magasin.lng]}
                >
                    <Popup>
                        <b>{magasin.nom}</b> <br />
                        {magasin.description} <br />
                        <a href={magasin.site_web} target="_blank" rel="noopener noreferrer">Site Web</a> <br />
                        Note: {magasin.note}
                        <img src={magasin.logo} alt={`${magasin.nom} logo`} style={{ width: '100px', marginTop: '10px' }} />
                    </Popup>
                </Marker>
            ))}

        </MapContainer>
    );
};

export default MapComponent;