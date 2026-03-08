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
        $restaurantsEntities = $repository->findAll();
        $restaurantsData = [];

        // On définit les dates de comparaison
        $today = (new \DateTime())->format('Y-m-d');
        $tomorrow = (new \DateTime('+1 day'))->format('Y-m-d');

        foreach ($restaurantsEntities as $resto) {
            if ($resto->getLatitude() && $resto->getLongitude()) {

                // --- LOGIQUE DE STATUT ---
                $status = 'standard';
                // On boucle sur les événements du restaurant pour déterminer le statut
                foreach ($resto->getEvenements() as $event) {
                    $eventDate = $event->getDateEvenement()->format('Y-m-d');

                    if ($eventDate === $today) {
                        $status = 'today';
                        break; // Priorité à l'événement du jour
                    } elseif ($eventDate === $tomorrow) {
                        $status = 'soon';
                    }
                }

                $restaurantsData[] = [
                    'id'   => $resto->getId(), // Ajout de l'ID pour tes boutons détails
                    'nom'  => $resto->getNomRestaurant(),
                    'lat'  => $resto->getLatitude(),
                    'lng'  => $resto->getLongitude(),
                    'event_status' => $status, // <--- C'EST ÇA QUI MANQUAIT !
                ];
            }
        }

        return $this->render('map/index.html.twig', [
            'restaurants_json' => json_encode($restaurantsData)
        ]);
    }
}
