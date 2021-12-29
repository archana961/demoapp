<?php

namespace App\Repository;

use App\Entity\EventLogs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EventLogs|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventLogs|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventLogs[]    findAll()
 * @method EventLogs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventLogsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventLogs::class);
    }

    // /**
    //  * @return EventLogs[] Returns an array of EventLogs objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EventLogs
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
