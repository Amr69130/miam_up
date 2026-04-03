<?php

namespace App\Controller;

use App\Repository\RestaurantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
            throw $this->createNotFoundException('Le restaurant n\'existe pas.');
        }

        return $this->render('restaurant/show.html.twig', [
            'restaurant' => $restaurant,
        ]);
    }
}