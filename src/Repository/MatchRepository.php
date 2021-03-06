<?php

declare(strict_types=1);

namespace Flashkick\Repository;

use Flashkick\Entity\Match;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Flashkick\Entity\Player;

/**
 * @method Match|null find($id, $lockMode = null, $lockVersion = null)
 * @method Match|null findOneBy(array $criteria, array $orderBy = null)
 * @method Match[]    findAll()
 * @method Match[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Match::class);
    }

    /**
     * @return Match[]
     */
    public function findByPlayer(Player $player, bool $winOnly = false): iterable
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->andWhere($qb->expr()->orX('m.player1 = :player', 'm.player2 = :player'))
            ->setParameter('player', $player)
            ->orderBy('m.createdAt', 'DESC');

        if ($winOnly) {
            $qb->andWhere('m.winner = :player');
        }

        return $qb->getQuery()->getResult();
    }

    // /**
    //  * @return Match[] Returns an array of Match objects
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
    public function findOneBySomeField($value): ?Match
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
