<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\Conversation;
use App\Form\EvenementType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EvenementController extends AbstractController
{
    #[Route('/evenement/nouveau', name: 'app_evenement_test')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            // 1. Configurer l'événement
            $evenement->setCreateur($user);
            $evenement->addUtilisateur($user);

            // 2. Créer la conversation
            $conversation = new Conversation();
            $conversation->setNomConversation('Groupe : ' . $evenement->getTitre());

            // 3. Lier les deux (Double liaison pour être sûr)
            $conversation->setEvenement($evenement);
            $evenement->setConversation($conversation);

            // 4. Ajouter le créateur comme membre du chat
            $conversation->addMembre($user);

            // 5. Persister les deux objets
            $em->persist($evenement);
            $em->persist($conversation);

            // 6. Sauvegarder en base de données
            $em->flush();

            $this->addFlash('success', 'Événement et groupe de discussion créés avec succès !');
            return $this->redirectToRoute('app_map');
        }

        return $this->render('evenement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/event/join/{id}', name: 'app_event_join')]
    public function joinEvent(Evenement $event, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_utilisateur_login');
        }

        $conversation = $event->getConversation();

        if ($event->getUtilisateurs()->contains($user)) {
            $event->removeUtilisateur($user);
            if ($conversation) {
                $conversation->removeMembre($user);
            }
            $this->addFlash('warning', 'Vous avez quitté l\'événement.');
        } else {
            $event->addUtilisateur($user);
            if ($conversation) {
                $conversation->addMembre($user);
            }
            $this->addFlash('success', 'Vous avez rejoint l\'événement et son groupe !');
        }

        $em->flush();
        return $this->redirectToRoute('app_map');
    }
}
