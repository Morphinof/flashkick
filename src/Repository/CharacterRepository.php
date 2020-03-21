<?php

declare(strict_types=1);

namespace Flashkick\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use Flashkick\Entity\Character;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Flashkick\Entity\Game;
use Flashkick\Entity\Player;

/**
 * @method Character|null find($id, $lockMode = null, $lockVersion = null)
 * @method Character|null findOneBy(array $criteria, array $orderBy = null)
 * @method Character[]    findAll()
 * @method Character[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CharacterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Character::class);
    }

    public function getCharactersStatistics(Player $player, Game $game): iterable
    {
        $sql = <<<SQL
            SELECT COUNT(g.id) as nb_wins, c.id, c.name, c.icon, c.created_at, c.updated_at, c.deleted_at
            FROM lobby l
            LEFT JOIN player p ON l.creator_id = p.id
            LEFT JOIN lobby_configuration l_c ON l.lobby_configuration_id = l_c.id
            LEFT JOIN lobbies_sets l_s ON l.id = l_s.lobby_id
            LEFT JOIN sets_matches s_m ON l_s.set_id = s_m.set_id
            LEFT JOIN `match` m ON s_m.match_id = m.id
            LEFT JOIN game g ON g.id = l_c.game_id
            LEFT JOIN `character` c ON (m.player_1_character = c.id OR m.player_2_character = c.id)
            WHERE g.id = :game
            AND m.winner = :player
            GROUP BY m.id
            ORDER BY nb_wins DESC
        ;
SQL;

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Character::class, 'c', 'character');
        $rsm->addFieldResult('c','id', 'id');
        $rsm->addFieldResult('c','name', 'name');
        $rsm->addFieldResult('c','icon', 'icon');
        $rsm->addFieldResult('c','created_at', 'createdAt');
        $rsm->addFieldResult('c','updated_at', 'updatedAt');
        $rsm->addFieldResult('c','deleted_at', 'deletedAt');
        $rsm->addJoinedEntityResult(Game::class, 'g','c', 'game');
        $rsm->addScalarResult('nb_wins', 'nb_wins');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameters([
            'player' => $player->getId(),
            'game' => $game->getId(),
        ]);

        return $query->getResult();
    }

    // /**
    //  * @return Character[] Returns an array of Character objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Character
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
