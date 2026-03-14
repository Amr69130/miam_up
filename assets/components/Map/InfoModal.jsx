import React from 'react';

const InfoModal = ({ isOpen, onClose }) => {
    if (!isOpen) return null;

    return (
        <div style={{
            position: 'fixed', top: 0, left: 0, width: '100%', height: '100%',
            backgroundColor: 'rgba(0,0,0,0.7)', zIndex: 9999,
            display: 'flex', justifyContent: 'center', alignItems: 'center', padding: '20px'
        }}>
            <div style={{
                backgroundColor: 'white', padding: '30px', borderRadius: '15px',
                maxWidth: '500px', position: 'relative', textAlign: 'center'
            }}>
                <button onClick={onClose} style={{ position: 'absolute', top: '10px', right: '15px', border: 'none', background: 'none', fontSize: '20px', cursor: 'pointer' }}>✕</button>

                <h2 className="text-2xl font-bold mb-4">Bienvenue sur Miam-Up !</h2>
                <p className="mb-4 text-gray-700">L'application conçue pour créer vos événements dans les restos proches ou plus éloignés. Si dans la bande de potes l'organisateur n'est pas vous ; PAS DE PANIQUE ! Vous pouvez rejoindre l'évenement en un simple clic ! Aussi vous pouvez rejoindre des événements "open" dans lesquels se faire des nouveaux amis.</p>

                <h3 className="font-bold mb-2">Comprendre la carte</h3>
                <ul style={{ textAlign: 'left', listStyle: 'none', padding: 0 }} className="mb-4">
                    <li>⚪ <b>Marqueur gris :</b> Restaurant dans lequel vous pouvez créer un événement</li>
                    <li>🟢 <b>Marqueur vert :</b> Restaurant dans lequel il y a un événement aujourd'hui !</li>
                    <li>🔵 <b>Marqueur Bleu :</b> Restaurant dans lequel un événement est programmé dans les jours à venir.</li>
                </ul>

                <p className="italic text-sm text-gray-500 mb-6">"Cliquez sur un marqueur pour voir les détails, la distance exacte et l'itinéraire. Bonne dégustation !"</p>

                <button onClick={onClose} className="bg-indigo-600 text-white px-6 py-2 rounded-full font-bold hover:bg-indigo-700 transition">
                    C'est parti !
                </button>
            </div>
        </div>
    );
};

export default InfoModal;
