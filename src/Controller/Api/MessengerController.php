<?php

namespace App\Controller\Api;

use App\Entity\Message;
use App\Entity\Conversation;
use App\Repository\UtilisateurRepository;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class MessengerController extends AbstractController
{
    /**
     * Envoyer un message à un ami (Chat Privé)
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

        $conversation = $convRepo->findConversationBetweenTwoUsers($user->getId(), $ami->getId());

        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->addMembre($user);
            $conversation->addMembre($ami);
            $conversation->setNomConversation('Chat ' . $user->getPrenomUtilisateur() . ' / ' . $ami->getPrenomUtilisateur());
            $em->persist($conversation);
        }

        $message = new Message();
        $message->setContenu($data['contenu']);
        $message->setExpediteur($user);
        $message->setConversation($conversation);
        $message->setDateMessage(new \DateTime());
        $message->setStatusMessage('envoyé');

        $em->persist($message);
        $em->flush();

        return new JsonResponse(['status' => 'Message envoyé', 'conversationId' => $conversation->getId()], 201);
    }

    /**
     * Récupérer les messages d'une conversation avec un ami (Chat Privé)
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

    /**
     * NOUVEAU : Récupérer les messages d'un GROUPE (Via ID de Conversation)
     */
    #[Route('/api/chat/group/messages/{id}', name: 'api_chat_group_messages', methods: ['GET'])]
    public function getGroupMessages(Conversation $conversation): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) return new JsonResponse(['error' => 'Non connecté'], 401);

        // Sécurité : On vérifie si l'utilisateur fait partie des membres du groupe
        if (!$conversation->getMembres()->contains($user)) {
            return new JsonResponse(['error' => "Vous n'avez pas accès à cette conversation"], 403);
        }

        $messagesData = [];
        foreach ($conversation->getMessages() as $msg) {
            $messagesData[] = [
                'id' => $msg->getId(),
                'contenu' => $msg->getContenu(),
                'date' => $msg->getDateMessage()->format('d/m H:i'),
                'expediteurId' => $msg->getExpediteur()->getId(),
                'expediteurNom' => $msg->getExpediteur()->getPrenomUtilisateur(),
                'status' => $msg->getStatusMessage()
            ];
        }

        return new JsonResponse($messagesData, 200);
    }

    /**
     * NOUVEAU : Envoyer un message dans un GROUPE
     */
    #[Route('/api/chat/group/send/{id}', name: 'api_chat_group_send', methods: ['POST'])]
    public function sendGroupMessage(
        Conversation $conversation,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (!$user) return new JsonResponse(['error' => 'Non connecté'], 401);
        if (!isset($data['contenu'])) return new JsonResponse(['error' => 'Contenu manquant'], 400);

        // Sécurité : On vérifie l'appartenance au groupe
        if (!$conversation->getMembres()->contains($user)) {
            return new JsonResponse(['error' => 'Action non autorisée'], 403);
        }

        $message = new Message();
        $message->setContenu($data['contenu']);
        $message->setExpediteur($user);
        $message->setConversation($conversation);
        $message->setDateMessage(new \DateTime());
        $message->setStatusMessage('envoyé');

        $em->persist($message);
        $em->flush();

        return new JsonResponse(['status' => 'Message envoyé'], 201);
    }

    /**
     * Compter les messages non lus globalement
     */
    #[Route('/api/chat/unread-count', name: 'api_chat_unread_count', methods: ['GET'])]
    public function getUnreadCount(MessageRepository $messageRepository): JsonResponse
    {
        $moi = $this->getUser();
        if (!$moi) return $this->json(['unreadCount' => 0]);

        $monId = $moi->getId();

        $count = $messageRepository->createQueryBuilder('m')
            ->select('count(m.id)')
            ->join('m.conversation', 'c')
            ->join('c.membres', 'u')
            ->where('u.id = :monId')
            ->andWhere('m.expediteur != :monId')
            ->andWhere('m.status_message LIKE :statut')
            ->setParameter('monId', $monId)
            ->setParameter('statut', 'envoy%')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->json([
            'unreadCount' => (int) $count
        ]);
    }

    /**
     * Marquer comme lu via l'ID de l'ami
     */
    #[Route('/api/chat/read/{idAmi}', name: 'api_chat_mark_read', methods: ['POST'])]
    public function markAsRead(
        int $idAmi,
        UtilisateurRepository $utilisateurRepository,
        ConversationRepository $conversationRepository,
        MessageRepository $messageRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $moi = $this->getUser();
        if (!$moi) return $this->json(['error' => 'Non connecté'], 403);

        $ami = $utilisateurRepository->find($idAmi);
        if (!$ami) return $this->json(['error' => 'Ami non trouvé'], 404);

        // On cherche la conversation commune
        $conversations = $conversationRepository->findAll();
        $maConversation = null;

        foreach ($conversations as $conv) {
            if ($conv->getMembres()->contains($moi) && $conv->getMembres()->contains($ami)) {
                $maConversation = $conv;
                break;
            }
        }

        if (!$maConversation) {
            return $this->json(['success' => true, 'message' => 'Aucune conversation trouvée']);
        }

        $messagesANettoyer = $messageRepository->createQueryBuilder('m')
            ->where('m.conversation = :conv')
            ->andWhere('m.expediteur != :moi')
            ->andWhere('m.status_message LIKE :statut')
            ->setParameter('conv', $maConversation)
            ->setParameter('moi', $moi)
            ->setParameter('statut', 'envoy%')
            ->getQuery()
            ->getResult();

        foreach ($messagesANettoyer as $msg) {
            $msg->setStatusMessage('lu');
        }

        $em->flush();

        return $this->json([
            'success' => true,
            'nb_marques_lus' => count($messagesANettoyer)
        ]);
    }
}
