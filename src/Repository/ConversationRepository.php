<?php

namespace App\Repository;

use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /*public function findConversationBetweenTwoUsers(int $userId1, int $userId2): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->join('c.membres', 'm1')
            ->join('c.membres', 'm2')
            ->where('m1.id = :user1')
            ->andWhere('m2.id = :user2')
            ->setParameter('user1', $userId1)
            ->setParameter('user2', $userId2)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }*/
    public function findConversationBetweenTwoUsers($user1Id, $user2Id)
    {
        return $this->createQueryBuilder('c')
            ->join('c.membres', 'm1')
            ->join('c.membres', 'm2')
            ->where('m1.id = :u1')
            ->andWhere('m2.id = :u2')
            // On ajoute cette sous-requête pour compter le nombre total de membres
            // Si le compte est différent de 2, ce n'est pas un chat privé !
            ->andWhere('(SELECT COUNT(m3) FROM App\Entity\Conversation c2
                     JOIN c2.membres m3
                     WHERE c2.id = c.id) = 2')
            ->setParameter('u1', $user1Id)
            ->setParameter('u2', $user2Id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Conversation[] Returns an array of Conversation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Conversation
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
