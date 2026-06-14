<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\Conversation;
use App\Form\EvenementType;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EvenementController extends AbstractController
{
    #[Route('/evenement/nouveau', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, RestaurantRepository $restaurantRepo): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_utilisateur_login');
        }

        // --- Traitement POST depuis la modale ---
        if ($request->isMethod('POST')) {
            $titre       = trim($request->request->get('titre', ''));
            $dateStr     = $request->request->get('date_evenement', '');
            $dateFin     = $request->request->get('date_fin', '');
            $description = $request->request->get('description_evenement', '');
            $places      = $request->request->get('nombre_places');
            $restaurantId = $request->request->get('restaurant');

            // Validation basique
            if (empty($titre) || strlen($titre) < 3) {
                $this->addFlash('danger', 'Le titre est obligatoire (min 3 caractères).');
                return $this->redirectToRoute('app_map');
            }
            if (empty($dateStr)) {
                $this->addFlash('danger', 'La date de l\'événement est obligatoire.');
                return $this->redirectToRoute('app_map');
            }

            // Créer l'événement
            $evenement = new Evenement();
            $evenement->setTitre($titre);
            $evenement->setDateEvenement(new \DateTime($dateStr));

            if (!empty($dateFin)) {
                $evenement->setDateFin(new \DateTime($dateFin));
            }
            if (!empty($description)) {
                $evenement->setDescriptionEvenement($description);
            }
            if (!empty($places)) {
                $evenement->setNombrePlaces((int) $places);
            }

            // Lier au restaurant
            if (!empty($restaurantId)) {
                $restaurant = $restaurantRepo->find($restaurantId);
                if ($restaurant) {
                    $evenement->setRestaurant($restaurant);
                }
            }

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

        // --- GET : ancien formulaire Symfony (fallback) ---
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $evenement->setCreateur($user);
            $evenement->addUtilisateur($user);

            $conversation = new Conversation();
            $conversation->setNomConversation('Groupe : ' . $evenement->getTitre());
            $conversation->setEvenement($evenement);
            $evenement->setConversation($conversation);
            $conversation->addMembre($user);

            $em->persist($evenement);
            $em->persist($conversation);
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
