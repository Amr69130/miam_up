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

            $status = 'standard';
            $eventsData = [];

            foreach ($restaurant->getEvenements() as $event) {
                $eventDate = $event->getDateEvenement()->format('Y-m-d');

                // On récupère les participants
                $participants = [];
                foreach ($event->getUtilisateurs() as $u) {
                    $participants[] = [
                        'id' => $u->getId(),
                        'prenom' => $u->getPrenomUtilisateur()
                    ];
                }

                $eventsData[] = [
                    'id' => $event->getId(),
                    'titre' => $event->getTitre(),
                    'participants' => $participants,
                    // ON AJOUTE L'ID DU CRÉATEUR ICI POUR L'ÉTOILE ⭐
                    'createur_id' => $event->getCreateur() ? $event->getCreateur()->getId() : null,
                    'is_joined' => $event->getUtilisateurs()->contains($this->getUser()),
                ];

                if ($eventDate === $today) {
                    $status = 'today';
                } elseif ($eventDate === $tomorrow && $status !== 'today') {
                    $status = 'soon';
                }
            }

            $data[] = [
                'id'          => $restaurant->getId(),
                'nom'         => $restaurant->getNomRestaurant(),
                'adresse'     => $restaurant->getAdresseRestaurant(),
                'lat'         => (float)$restaurant->getLatitude(),
                'lng'         => (float)$restaurant->getLongitude(),
                'distance_km' => ($distance !== null) ? round((float)$distance, 2) : null,
                'event_status'=> $status,
                'events'      => $eventsData
            ];
        }
        return new JsonResponse($data);
    }
}
