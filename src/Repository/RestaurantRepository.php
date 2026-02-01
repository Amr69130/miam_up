<?php

namespace App\Repository;

use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Restaurant>
 */
class RestaurantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Restaurant::class);
    }

    //    /**
    //     * @return Restaurant[] Returns an array of Restaurant objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Restaurant
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * Retourne les restaurants situés dans un rayon de $radius kilomètres
     */
    public function findNearby(float $lat, float $lng, int $radius = 10): array
    {
        return $this->createQueryBuilder('r')
            ->select('r')
            // Application directe de la formule de Haversine (voir schéma en Phase 3)
            // 6371 représente le rayon moyen de la Terre en kilomètres
            ->addSelect(
                '(6371 * acos(cos(radians(:lat)) * cos(radians(r.latitude)) * cos(radians(r.longitude) - radians(:lng)) + sin(radians(:lat)) * sin(radians(r.latitude)))) AS distance'
            )
            ->setParameter('lat', $lat)
            ->setParameter('lng', $lng)
            ->setParameter('radius', $radius)
            // On filtre pour ne garder que les restos dans le rayon
            ->having('distance <= :radius')
            // On trie du plus proche au plus loin
            ->orderBy('distance', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
