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
    #[IsGranted('ROLE_USER')] // Seuls les connectés passent ici
    public function ajouter(
        Utilisateur $utilisateur_receveur,
        AmitieRepository $amitieRepo,
        EntityManagerInterface $em
    ): Response {
        $utilisateur_demandeur = $this->getUser();

        // 1. Sécurité : on ne s'ajoute pas soi-même
        if ($utilisateur_demandeur === $utilisateur_receveur) {
            $this->addFlash('danger', 'Vous ne pouvez pas vous ajouter vous-même.');
            return $this->redirectToRoute('app_home');
        }

        // 2. Contrôle des doublons : existe-t-il déjà une relation entre ces deux-là ?
        // On cherche si A a demandé B OU si B a demandé A
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

        // 3. Création de la demande
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
        $user = $this->getUser();

        // 1. Demandes que j'ai REÇUES (en attente)
        $demandesRecues = $amitieRepository->findBy([
            'utilisateur_receveur' => $user,
            'statut' => 'en_attente'
        ]);

        // 2. Demandes que j'ai FAITES (en attente) --- NOUVEAU ---
        $demandesEnvoyees = $amitieRepository->findBy([
            'utilisateur_demandeur' => $user,
            'statut' => 'en_attente'
        ]);

        // 3. Mes amis (acceptés)
        $amis = $amitieRepository->createQueryBuilder('a')
            ->where('(a.utilisateur_demandeur = :user OR a.utilisateur_receveur = :user)')
            ->andWhere('a.statut = :statut')
            ->setParameter('user', $user)
            ->setParameter('statut', 'accepte')
            ->getQuery()
            ->getResult();

        return $this->render('ami/index.html.twig', [
            'demandes_recues' => $demandesRecues,
            'demandes_envoyees' => $demandesEnvoyees, // On l'envoie à la vue
            'amis' => $amis,
        ]);
    }

    #[Route('/ami/accept/{id}', name: 'app_ami_accept')]
    public function accept(Amitie $amitie, EntityManagerInterface $em): Response
    {
        // Sécurité : Seul le receveur peut accepter
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
        // Sécurité : Seul le receveur peut refuser
        if ($amitie->getUtilisateurReceveur() !== $this->getUser()) {
            $this->addFlash('danger', "Action non autorisée.");
            return $this->redirectToRoute('app_ami_list');
        }

        // On supprime carrément la ligne pour permettre de redemander plus tard
        $em->remove($amitie);
        $em->flush();

        $this->addFlash('secondary', "Demande refusée.");

        return $this->redirectToRoute('app_ami_list');
    }
}
