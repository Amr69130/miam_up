<?php

namespace App\Controller;

use App\Repository\RestaurantRepository;
use App\Entity\Restaurant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

final class RestaurantController extends AbstractController
{
    #[Route('/restaurants', name: 'restaurant_list')]
    public function index(Request $request, RestaurantRepository $restaurantRepository): Response
    {
        $search = $request->query->get('q', '');

        if ($search !== '') {
            $restaurants = $restaurantRepository->searchByNameOrAddress($search);
        } else {
            $restaurants = $restaurantRepository->findAll();
        }

        usort($restaurants, fn($a, $b) => ($b->getNoteConsommateurs() ?? 0) <=> ($a->getNoteConsommateurs() ?? 0));

        return $this->render('restaurant/list.html.twig', [
            'restaurants' => $restaurants,
            'search'      => $search,
            'totalCount'  => count($restaurants),
        ]);
    }

    #[Route('/restaurant/{id}', name: 'restaurant_show')]
    public function show(int $id, RestaurantRepository $restaurantRepository): Response
    {
        // On récupère un restaurant spécifique par son ID (pour une page de détail)
        $restaurant = $restaurantRepository->find($id);

        if (!$restaurant) {
            throw $this->createNotFoundException("Le restaurant n'existe pas.");
        }

        return $this->render('restaurant/show.html.twig', [
            'restaurant' => $restaurant,
        ]);
    }

    // --- ROUTES DU PANNEAU RESTAURATEUR ---

    #[Route('/panneau', name: 'app_panneau_resto')]
    public function panneau(): Response
    {
        $user = $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');

        $restaurant = $user->getRestaurants()->first() ?: null;

        if (!$restaurant) {
            $this->addFlash('warning', "Vous n'avez pas de restaurant attribué.");
            return $this->redirectToRoute('app_home');
        }

        return $this->render('pannel_resto/mon_restaurant.html.twig', [
            'active' => 'monresto',
            'active_tab' => 'restaurant',
            'restaurant' => $restaurant 
        ]);
    }

    #[Route('/restaurant/{id}/modal', name: 'restaurant_modal', methods: ['GET'])]
    public function modal(int $id, \Symfony\Component\HttpFoundation\Request $request, RestaurantRepository $restaurantRepository): Response
    {
        $restaurant = $restaurantRepository->find($id);

        if (!$restaurant) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $distance = $request->query->get('distance');

        // Calcul du statut de l'événement
        $today = (new \DateTime())->format('Y-m-d');
        $tomorrow = (new \DateTime('+1 day'))->format('Y-m-d');
        $status = 'standard';
        
        foreach ($restaurant->getEvenements() as $event) {
            $eventDate = $event->getDateEvenement()->format('Y-m-d');
            if ($eventDate === $today) {
                $status = 'today';
                break;
            } elseif ($eventDate === $tomorrow) {
                $status = 'soon';
            }
        }

        return $this->render('restaurant/_modal.html.twig', [
            'restaurant' => $restaurant,
            'distance_km' => $distance,
            'event_status' => $status,
        ]);
    }

    #[Route('/panneau/avis', name: 'app_panneau_avis')]
    public function avis(): Response
    {
        $user = $this->getUser();
        $restaurant = $user ? $user->getRestaurants()->first() : null;

        if (!$restaurant) {
            return $this->redirectToRoute('app_home');
        }

        // On récupère les avis de ce restaurant depuis la BDD
        $avisCollection = $restaurant->getAvis(); 
        $totalAvis = count($avisCollection);
        
        // Initialisation des compteurs pour les stats
        $sommeNotes = 0;
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        
        // On calcule la somme et on compte les étoiles
        foreach ($avisCollection as $avis) {
            $sommeNotes += $avis->getNote();
            $distribution[$avis->getNote()]++;
        }

        // Calcul de la moyenne réelle
        $moyenne = $totalAvis > 0 ? round($sommeNotes / $totalAvis, 1) : 0;

        // Préparation des pourcentages pour le graphique Twig
        $stats = [];
        foreach ($distribution as $etoile => $quantite) {
            $stats[] = [
                'etoiles' => $etoile,
                'pourcentage' => $totalAvis > 0 ? round(($quantite / $totalAvis) * 100) : 0
            ];
        }

        return $this->render('pannel_resto/avis_clients.html.twig', [
            'active' => 'monresto',
            'active_tab' => 'avis',
            'restaurant' => $restaurant,
            'note_moyenne' => $moyenne,
            'total_avis' => $totalAvis,
            'distribution' => array_reverse($stats, true), 
            'avis_list' => $avisCollection
        ]);
    }

