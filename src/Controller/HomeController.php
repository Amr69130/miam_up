<?php

namespace App\Controller;

use App\Repository\RestaurantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/accueil', name: 'app_home')]
    public function index(RestaurantRepository $restaurantRepo): Response
    {
        $allRestaurants = $restaurantRepo->findAll();

        // 1. Filtrer ceux qui ont une note, PUIS trier, PUIS couper
        $rated = array_filter($allRestaurants, fn($r) => $r->getNoteConsommateurs() !== null);
        usort($rated, fn($a, $b) => $b->getNoteConsommateurs() <=> $a->getNoteConsommateurs());
        $topRated = array_slice($rated, 0, 6);

        // 2. "À découvrir" = restaurants hors top (ou tous si pas assez)
        $topIds = array_map(fn($r) => $r->getId(), $topRated);
        $discover = array_filter($allRestaurants, fn($r) => !in_array($r->getId(), $topIds));
        // Si pas assez de restaurants hors top, on complète avec tous
        if (count($discover) < 4) {
            $discover = $allRestaurants;
        }
        $discover = array_slice(array_values($discover), 0, 4);

        return $this->render('home/index.html.twig', [
            'topRated'  => $topRated,
            'discover'  => $discover,
            'totalCount' => count($allRestaurants),
        ]);
    }
}