<?php

namespace Flashkick\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;
use Flashkick\Entity\Game;
use Flashkick\Entity\Lobby;
use Flashkick\Entity\LobbyConfiguration;
use Flashkick\Entity\Match;
use Flashkick\Entity\Player;

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

    public function getByMatch(Match $match): ?Lobby
    {
        $sql = <<<SQL
            SELECT l.id, l.uuid, l.created_at, l.updated_at, l.deleted_at
            FROM lobby l
            LEFT JOIN player p ON l.creator_id = p.id
            LEFT JOIN lobby_configuration l_c ON l.lobby_configuration_id = l_c.id
            LEFT JOIN lobbies_sets l_s ON l.id = l_s.lobby_id
            LEFT JOIN sets_matches s_m ON l_s.set_id = s_m.set_id
            WHERE s_m.match_id = :match;
SQL;

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Lobby::class, 'l');
        $rsm->addFieldResult('l','id', 'id');
        $rsm->addFieldResult('l','uuid', 'uuid');
        $rsm->addJoinedEntityResult(Player::class, 'p', 'l', 'creator');
        $rsm->addJoinedEntityResult(LobbyConfiguration::class, 'l_c', 'l', 'configuration');
        $rsm->addFieldResult('l','created_at', 'createdAt');
        $rsm->addFieldResult('l','updated_at', 'updatedAt');
        $rsm->addFieldResult('l','deleted_at', 'deletedAt');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('match', $match);

        return $query->getOneOrNullResult();
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
