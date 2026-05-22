import React, { useState, useEffect, useRef } from 'react';

const Chat = ({ idAmi, prenomAmi }) => {
    const [messages, setMessages] = useState([]);
    const [nouveauMessage, setNouveauMessage] = useState('');
    const [loading, setLoading] = useState(true);
    const [uploading, setUploading] = useState(false);
    const [pastePreview, setPastePreview] = useState(null); // { file, url }
    const messagesEndRef = useRef(null);
    const fileInputRef = useRef(null);

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

    // Fonction centrale d'envoi d'un fichier (utilisée par input ET paste)
    const uploadFile = async (file, onDone) => {
        setUploading(true);
        const formData = new FormData();
        formData.append('fichier', file);

        try {
            const response = await fetch(`/api/chat/upload/${idAmi}`, {
                method: 'POST',
                body: formData,
            });
            if (response.ok) {
                fetchMessages();
            } else {
                let errMsg = 'Erreur lors de l\'envoi du fichier';
                try { const data = await response.json(); errMsg = data.error || errMsg; } catch (_) {}
                alert(errMsg);
            }
        } catch (error) {
            console.error("Erreur upload:", error);
            alert('Impossible d\'envoyer le fichier. Vérifiez votre connexion.');
        } finally {
            setUploading(false);
            if (onDone) onDone();
        }
    };

    // Envoyer un fichier via le sélecteur
    const handleFileUpload = async (e) => {
        const file = e.target.files[0];
        if (!file) return;
        await uploadFile(file, () => { e.target.value = ''; });
    };

    // Coller une image depuis le presse-papier (Ctrl+V)
    const handlePaste = (e) => {
        const items = e.clipboardData?.items;
        if (!items) return;
        for (const item of items) {
            if (item.type.startsWith('image/')) {
                e.preventDefault();
                const file = item.getAsFile();
                if (!file) return;
                const previewUrl = URL.createObjectURL(file);
                setPastePreview({ file, url: previewUrl });
                return;
            }
        }
    };

    // Confirmer l'envoi de l'image collée
    const confirmPasteSend = async () => {
        if (!pastePreview) return;
        const file = pastePreview.file;
        URL.revokeObjectURL(pastePreview.url);
        setPastePreview(null);
        await uploadFile(file);
    };

    const cancelPaste = () => {
        if (pastePreview) URL.revokeObjectURL(pastePreview.url);
        setPastePreview(null);
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
                                        : 'bg-[#00A9A5] text-white rounded-tr-none shadow-[#00A9A5]/20'
                                }`}>
                                    {/* Affichage image */}
                                    {msg.fichierType === 'image' && msg.fichier && (
                                        <a href={msg.fichier} target="_blank" rel="noopener noreferrer">
                                            <img
                                                src={msg.fichier}
                                                alt="Image partagée"
                                                className="max-w-full rounded-xl mb-1 max-h-48 object-cover cursor-pointer hover:opacity-90 transition-opacity"
                                            />
                                        </a>
                                    )}
                                    {/* Affichage fichier */}
                                    {msg.fichierType === 'file' && msg.fichier && (
                                        <a
                                            href={msg.fichier}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className={`flex items-center gap-2 text-sm mb-1 underline ${isReceived ? 'text-[#00A9A5]' : 'text-white/90'}`}
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            {msg.contenu.replace('📎 ', '')}
                                        </a>
                                    )}
                                    {/* Texte normal (pas un fichier) */}
                                    {!msg.fichierType && (
                                        <p className="text-sm leading-relaxed">{msg.contenu}</p>
                                    )}
                                    <p className={`text-[9px] mt-1 text-right font-medium uppercase tracking-tighter ${isReceived ? 'text-gray-400' : 'text-white/60'}`}>
                                        {msg.date}
                                    </p>
                                </div>
                            </div>
                        );
                    })
                )}
                <div ref={messagesEndRef} />
            </div>

            {/* Aperçu image collée (Ctrl+V) */}
            {pastePreview && (
                <div className="px-4 py-3 bg-yellow-50 border-t border-yellow-200 flex items-center gap-3 shrink-0">
                    <img src={pastePreview.url} alt="Aperçu" className="h-16 w-16 object-cover rounded-lg border border-yellow-300" />
                    <div className="flex-1 text-sm text-yellow-800">Envoyer cette image ?</div>
                    <button
                        type="button"
                        onClick={confirmPasteSend}
                        disabled={uploading}
                        className="bg-[#00A9A5] text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-[#008F8C] disabled:opacity-50 transition-colors"
                    >Envoyer</button>
                    <button
                        type="button"
                        onClick={cancelPaste}
                        className="text-gray-400 hover:text-gray-600 px-2 py-1.5 text-xs transition-colors"
                    >Annuler</button>
                </div>
            )}

            {/* Formulaire d'envoi - Fixe en bas */}
            <form onSubmit={handleSend} className="p-4 bg-white border-t border-gray-100 flex gap-2 shrink-0 items-center">
                {/* Bouton upload fichier */}
                <input
                    ref={fileInputRef}
                    type="file"
                    accept="image/*,.pdf,.txt"
                    className="hidden"
                    onChange={handleFileUpload}
                />
                <button
                    type="button"
                    onClick={() => fileInputRef.current?.click()}
                    disabled={uploading}
                    title="Envoyer une image ou un fichier"
                    className="text-[#00A9A5] p-2.5 rounded-xl hover:bg-[#84DCCF]/20 transition-colors flex-shrink-0 disabled:opacity-50"
                >
                    {uploading ? (
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    ) : (
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                    )}
                </button>

                <input
                    type="text"
                    value={nouveauMessage}
                    onChange={(e) => setNouveauMessage(e.target.value)}
                    onPaste={handlePaste}
                    placeholder="Écrivez votre message... (Ctrl+V pour coller une image)"
                    className="flex-1 bg-[#F8F8F8] border border-gray-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-[#00A9A5] focus:border-transparent text-sm transition-all"
                />
                <button
                    type="submit"
                    className="bg-[#00A9A5] text-white p-3 rounded-xl hover:bg-[#008F8C] transition-transform active:scale-95 shadow-lg shadow-[#00A9A5]/20 flex-shrink-0"
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
