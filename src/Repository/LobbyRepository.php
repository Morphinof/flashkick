<?php

namespace Flashkick\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Flashkick\Entity\Game;
use Flashkick\Entity\Lobby;

/**
 * @method Lobby|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lobby|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lobby[]    findAll()
 * @method Lobby[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LobbyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lobby::class);
    }

    public function findByGame(Game $game): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.configuration', 'l_c')
            ->andWhere('l_c.game = :game')
            ->setParameter('game', $game)
            ->orderBy('l.createdAt', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Lobby[] Returns an array of Lobby objects
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
    public function findOneBySomeField($value): ?Lobby
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
