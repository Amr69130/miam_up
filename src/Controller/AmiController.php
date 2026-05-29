<?php

namespace App\Controller;

use App\Entity\Amitie;
use App\Entity\Utilisateur;
use App\Repository\AmitieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AmiController extends AbstractController
{
    #[Route('/ami/add/{id}', name: 'app_ami_add')]
    #[IsGranted('ROLE_USER')]
    public function ajouter(Utilisateur $utilisateur_receveur, AmitieRepository $amitieRepo, EntityManagerInterface $em): Response
    {
        $utilisateur_demandeur = $this->getUser();

        if ($utilisateur_demandeur === $utilisateur_receveur) {
            $this->addFlash('danger', 'Vous ne pouvez pas vous ajouter vous-même.');
            return $this->redirectToRoute('app_home');
        }

        $dejaExistant = $amitieRepo->findOneBy([
            'utilisateur_demandeur' => $utilisateur_demandeur,
            'utilisateur_receveur' => $utilisateur_receveur
        ]);

        $inverseExistant = $amitieRepo->findOneBy([
            'utilisateur_demandeur' => $utilisateur_receveur,
            'utilisateur_receveur' => $utilisateur_demandeur
        ]);

        if ($dejaExistant || $inverseExistant) {
            $this->addFlash('warning', 'Une demande existe déjà ou vous êtes déjà amis.');
            return $this->redirectToRoute('app_home');
        }

        $amitie = new Amitie();
        $amitie->setUtilisateurDemandeur($utilisateur_demandeur);
        $amitie->setUtilisateurReceveur($utilisateur_receveur);

        $em->persist($amitie);
        $em->flush();

        $this->addFlash('success', 'Demande envoyée !');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/profil/amis', name: 'app_ami_list')]
    public function list(AmitieRepository $amitieRepository): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // 1. Récupérer les Groupes (Conversations d'événements)
        $conversations = $user->getConversations();

        // 2. Récupérer les Relations d'Amitié acceptées
        $amisRelations = $amitieRepository->createQueryBuilder('a')
            ->where('(a.utilisateur_demandeur = :user OR a.utilisateur_receveur = :user)')
            ->andWhere('a.statut = :statut')
            ->setParameter('user', $user)
            ->setParameter('statut', 'accepte')
            ->getQuery()
            ->getResult();

        // On transforme les relations en liste d'utilisateurs pour faciliter le Twig
        $listeAmis = [];
        foreach ($amisRelations as $rel) {
            $listeAmis[] = ($rel->getUtilisateurDemandeur() === $user)
                ? $rel->getUtilisateurReceveur()
                : $rel->getUtilisateurDemandeur();
        }

        // 3. Demandes en attente
        $demandesRecues = $amitieRepository->findBy(['utilisateur_receveur' => $user, 'statut' => 'en_attente']);
        $demandesEnvoyees = $amitieRepository->findBy(['utilisateur_demandeur' => $user, 'statut' => 'en_attente']);

        return $this->render('ami/index.html.twig', [
            'conversations' => $conversations,
            'amis' => $listeAmis,
            'demandes_recues' => $demandesRecues,
            'demandes_envoyees' => $demandesEnvoyees,
        ]);
    }

    #[Route('/ami/accept/{id}', name: 'app_ami_accept')]
    public function accept(Amitie $amitie, EntityManagerInterface $em): Response
    {
        if ($amitie->getUtilisateurReceveur() !== $this->getUser()) {
            $this->addFlash('danger', "Vous ne pouvez pas accepter cette demande.");
            return $this->redirectToRoute('app_ami_list');
        }

        $amitie->setStatut('accepte');
        $em->flush();

        $this->addFlash('success', "Vous êtes maintenant ami avec " . $amitie->getUtilisateurDemandeur()->getPrenomUtilisateur());
        return $this->redirectToRoute('app_ami_list');
    }

    #[Route('/ami/reject/{id}', name: 'app_ami_reject')]
    public function reject(Amitie $amitie, EntityManagerInterface $em): Response
    {
        if ($amitie->getUtilisateurReceveur() !== $this->getUser()) {
            $this->addFlash('danger', "Action non autorisée.");
            return $this->redirectToRoute('app_ami_list');
        }

        $em->remove($amitie);
        $em->flush();

        $this->addFlash('secondary', "Demande refusée.");
        return $this->redirectToRoute('app_ami_list');
    }
}
