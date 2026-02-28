<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\RestaurantRepository;

class MapController extends AbstractController
{
    #[Route('/carte', name: 'app_map')]
    public function index(RestaurantRepository $repository): Response
    {
        // On utilise la fonction qui existe déjà (findAll)
        $restaurantsEntities = $repository->findAll();

        // On crée la structure de données dont la Map a besoin
        $restaurantsData = [];
        foreach ($restaurantsEntities as $resto) {
            if ($resto->getLatitude() && $resto->getLongitude()) {
                $restaurantsData[] = [
                    'nom' => $resto->getNomRestaurant(),
                    'lat' => $resto->getLatitude(),
                    'lng' => $resto->getLongitude(),

                ];
            }
        }
        return $this->render('map/index.html.twig', [
            'restaurants_json' => json_encode($restaurantsData)
        ]);
    }
}
