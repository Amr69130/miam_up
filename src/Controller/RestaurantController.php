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

    // --- ROUTES VIDES POUR LES LIENS ENCORE NON DÉVELOPPÉS ---
    #[Route('/panneau/evenement/create', name: 'panneau_evenement_create')]
    #[Route('/panneau/evenement/update/{id}', name: 'panneau_evenement_update')]
    #[Route('/panneau/avis/repondre/{id}', name: 'panneau_repondre_avis')]
    #[Route('/panneau/update-horaires', name: 'panneau_update_horaires')]
    #[Route('/panneau/localisation-demande', name: 'panneau_localisation_demande')]
    public function voidRoute(): Response { return $this->redirectToRoute('app_panneau_resto'); }
}