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
}
