<?php

namespace App\Controller;

use App\Repository\RestaurantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class RestaurantController extends AbstractController
{
    #[Route('/restaurants', name: 'restaurant_list')]
    public function index(RestaurantRepository $restaurantRepository): Response
    {
        $restaurants = $restaurantRepository->findAll();
        return $this->render('restaurant/index.html.twig', ['restaurants' => $restaurants]);
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

    /**
     * Action pour sauvegarder dynamiquement les modifications
     */
    #[Route('/panneau/update-field', name: 'panneau_update_field', methods: ['POST'])]
    public function updateField(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $restaurant = $user ? $user->getRestaurants()->first() : null;

        if (!$restaurant) {
            $this->addFlash('danger', 'Restaurant introuvable.');
            return $this->redirectToRoute('app_panneau_resto');
        }

        $field = $request->request->get('field'); 
        $value = $request->request->get('value'); 

        switch ($field) {
            case 'nom_restaurant': $restaurant->setNomRestaurant($value); break;
            case 'adresse_restaurant': $restaurant->setAdresseRestaurant($value); break;
            case 'num_tel_restaurant': $restaurant->setNumTelRestaurant($value); break;
            case 'site_web': $restaurant->setSiteWeb($value); break;
            case 'carte_menu': $restaurant->setCarteMenu($value); break;
            case 'logo': $restaurant->setLogo($value); break;
            case 'description_restaurant': $restaurant->setDescriptionRestaurant($value); break;
            case 'horaires': $restaurant->setHoraires($value); break;
            default:
                $this->addFlash('danger', 'Modification impossible : champ inconnu.');
                return $this->redirectToRoute('app_panneau_resto');
        }

        $em->flush();
        $this->addFlash('success', 'Information mise à jour avec succès !');
        return $this->redirectToRoute('app_panneau_resto');
    }

    #[Route('/panneau/avis', name: 'app_panneau_avis')]
    public function avis(): Response
    {
        $user = $this->getUser();
        $restaurant = $user ? $user->getRestaurants()->first() : null;

        if (!$restaurant) return $this->redirectToRoute('app_home');

        // Note : On pourra calculer ici la moyenne réelle plus tard
        return $this->render('pannel_resto/avis_clients.html.twig', [
            'active' => 'monresto',
            'active_tab' => 'avis',
            'restaurant' => $restaurant,
            'note_moyenne' => 4.5, // Exemple statique en attendant le calcul SQL
            'distribution' => [
                ['etoiles' => 5, 'pourcentage' => 80],
                ['etoiles' => 4, 'pourcentage' => 15],
                ['etoiles' => 3, 'pourcentage' => 5],
            ],
            'avis_list' => [] // À lier avec une table Avis si elle existe
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