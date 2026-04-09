<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EvenementController extends AbstractController
{
    #[Route('/creer-mon-event', name: 'app_evenement_test')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Optionnel : On peut quand même noter QUI a créé l'événement
            // si tu as un champ 'createur' dans ton entité.

            $em->persist($evenement);
            $em->flush();

            $this->addFlash('success', 'Événement ajouté sur la carte !');
            return $this->redirectToRoute('app_carte');
        }

        return $this->render('evenement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/event/join/{id}', name: 'app_event_join')]
    public function joinEvent(Evenement $event, EntityManagerInterface $entityManager): Response
    {
        // 1. Sécurité : Bloquer l'action si l'utilisateur n'est pas connecté
        $user = $this->getUser();
        
        if (!$user) {
            // Redirection vers la page de connexion
            $this->addFlash('danger', 'Vous devez être connecté pour rejoindre un événement.');
            return $this->redirectToRoute('app_login');
        }

        // 2. Vérification d'Unicité (avec la VRAIE relation de Ghouti)
        if ($event->getUtilisateurs()->contains($user)) {
            // Si déjà inscrit -> on le désinscrit (toggle)
            $event->removeUtilisateur($user);
            $this->addFlash('warning', 'Vous êtes désinscrit de l\'événement.');
        } else {
            // Si nouveau -> on l'inscrit
            $event->addUtilisateur($user);
            $this->addFlash('success', 'Vous êtes bien inscrit à l\'événement !');
        }

        // 3. Persistance : On applique les changements dans la base de données
        $entityManager->flush();

        // 4. Redirection vers la carte
        return $this->redirectToRoute('app_carte'); 
    }
}
