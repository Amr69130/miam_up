import React, { useState, useEffect, useRef } from 'react';

const Chat = ({ idAmi, prenomAmi }) => {
    const [messages, setMessages] = useState([]);
    const [nouveauMessage, setNouveauMessage] = useState('');
    const [loading, setLoading] = useState(true);
    const messagesEndRef = useRef(null);

    // Marquer les messages comme lus
    const markAsRead = async () => {
        try {
            await fetch(`/api/chat/read/${idAmi}`, { method: 'POST' });
            if (window.updateUnreadBadge) {
                window.updateUnreadBadge();
            }
        } catch (error) {
            console.error("Erreur marquage lu:", error);
        }
    };

    // Charger les messages
    const fetchMessages = async () => {
        try {
            const response = await fetch(`/api/chat/messages/${idAmi}`);
            if (!response.ok) throw new Error('Erreur réseau');
            const data = await response.json();

            if (data.length > messages.length) {
                const dernierMessage = data[data.length - 1];
                if (dernierMessage.expediteurId === parseInt(idAmi)) {
                    markAsRead();
                }
            }

            setMessages(data);
            setLoading(false);
        } catch (error) {
            console.error("Erreur chargement:", error);
            setLoading(false);
        }
    };

    // Envoyer un message
    const handleSend = async (e) => {
        e.preventDefault();
        if (!nouveauMessage.trim()) return;

        try {
            const response = await fetch(`/api/chat/send/${idAmi}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ contenu: nouveauMessage })
            });

            if (response.ok) {
                setNouveauMessage('');
                fetchMessages();
            }
        } catch (error) {
            console.error("Erreur envoi:", error);
        }
    };

    // Scroll automatique
    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
    }, [messages]);

    // Cycle de vie : chargement et intervalle
    useEffect(() => {
        setLoading(true); // Reset loading quand on change d'ami
        fetchMessages();
        markAsRead();

        const interval = setInterval(fetchMessages, 4000);
        return () => clearInterval(interval);
    }, [idAmi]);

    if (loading) return (
        <div className="flex items-center justify-center h-full bg-gray-50">
            <div className="text-indigo-500 font-medium animate-pulse">Chargement de la conversation...</div>
        </div>
    );

    return (
        /* Changement majeur : h-full, w-full et on vire les shadow/border inutiles ici */
        <div className="flex flex-col h-full w-full bg-white overflow-hidden">

            {/* Header interne au chat (optionnel car déjà un peu géré par le Twig, mais utile pour le nom) */}
            <div className="px-6 py-4 border-b bg-white flex justify-between items-center shrink-0">
                <div>
                    <h3 className="font-bold text-gray-800">{prenomAmi}</h3>
                    <div className="flex items-center gap-1.5">
                        <span className="w-2 h-2 bg-green-500 rounded-full"></span>
                        <span className="text-[10px] text-gray-400 uppercase font-bold tracking-widest">En ligne</span>
                    </div>
                </div>
            </div>

            {/* Zone des messages - Scrollable */}
            <div className="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50/30">
                {messages.length === 0 ? (
                    <div className="flex flex-col items-center justify-center h-full opacity-40">
                        <p className="text-sm italic">Aucun message avec {prenomAmi}.</p>
                    </div>
                ) : (
                    messages.map((msg) => {
                        const isReceived = msg.expediteurId === parseInt(idAmi);
                        return (
                            <div key={msg.id} className={`flex ${isReceived ? 'justify-start' : 'justify-end'}`}>
                                <div className={`max-w-[80%] px-4 py-2.5 rounded-2xl shadow-sm ${
                                    isReceived
                                        ? 'bg-white text-gray-800 rounded-tl-none border border-gray-100'
                                        : 'bg-indigo-600 text-white rounded-tr-none shadow-indigo-100'
                                }`}>
                                    <p className="text-sm leading-relaxed">{msg.contenu}</p>
                                    <p className={`text-[9px] mt-1 text-right font-medium uppercase tracking-tighter ${isReceived ? 'text-gray-400' : 'text-indigo-200'}`}>
                                        {msg.date}
                                    </p>
                                </div>
                            </div>
                        );
                    })
                )}
                <div ref={messagesEndRef} />
            </div>

            {/* Formulaire d'envoi - Fixe en bas */}
            <form onSubmit={handleSend} className="p-4 bg-white border-t border-gray-100 flex gap-2 shrink-0">
                <input
                    type="text"
                    value={nouveauMessage}
                    onChange={(e) => setNouveauMessage(e.target.value)}
                    placeholder="Écrivez votre message..."
                    className="flex-1 bg-gray-100 border-none rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all"
                />
                <button
                    type="submit"
                    className="bg-indigo-600 text-white p-3 rounded-xl hover:bg-indigo-700 transition-transform active:scale-95 shadow-lg shadow-indigo-100"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 rotate-90" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                    </svg>
                </button>
            </form>
        </div>
    );
};

export default Chat;
