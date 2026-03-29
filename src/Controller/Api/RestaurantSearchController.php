<?php

namespace App\Controller\Api;

use App\Repository\RestaurantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class RestaurantSearchController extends AbstractController
{
    #[Route('/api/restaurants/search', name: 'api_restaurant_search', methods: ['GET'])]
    public function search(Request $request, RestaurantRepository $repository): JsonResponse
    {
        $lat = $request->query->get('lat', 45.7578);
        $lng = $request->query->get('lng', 4.8322);

        $restaurants = $repository->findNearby((float)$lat, (float)$lng, 10);

        // --- ON PRÉPARE LES DATES POUR LE CALCUL DU STATUT ---
        $today = (new \DateTime())->format('Y-m-d');
        $tomorrow = (new \DateTime('+1 day'))->format('Y-m-d');

        $data = [];
        foreach ($restaurants as $result) {
            $restaurant = $result[0];
            $distance = $result['distance'] ?? null;

            // --- LOGIQUE DE STATUT (Identique au MapController) ---
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

            // --- BLOC DE TEST QUI FORCE UN EVENT ---
            if ($restaurant->getNomRestaurant() === 'EcullyPizza') {
                $status = 'today'; // On force le vert pour tester le PNG
            }
            if ($restaurant->getNomRestaurant() === 'EcullyTacos') {
                $status = 'soon'; // On force le bleu pour tester le PNG
            }
            if ($restaurant->getNomRestaurant() === 'Lyon9Pizza') {
                $status = 'soon'; // On force le bleu pour tester le PNG
            }

            $data[] = [
                'id'          => $restaurant->getId(),
                'nom'         => $restaurant->getNomRestaurant(),
                'adresse'     => $restaurant->getAdresseRestaurant(),
                'lat'         => (float)$restaurant->getLatitude(),
                'lng'         => (float)$restaurant->getLongitude(),
                'distance_km' => ($distance !== null) ? round((float)$distance, 2) : null,
                'event_status'=> $status // <--- ON AJOUTE L'INFO ICI !
            ];
        }
        return new JsonResponse($data);
    }
}