    #[Route('/panneau/evenements', name: 'app_panneau_evenements')]
    public function evenements(): Response
    {
        $user = $this->getUser();
        $restaurant = $user ? $user->getRestaurants()->first() : null;

        if (!$restaurant) return $this->redirectToRoute('app_home');

        return $this->render('pannel_resto/evenements.html.twig', [
            'active' => 'monresto',
            'active_tab' => 'evenements',
            'restaurant' => $restaurant,
            'evenements' => $restaurant->getEvenements() // Vraie relation BDD
        ]);
    }

    #[Route('/panneau/localisation', name: 'app_panneau_localisation')]
    public function localisation(): Response
    {
        $user = $this->getUser();
        $restaurant = $user ? $user->getRestaurants()->first() : null;

        if (!$restaurant) return $this->redirectToRoute('app_home');

        return $this->render('pannel_resto/localisation.html.twig', [
            'active' => 'monresto',
            'active_tab' => 'localisation',
            'restaurant' => $restaurant,
            'localisation' => [
                'latitude' => $restaurant->getLatitude(), 
                'longitude' => $restaurant->getLongitude()
            ],
            'demande_en_attente' => false,
            'historique' => []
        ]);
    }

    // --- CRÉATION D'UN ÉVÉNEMENT DEPUIS LE PANNEAU ---
    #[Route('/panneau/evenement/create', name: 'panneau_evenement_create', methods: ['POST'])]
    public function createEvenement(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $restaurant = $user ? $user->getRestaurants()->first() : null;
        if (!$restaurant) return $this->redirectToRoute('app_home');

        $titre       = trim($request->request->get('titre', ''));
        $dateStr     = $request->request->get('date_evenement', '');
        $description = $request->request->get('description_evenement', '');
        $places      = $request->request->get('nombre_places');

        if (empty($titre) || strlen($titre) < 3) {
            $this->addFlash('danger', 'Le titre est obligatoire (min 3 caractères).');
            return $this->redirectToRoute('app_panneau_evenements');
        }
        if (empty($dateStr)) {
            $this->addFlash('danger', 'La date et l\'heure sont obligatoires.');
            return $this->redirectToRoute('app_panneau_evenements');
        }

        $evenement = new \App\Entity\Evenement();
        $evenement->setTitre($titre);
        $evenement->setDateEvenement(new \DateTime($dateStr));
        $evenement->setRestaurant($restaurant);
        $evenement->setCreateur($user);
        $evenement->addUtilisateur($user);

        if (!empty($description)) {
            $evenement->setDescriptionEvenement($description);
        }
        if (!empty($places)) {
            $evenement->setNombrePlaces((int) $places);
        }

        // Créer la conversation/groupe associé
        $conversation = new \App\Entity\Conversation();
        $conversation->setNomConversation('Groupe : ' . $evenement->getTitre());
        $conversation->setEvenement($evenement);
        $evenement->setConversation($conversation);
        $conversation->addMembre($user);

        $em->persist($evenement);
        $em->persist($conversation);
        $em->flush();

        $this->addFlash('success', 'Événement créé avec succès !');
        return $this->redirectToRoute('app_panneau_evenements');
    }

