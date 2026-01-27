<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocodingService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Transforme une adresse en coordonnées [lat, long]
     */
    public function getCoordinates(string $address): ?array
    {
        if (empty($address)) {
            return null;
        }

        // 1. On appelle l'API (on encode l'adresse pour l'URL)
        $response = $this->client->request(
            'GET',
            'https://api-adresse.data.gouv.fr/search/?q=' . urlencode($address) . '&limit=1'
        );

        $data = $response->toArray();

        // 2. On vérifie si on a un résultat
        if (empty($data['features'])) {
            return null;
        }

        // 3. L'API renvoie [longitude, latitude], on les récupère
        $coordinates = $data['features'][0]['geometry']['coordinates'];

        return [
            'long' => $coordinates[0],
            'lat' => $coordinates[1],
        ];
    }
}
