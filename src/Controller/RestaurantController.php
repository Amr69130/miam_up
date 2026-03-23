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
    #[Route('/panneau', name: 'app_panneau_resto')]
    public function panneau(): Response
    {
        // On renvoie vers le fichier index de ton nouveau dossier pannel_resto
        // Et on lui passe la variable 'active' pour allumer le bon bouton dans la sidebar
        return $this->render('pannel_resto/index.html.twig', [
            'active' => 'monresto',
        ]);
    }
}