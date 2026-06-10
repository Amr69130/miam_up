<?php

namespace App\Repository;

use App\Entity\DemandeLocalisation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DemandeLocalisation>
 *
 * @method DemandeLocalisation|null find($id, $lockMode = null, $lockVersion = null)
 * @method DemandeLocalisation|null findOneBy(array $criteria, array $orderBy = null)
 * @method DemandeLocalisation[]    findAll()
 * @method DemandeLocalisation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandeLocalisationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeLocalisation::class);
    }

    public function save(DemandeLocalisation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DemandeLocalisation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
