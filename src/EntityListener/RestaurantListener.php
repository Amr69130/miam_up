<?php

namespace App\EntityListener;

use App\Entity\Restaurant;
use App\Service\GeocodingService;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

/**
 * Cet écouteur d'entité automatise la mise à jour des coordonnées GPS
 * chaque fois qu'un restaurant est manipulé par Doctrine.
 */
#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Restaurant::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Restaurant::class)]
class RestaurantListener
{
   private GeocodingService $geocodingService;

    // Injection du service de géocodage via le constructeur
    public function __construct(GeocodingService $geocodingService)
    {
        $this->geocodingService = $geocodingService;
    }

    /**
     * Ca se déclenche juste avant la qu'un nouveau restaurant soit crée / pris en compte (persisté) en BDD
     */
    public function prePersist(Restaurant $restaurant, LifecycleEventArgs $event): void
    {
        $this->updateCoordinates($restaurant);
    }

    /**
     * Ca se déclenche lors de la modification d'un restaurant existant
     */
    public function preUpdate(Restaurant $restaurant, PreUpdateEventArgs $event): void
    {
        // On ne recalcule les coordonnées automatiques QUE si l'adresse a été modifiée.
        // Cela permet de modifier manuellement la latitude/longitude (ex: via la modération) sans qu'elles soient écrasées.
        if ($event->hasChangedField('adresse_restaurant')) {
            $this->updateCoordinates($restaurant);
        }
    }

    /**
     * Fonction qui contient la logique métier càd : transformer l'adresse textuelle en coordonnées numériques
     */
    private function updateCoordinates(Restaurant $restaurant): void
    {
        $address = $restaurant->getAdresseRestaurant();

        if ($address) {
            // Alors => On vient appeler le service externe (API Adresse)
            $coordinates = $this->geocodingService->getCoordinates($address);

            if ($coordinates) {
                // Alors => Hydratation automatique des Latitude et Longitude de manière auto
                $restaurant->setLatitude($coordinates['lat']);
                $restaurant->setLongitude($coordinates['long']);
            }
        }
    }
}
