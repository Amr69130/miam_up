import React, { useState, useEffect, useRef } from 'react';

const Chat = ({ idAmi, idGroupe, prenomAmi, nomGroupe }) => {
    const [messages, setMessages] = useState([]);
    const [nouveauMessage, setNouveauMessage] = useState('');
    const [loading, setLoading] = useState(true);

    const messagesEndRef = useRef(null);

    // --- LOGIQUE DES URLS ---
    const isGroup = idGroupe && idGroupe !== "" && idGroupe !== "null";

    const fetchUrl = isGroup
        ? `/api/chat/group/messages/${idGroupe}`
        : `/api/chat/messages/${idAmi}`;

    const sendUrl = isGroup
        ? `/api/chat/group/send/${idGroupe}`
        : `/api/chat/send/${idAmi}`;

    // Marquer les messages comme lus (Amis uniquement)
    const markAsRead = async () => {
        if (isGroup || !idAmi) return;
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
            if (!idAmi && !isGroup) return;

            const response = await fetch(fetchUrl);
            if (!response.ok) throw new Error('Erreur réseau');
            const data = await response.json();

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
            const response = await fetch(sendUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ contenu: nouveauMessage })
            });

            if (response.ok) {
                setNouveauMessage('');
                // On rafraîchit immédiatement pour voir son propre message
                fetchMessages();
            }
        } catch (error) {
            console.error("Erreur envoi:", error);
        }
    };

    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
    }, [messages]);

    useEffect(() => {
        setLoading(true);
        fetchMessages();
        if (!isGroup) markAsRead();

        const interval = setInterval(fetchMessages, 4000);
        return () => clearInterval(interval);
    }, [idAmi, idGroupe]);

    if (loading) return (
        <div className="flex items-center justify-center h-full bg-gray-50">
            <div className="text-indigo-500 font-medium animate-pulse">Chargement de la conversation...</div>
        </div>
    );

    return (
        <div className="flex flex-col h-full w-full bg-white overflow-hidden">
            {/* Header */}
            <div className="px-6 py-4 border-b bg-white flex justify-between items-center shrink-0">
                <div>
                    <h3 className="font-bold text-gray-800">
                        {isGroup ? `🏠 Groupe : ${nomGroupe}` : `👤 ${prenomAmi}`}
                    </h3>
                    <div className="flex items-center gap-1.5">
                        <span className={`w-2 h-2 ${isGroup ? 'bg-indigo-400' : 'bg-green-500'} rounded-full`}></span>
                        <span className="text-[10px] text-gray-400 uppercase font-bold tracking-widest">
                            {isGroup ? 'Chat Événement' : 'En ligne'}
                        </span>
                    </div>
                </div>
            </div>

            {/* Zone des messages */}
            <div className="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50/30">
                {messages.length === 0 ? (
                    <div className="flex flex-col items-center justify-center h-full opacity-40">
                        <p className="text-sm italic">Aucun message ici.</p>
                    </div>
                ) : (
                    messages.map((msg) => {
                        // LOGIQUE DE POSITIONNEMENT :
                        // Si c'est un chat privé : msg.expediteurId === idAmi => c'est l'autre (gauche)
                        // Si c'est un chat de groupe : Symfony doit renvoyer un flag 'isMine' ou on compare avec l'utilisateur
                        // Ici on part du principe que si l'ID expéditeur est celui de l'ami, c'est à gauche.
                        // Sinon, si on est en groupe, on regarde si le nom de l'expéditeur est différent du nôtre.

                        const isMe = isGroup
                            ? msg.isMine // Idéalement envoyé par ton API
                            : msg.expediteurId !== parseInt(idAmi);

                        return (
                            <div key={msg.id} className={`flex ${isMe ? 'justify-end' : 'justify-start'}`}>
                                <div className={`max-w-[80%] px-4 py-2.5 rounded-2xl shadow-sm ${
                                    isMe
                                        ? 'bg-indigo-600 text-white rounded-tr-none shadow-indigo-100'
                                        : 'bg-white text-gray-800 rounded-tl-none border border-gray-100'
                                }`}>
                                    {isGroup && !isMe && (
                                        <p className="text-[10px] font-bold mb-1 text-indigo-400">
                                            {msg.expediteurNom}
                                        </p>
                                    )}
                                    <p className="text-sm leading-relaxed">{msg.contenu}</p>
                                    <p className={`text-[9px] mt-1 text-right font-medium opacity-60`}>
                                        {msg.date}
                                    </p>
                                </div>
                            </div>
                        );
                    })
                )}
                <div ref={messagesEndRef} />
            </div>

            {/* Formulaire */}
            <form onSubmit={handleSend} className="p-4 bg-white border-t border-gray-100 flex gap-2 shrink-0">
                <input
                    type="text"
                    value={nouveauMessage}
                    onChange={(e) => setNouveauMessage(e.target.value)}
                    placeholder="Écrivez votre message..."
                    className="flex-1 bg-gray-100 border-none rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition-all"
                />
                <button type="submit" className="bg-indigo-600 text-white p-3 rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 rotate-90" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                    </svg>
                </button>
            </form>
        </div>
    );
};

export default Chat;
