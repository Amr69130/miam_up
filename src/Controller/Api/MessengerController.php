<?php

namespace App\Controller\Api;

use App\Entity\Message;
use App\Entity\Conversation;
use App\Repository\UtilisateurRepository;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route; // <--- Import important pour PHP 8.x

class MessengerController extends AbstractController
{
    /**
     * Envoyer un message à un ami
     */
    #[Route('/api/chat/send/{idAmi}', name: 'api_chat_send', methods: ['POST'])]
    public function sendMessage(
        int $idAmi,
        Request $request,
        UtilisateurRepository $userRepo,
        ConversationRepository $convRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        $ami = $userRepo->find($idAmi);
        $data = json_decode($request->getContent(), true);

        if (!$user) return new JsonResponse(['error' => 'Non connecté'], 401);
        if (!$ami || !isset($data['contenu'])) return new JsonResponse(['error' => 'Données invalides'], 400);

        // 1. Trouver ou créer la conversation
        $conversation = $convRepo->findConversationBetweenTwoUsers($user->getId(), $ami->getId());

        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->addMembre($user);
            $conversation->addMembre($ami);
            $conversation->setNomConversation('Chat ' . $user->getPrenomUtilisateur() . ' / ' . $ami->getPrenomUtilisateur());
            $em->persist($conversation);
        }

        // 2. Créer le message
        $message = new Message();
        $message->setContenu($data['contenu']);
        $message->setExpediteur($user);
        $message->setConversation($conversation);
        $message->setDateMessage(new \DateTime()); // Assure-toi que la date est mise
        $message->setStatusMessage('envoyé');

        $em->persist($message);
        $em->flush();

        return new JsonResponse(['status' => 'Message envoyé', 'conversationId' => $conversation->getId()], 201);
    }

    /**
     * Récupérer les messages d'une conversation avec un ami
     */
    #[Route('/api/chat/messages/{idAmi}', name: 'api_chat_get_messages', methods: ['GET'])]
    public function getMessages(
        int $idAmi,
        ConversationRepository $convRepo
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) return new JsonResponse(['error' => 'Non connecté'], 401);

        $conversation = $convRepo->findConversationBetweenTwoUsers($user->getId(), $idAmi);

        if (!$conversation) {
            return new JsonResponse([], 200);
        }

        $messagesData = [];
        foreach ($conversation->getMessages() as $msg) {
            $messagesData[] = [
                'id' => $msg->getId(),
                'contenu' => $msg->getContenu(),
                'date' => $msg->getDateMessage()->format('d/m H:i'),
                'expediteurId' => $msg->getExpediteur()->getId(),
                'status' => $msg->getStatusMessage()
            ];
        }

        return new JsonResponse($messagesData, 200);
    }
}
