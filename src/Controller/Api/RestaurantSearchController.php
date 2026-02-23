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
        // On récupère les coordonnées GPS envoyées dans l'URL (ex: ?lat=45.75&lng=4.83)
        // Par défaut, si rien n'est mis, on centre sur Lyon (45.75 / 4.83)
        $lat = $request->query->get('lat', 45.7578);
        $lng = $request->query->get('lng', 4.8322);

        // Appel de la méthode Haversine créée dans le Repository
        // On cherche dans un rayon de 10km
        $restaurants = $repository->findNearby((float)$lat, (float)$lng, 10);

        // On prépare un tableau simple pour éviter les boucles infinies (Circular Reference)
        $data = [];
        foreach ($restaurants as $result) {
            // On extrait l'objet restaurant (index 0) et la valeur distance
            $restaurant = $result[0];
            $distance = $result['distance'] ?? null; // Utilisation de l'opérateur de coalescence pour éviter les erreurs

            $data[] = [
                'id'          => $restaurant->getId(), // INDISPENSABLE pour la Card de ton collègue
                'nom'         => $restaurant->getNomRestaurant(),
                'adresse'     => $restaurant->getAdresseRestaurant(),
                'lat'         => (float)$restaurant->getLatitude(),
                'lng'         => (float)$restaurant->getLongitude(),
                'distance_km' => ($distance !== null) ? round((float)$distance, 2) : null
            ];
        }
        return new JsonResponse($data);
    }
}
