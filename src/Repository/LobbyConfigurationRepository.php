<?php

declare(strict_types=1);

namespace Flashkick\Repository;

use Flashkick\Entity\LobbyConfiguration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LobbyConfiguration|null find($id, $lockMode = null, $lockVersion = null)
 * @method LobbyConfiguration|null findOneBy(array $criteria, array $orderBy = null)
 * @method LobbyConfiguration[]    findAll()
 * @method LobbyConfiguration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LobbyConfigurationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LobbyConfiguration::class);
    }

    // /**
    //  * @return LobbyConfiguration[] Returns an array of LobbyConfiguration objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LobbyConfiguration
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