    // --- MODIFICATION D'UN ÉVÉNEMENT ---
    #[Route('/panneau/evenement/update/{id}', name: 'panneau_evenement_update', methods: ['POST'])]
    public function updateEvenement(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $restaurant = $user ? $user->getRestaurants()->first() : null;
        if (!$restaurant) return $this->redirectToRoute('app_home');

        $evenement = $em->getRepository(\App\Entity\Evenement::class)->find($id);
        if (!$evenement || $evenement->getRestaurant() !== $restaurant) {
            $this->addFlash('danger', 'Événement introuvable.');
            return $this->redirectToRoute('app_panneau_evenements');
        }

        $method = $request->request->get('_method', 'POST');

        // --- Suppression ---
        if (strtoupper($method) === 'DELETE') {
            // Supprimer la conversation liée si elle existe
            $conversation = $evenement->getConversation();
            if ($conversation) {
                $em->remove($conversation);
            }
            $em->remove($evenement);
            $em->flush();
            $this->addFlash('success', 'Événement supprimé.');
            return $this->redirectToRoute('app_panneau_evenements');
        }

        // --- Mise à jour ---
        $titre       = trim($request->request->get('titre', ''));
        $dateStr     = $request->request->get('date_evenement', '');
        $description = $request->request->get('description_evenement', '');
        $places      = $request->request->get('nombre_places');

        if (!empty($titre) && strlen($titre) >= 3) {
            $evenement->setTitre($titre);
        }
        if (!empty($dateStr)) {
            $newDate = new \DateTime($dateStr);
            $today = new \DateTime('today');
            if ($newDate >= $today) {
                $evenement->setDateEvenement($newDate);
            }
        }
        if ($description !== null) {
            $evenement->setDescriptionEvenement($description);
        }
        if (!empty($places)) {
            $nbParticipants = $evenement->getUtilisateurs()->count();
            $newPlaces = max((int) $places, $nbParticipants);
            $evenement->setNombrePlaces($newPlaces);
        }

        // Mettre à jour le nom de la conversation si le titre a changé
        $conversation = $evenement->getConversation();
        if ($conversation) {
            $conversation->setNomConversation('Groupe : ' . $evenement->getTitre());
        }

        $em->flush();
        $this->addFlash('success', 'Événement modifié avec succès !');
        return $this->redirectToRoute('app_panneau_evenements');
    }

    // --- ROUTES VIDES POUR LES LIENS ENCORE NON DÉVELOPPÉS ---
    #[Route('/panneau/avis/repondre/{id}', name: 'panneau_repondre_avis')]
    #[Route('/panneau/update-horaires', name: 'panneau_update_horaires')]
    #[Route('/panneau/localisation-demande', name: 'panneau_localisation_demande')]
    public function voidRoute(): Response { return $this->redirectToRoute('app_panneau_resto'); }

    #[Route('/panneau/update-field', name: 'panneau_update_field', methods: ['POST'])]
    public function updateField(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $restaurant = $user?->getRestaurants()->first() ?: null;

        if (!$restaurant) {
            return $this->redirectToRoute('app_home');
        }

        $field = $request->request->get('field');
        $value = trim($request->request->get('value', ''));

        match ($field) {
            'nom_restaurant'         => $restaurant->setNomRestaurant($value),
            'description_restaurant' => $restaurant->setDescriptionRestaurant(strip_tags(html_entity_decode($value))),
            'num_tel_restaurant'     => $restaurant->setNumTelRestaurant($value),
            'site_web'               => $restaurant->setSiteWeb($value),
            'adresse_restaurant'     => $restaurant->setAdresseRestaurant($value),
            'carte_menu'             => $restaurant->setCarteMenu($value),
            'logo'                   => $restaurant->setLogo($value),
            // Legacy keys (au cas où)
            'nom'         => $restaurant->setNomRestaurant($value),
            'description' => $restaurant->setDescriptionRestaurant(strip_tags(html_entity_decode($value))),
            'telephone'   => $restaurant->setNumTelRestaurant($value),
            'siteWeb'     => $restaurant->setSiteWeb($value),
            'adresse'     => $restaurant->setAdresseRestaurant($value),
            default       => null,
        };

        $em->flush();
        $this->addFlash('success', 'Modification enregistrée.');

        return $this->redirectToRoute('app_panneau_resto');
    }
}