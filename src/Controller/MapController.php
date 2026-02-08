<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MapController extends AbstractController
{
    #[Route('/carte', name: 'app_map')]
    public function index(): Response
    {
        // Imaginons que ceci vient de la base de données
        $restaurants = [
            ['nom' => 'Le Miam Paris', 'lat' => 48.8566, 'lng' => 2.3522],
            ['nom' => 'Le Miam Lyon', 'lat' => 45.7640, 'lng' => 4.8357],
            ['nom' => 'Le Miam Marseille', 'lat' => 43.2965, 'lng' => 5.3698],
        ];

        // On passe les données à la vue (converties en JSON)
        return $this->render('map/index.html.twig', [
            'restaurants_json' => json_encode($restaurants)
        ]);
    }
}