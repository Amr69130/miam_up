<?php

namespace App\Controller\Api;

use App\Repository\RestaurantRepository;
use App\Repository\AmitieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class RestaurantSearchController extends AbstractController
{
    #[Route('/api/restaurants/search', name: 'api_restaurant_search', methods: ['GET'])]
    public function search(
        Request $request,
        RestaurantRepository $repository,
        AmitieRepository $amitieRepository
    ): JsonResponse {
        // 1. Récupération des paramètres et de l'utilisateur connecté
        $lat = $request->query->get('lat', 45.7578);
        $lng = $request->query->get('lng', 4.8322);
        $currentUser = $this->getUser();

        $restaurants = $repository->findNearby((float)$lat, (float)$lng, 10);

        // 2. Dates pour le statut visuel du marqueur (gris/bleu/vert)
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

                $participants = [];
                foreach ($event->getUtilisateurs() as $u) {

                    $friendshipStatus = null;
                    $amISender = false;
                    $isMe = ($currentUser && $u->getId() === $currentUser->getId());

                    // --- LOGIQUE SOCIALE ---
                    // On ne cherche une amitié que si ce n'est pas nous-même
                    if ($currentUser && !$isMe) {
                        $relation = $amitieRepository->createQueryBuilder('a')
                            ->where('(a.utilisateur_demandeur = :me AND a.utilisateur_receveur = :other) OR (a.utilisateur_demandeur = :other AND a.utilisateur_receveur = :me)')
                            ->setParameter('me', $currentUser)
                            ->setParameter('other', $u)
                            ->getQuery()
                            ->getOneOrNullResult();

                        if ($relation) {
                            $friendshipStatus = $relation->getStatut();
                            // Est-ce que c'est moi qui ai cliqué sur "+" à l'origine ?
                            $amISender = ($relation->getUtilisateurDemandeur() === $currentUser);
                        }
                    }

                    $participants[] = [
                        'id' => $u->getId(),
                        'prenom' => $u->getPrenomUtilisateur(),
                        'is_me' => $isMe, // Pour cacher le bouton "+" sur soi-même en React
                        'friendship' => [
                            'status' => $friendshipStatus, // 'en_attente', 'accepte', ou null
                            'am_i_sender' => $amISender
                        ]
                    ];
                }

                $eventsData[] = [
                    'id' => $event->getId(),
                    'titre' => $event->getTitre(),
                    'participants' => $participants,
                    'createur_id' => $event->getCreateur() ? $event->getCreateur()->getId() : null,
                    'is_joined' => $currentUser ? $event->getUtilisateurs()->contains($currentUser) : false,
                ];

                // Définition de la couleur du marqueur selon la date
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
