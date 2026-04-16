<?php

namespace App\Controller;

use App\Repository\RestaurantRepository;
use App\Entity\Restaurant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RestaurantController extends AbstractController
{
    // Données partagées pour le restaurant (Header/Infos)
    private function getMockRestaurant() {
        return [
            'nom' => "Le Petit Bistro",
            'adresse' => "12 Rue de la Gastronomie, 75011 Paris",
            'siret' => "123 456 789 00012",
            'telephone' => "01 23 45 67 89",
            'site_web' => "https://lepetitbistro.fr",
            'lien_menu' => "https://lepetitbistro.fr/carte.pdf",
            'url_logo' => "https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=150&q=80",
            'logo' => "https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=150&q=80",
            'note' => "4.8",
            'total_avis' => "124 avis",
            'description' => "Un bistrot chaleureux proposant une cuisine française traditionnelle revisitée avec des produits locaux de saison.",
            'horaires' => [
                ['jour' => 'Lundi', 'horaire' => '12:00 – 14:30, 19:00 – 22:30', 'ferme' => false],
                ['jour' => 'Mardi', 'horaire' => '12:00 – 14:30, 19:00 – 22:30', 'ferme' => false],
                ['jour' => 'Mercredi', 'horaire' => '12:00 – 14:30, 19:00 – 22:30', 'ferme' => false],
                ['jour' => 'Jeudi', 'horaire' => '12:00 – 14:30, 19:00 – 22:30', 'ferme' => false],
                ['jour' => 'Vendredi', 'horaire' => '12:00 – 14:30, 19:00 – 23:00', 'ferme' => false],
                ['jour' => 'Samedi', 'horaire' => '19:00 – 23:30', 'ferme' => false],
                ['jour' => 'Dimanche', 'horaire' => 'Fermé', 'ferme' => true],
            ]
        ];
    }

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
    public function show(Restaurant $restaurant): Response
    {
        return $this->render('restaurant/show.html.twig', [
            'restaurant' => $restaurant,
        ]);
    }

    // --- ROUTES DU PANNEAU RESTAURATEUR ---

    #[Route('/panneau', name: 'app_panneau_resto')]
    public function panneau(): Response
    {
            return $this->render('pannel_resto/mon_restaurant.html.twig', [
            'active' => 'monresto',
            'active_tab' => 'restaurant',
            'restaurant' => $this->getMockRestaurant()
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
    
    #[Route('/panneau/avis', name: 'app_panneau_avis')]
    public function avis(): Response
    {
        return $this->render('pannel_resto/avis_clients.html.twig', [
            'active' => 'monresto',
            'active_tab' => 'avis',
            'restaurant' => $this->getMockRestaurant(),
            'note_moyenne' => 4.8,
            'distribution' => [
                ['etoiles' => 5, 'pourcentage' => 85],
                ['etoiles' => 4, 'pourcentage' => 10],
                ['etoiles' => 3, 'pourcentage' => 3],
                ['etoiles' => 2, 'pourcentage' => 2],
                ['etoiles' => 1, 'pourcentage' => 0],
            ],
            'avis_list' => [
                [
                    'id' => 1, 
                    'auteur' => 'Thomas D.', 
                    'date' => 'Il y a 2 jours', 
                    'note' => 5, 
                    'commentaire' => 'Excellent service et plats délicieux !', 
                    'avatar' => 'https://i.pravatar.cc/150?u=1',
                    'reponse' => 'Merci beaucoup Thomas !'
                ],
            ]
        ]);
    }

    #[Route('/panneau/evenements', name: 'app_panneau_evenements')]
    public function evenements(): Response
    {
        return $this->render('pannel_resto/evenements.html.twig', [
            'active' => 'monresto',
            'active_tab' => 'evenements',
            'restaurant' => $this->getMockRestaurant(),
            'evenements' => [
                [
                    'id' => 1,
                    'titre' => "Soirée Dégustation Vins & Fromages",
                    'date_evenement' => new \DateTime('2026-03-22'),
                    'heure_debut' => "19:00",
                    'heure_fin' => "22:00",
                    'participants' => 13,
                    'nombre_places' => 20,
                    'description_evenement' => "Découvrez une sélection de vins naturels.",
                    'image' => "https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=400&q=80"
                ]
            ]
        ]);
    }

    #[Route('/panneau/localisation', name: 'app_panneau_localisation')]
    public function localisation(): Response
    {
        return $this->render('pannel_resto/localisation.html.twig', [
            'active' => 'monresto',
            'active_tab' => 'localisation',
            'restaurant' => $this->getMockRestaurant(),
            'localisation' => ['latitude' => 48.8584, 'longitude' => 2.2945],
            'demande_en_attente' => false,
            'historique' => []
        ]);
    }

    // Routes vides pour éviter les erreurs de liens (path) dans vos formulaires
    #[Route('/panneau/evenement/create', name: 'panneau_evenement_create')]
    #[Route('/panneau/evenement/update/{id}', name: 'panneau_evenement_update')]
    #[Route('/panneau/avis/repondre/{id}', name: 'panneau_repondre_avis')]
    #[Route('/panneau/update-field', name: 'panneau_update_field')]
    #[Route('/panneau/update-horaires', name: 'panneau_update_horaires')]
    #[Route('/panneau/localisation-demande', name: 'panneau_localisation_demande')]
    public function voidRoute(): Response { return $this->redirectToRoute('app_panneau_resto'); }
}