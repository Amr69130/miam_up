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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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
                'status' => $msg->getStatusMessage(),
                'fichier' => $msg->getFichier(),
                'fichierType' => $msg->getFichierType(),
            ];
        }

        return new JsonResponse($messagesData, 200);
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

        // On nettoie les messages de l'autre
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

    /**
     * Upload d'un fichier ou d'une image dans une conversation
     */
    #[Route('/api/chat/upload/{idAmi}', name: 'api_chat_upload_file', methods: ['POST'])]
    public function uploadFile(
        int $idAmi,
        Request $request,
        UtilisateurRepository $userRepo,
        ConversationRepository $convRepo,
        EntityManagerInterface $em,
        ParameterBagInterface $params
    ): JsonResponse {
        $user = $this->getUser();
        $ami = $userRepo->find($idAmi);

        if (!$user) return new JsonResponse(['error' => 'Non connecté'], 401);
        if (!$ami) return new JsonResponse(['error' => 'Ami introuvable'], 404);

        $file = $request->files->get('fichier');
        if (!$file) return new JsonResponse(['error' => 'Aucun fichier reçu'], 400);

        // Validation MIME et taille
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'text/plain'];
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $allowedMimes, true)) {
            return new JsonResponse(['error' => 'Type de fichier non autorisé'], 400);
        }
        if ($file->getSize() > 10 * 1024 * 1024) {
            return new JsonResponse(['error' => 'Fichier trop volumineux (max 10 Mo)'], 400);
        }

        $isImage = str_starts_with($mimeType, 'image/');
        $uploadDir = $params->get('kernel.project_dir') . '/public/uploads/messages';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $newFilename = $safeName . '_' . uniqid() . '.' . $file->guessExtension();
        $file->move($uploadDir, $newFilename);

        // Trouver ou créer la conversation
        $conversation = $convRepo->findConversationBetweenTwoUsers($user->getId(), $ami->getId());
        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->addMembre($user);
            $conversation->addMembre($ami);
            $conversation->setNomConversation('Chat ' . $user->getPrenomUtilisateur() . ' / ' . $ami->getPrenomUtilisateur());
            $em->persist($conversation);
        }

        $message = new Message();
        $message->setContenu($isImage ? '📷 Image' : '📎 ' . $file->getClientOriginalName());
        $message->setFichier('/uploads/messages/' . $newFilename);
        $message->setFichierType($isImage ? 'image' : 'file');
        $message->setExpediteur($user);
        $message->setConversation($conversation);
        $message->setDateMessage(new \DateTime());
        $message->setStatusMessage('envoyé');

        $em->persist($message);
        $em->flush();

        return new JsonResponse([
            'status' => 'Fichier envoyé',
            'fichier' => '/uploads/messages/' . $newFilename,
            'fichierType' => $isImage ? 'image' : 'file',
        ], 201);
    }
}
