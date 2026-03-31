<?php

namespace App\Controller;

use App\Repository\RestaurantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RestaurantController extends AbstractController
{
    #[Route('/restaurants', name: 'restaurant_list')]
    public function index(RestaurantRepository $restaurantRepository): Response
    {
        // On récupère tous les restaurants pour les afficher côté client
        $restaurants = $restaurantRepository->findAll();

        return $this->render('restaurant/index.html.twig', [
            'restaurants' => $restaurants,
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
            throw $this->createNotFoundException('Le restaurant n\'existe pas.');
        }

        return $this->render('restaurant/show.html.twig', [
            'restaurant' => $restaurant,
        ]);
    }
}