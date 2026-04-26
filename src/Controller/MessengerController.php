<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\AmitieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MessengerController extends AbstractController
{
    #[Route('/messages/{id}', name: 'app_messenger_chat')]
    public function chat(Utilisateur $ami, AmitieRepository $amitieRepository): Response
    {
        $user = $this->getUser();

        // On récupère tous tes amis acceptés pour la liste de gauche
        $amisRelations = $amitieRepository->createQueryBuilder('a')
            ->where('(a.utilisateur_demandeur = :user OR a.utilisateur_receveur = :user)')
            ->andWhere('a.statut = :statut')
            ->setParameter('user', $user)
            ->setParameter('statut', 'accepte')
            ->getQuery()
            ->getResult();

        // On transforme les relations Amitie en liste d'objets Utilisateur (tes amis)
        $listeAmis = [];
        foreach ($amisRelations as $rel) {
            $listeAmis[] = ($rel->getUtilisateurDemandeur() === $user)
                ? $rel->getUtilisateurReceveur()
                : $rel->getUtilisateurDemandeur();
        }

        return $this->render('messenger/chat.html.twig', [
            'ami_selectionne' => $ami,
            'liste_amis' => $listeAmis,
        ]);
    }
}
