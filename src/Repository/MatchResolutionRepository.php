<?php

declare(strict_types=1);

namespace Flashkick\Repository;

use Flashkick\Entity\MatchResolution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MatchResolution|null find($id, $lockMode = null, $lockVersion = null)
 * @method MatchResolution|null findOneBy(array $criteria, array $orderBy = null)
 * @method MatchResolution[]    findAll()
 * @method MatchResolution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MatchResolutionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MatchResolution::class);
    }

    // /**
    //  * @return MatchResolution[] Returns an array of MatchResolution objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MatchResolution
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
