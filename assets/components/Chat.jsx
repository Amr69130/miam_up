import React, { useState, useEffect, useRef } from 'react';

const Chat = ({ idAmi, prenomAmi }) => {
    const [messages, setMessages] = useState([]);
    const [nouveauMessage, setNouveauMessage] = useState('');
    const [loading, setLoading] = useState(true);
    const messagesEndRef = useRef(null);

    // 1. Fonction pour charger les messages (GET)
    const fetchMessages = async () => {
        try {
            const response = await fetch(`/api/chat/messages/${idAmi}`);
            if (!response.ok) throw new Error('Erreur réseau');
            const data = await response.json();
            setMessages(data);
            setLoading(false);
        } catch (error) {
            console.error("Erreur lors du chargement:", error);
            setLoading(false);
        }
    };

    // 2. Fonction pour envoyer un message (POST)
    const handleSend = async (e) => {
        e.preventDefault();

        // On n'envoie pas de message vide
        if (!nouveauMessage.trim()) return;

        try {
            const response = await fetch(`/api/chat/send/${idAmi}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ contenu: nouveauMessage })
            });

            if (response.ok) {
                setNouveauMessage(''); // On vide le champ
                fetchMessages();       // On rafraîchit la liste immédiatement
            } else {
                const errorData = await response.json();
                alert(errorData.error || "Erreur lors de l'envoi");
            }
        } catch (error) {
            console.error("Erreur envoi:", error);
        }
    };

    // Scroll automatique vers le bas à chaque nouveau message
    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
    }, [messages]);

    // Chargement initial et rafraîchissement automatique toutes les 4 secondes
    useEffect(() => {
        fetchMessages();
        const interval = setInterval(fetchMessages, 4000);
        return () => clearInterval(interval); // Nettoyage quand on ferme le chat
    }, [idAmi]);

    if (loading) return <div className="p-4 text-center">Chargement du chat...</div>;

    return (
        <div className="flex flex-col h-[450px] w-full border rounded-lg bg-white shadow-lg overflow-hidden">
            {/* Header */}
            <div className="p-3 bg-indigo-600 text-white font-bold flex justify-between items-center">
                <span>Chat avec {prenomAmi}</span>
                <span className="text-xs bg-indigo-500 px-2 py-1 rounded">En ligne</span>
            </div>

            {/* Zone des messages */}
            <div className="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
                {messages.length === 0 ? (
                    <p className="text-center text-gray-400 mt-10">Aucun message. Dites bonjour !</p>
                ) : (
                    messages.map((msg) => {
                        // On définit si le message vient de nous ou de l'ami
                        // Si l'expediteurId est égal à l'id de l'ami, c'est un message REÇU
                        const isReceived = msg.expediteurId === parseInt(idAmi);

                        return (
                            <div key={msg.id} className={`flex ${isReceived ? 'justify-start' : 'justify-end'}`}>
                                <div className={`max-w-[75%] p-3 rounded-2xl shadow-sm ${
                                    isReceived
                                        ? 'bg-white text-gray-800 rounded-tl-none border'
                                        : 'bg-indigo-500 text-white rounded-tr-none'
                                }`}>
                                    <p className="text-sm leading-relaxed">{msg.contenu}</p>
                                    <p className={`text-[10px] mt-1 text-right ${isReceived ? 'text-gray-400' : 'text-indigo-100'}`}>
                                        {msg.date}
                                    </p>
                                </div>
                            </div>
                        );
                    })
                )}
                <div ref={messagesEndRef} />
            </div>

            {/* Formulaire d'envoi */}
            <form onSubmit={handleSend} className="p-3 bg-white border-t flex gap-2">
                <input
                    type="text"
                    value={nouveauMessage}
                    onChange={(e) => setNouveauMessage(e.target.value)}
                    placeholder="Écrivez votre message..."
                    className="flex-1 border rounded-full px-4 py-2 outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                />
                <button
                    type="submit"
                    className="bg-indigo-600 text-white p-2 rounded-full hover:bg-indigo-700 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                    </svg>
                </button>
            </form>
        </div>
    );
};

export default Chat;
