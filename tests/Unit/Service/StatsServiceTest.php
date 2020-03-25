<?php

declare(strict_types=1);

namespace Flashkick\Service\Tests\Functional\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Flashkick\Entity\Game;
use Flashkick\Entity\Lobby;
use Flashkick\Entity\Match;
use Flashkick\Entity\MatchResolution;
use Flashkick\Entity\Set;
use Flashkick\Entity\User;
use Flashkick\Repository\CharacterRepository;
use Flashkick\Repository\LobbyRepository;
use Flashkick\Repository\MatchRepository;
use Flashkick\Service\StatsService;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class StatsServiceTest extends KernelTestCase
{
    private Prophet $prophet;
    private ObjectProphecy $registry;
    private ObjectProphecy $tokenStorage;
    private ObjectProphecy $lobbyRepository;
    private ObjectProphecy $matchRepository;
    private ObjectProphecy $characterRepository;
    private ObjectProphecy $em;
    private ObjectProphecy $token;

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function setUp(): void
    {
        self::$kernel = self::bootKernel();

        $this->registry = $this->prophesize(ManagerRegistry::class);
        $this->tokenStorage = $this->prophesize(TokenStorageInterface::class);
        $this->lobbyRepository = $this->prophesize(LobbyRepository::class);
        $this->matchRepository = $this->prophesize(MatchRepository::class);
        $this->characterRepository = $this->prophesize(CharacterRepository::class);
        $this->em = $this->prophesize(EntityManager::class);
        $this->em->flush()->willReturn(null);
        $this->registry->getManager()->willReturn($this->em->reveal());
        $this->token = $this->prophesize(TokenInterface::class);

        $this->prophet = new Prophet;
    }

    public function testGetGameStatistics(): void
    {
        $game = new Game();
        $user1 = new User();
        $player1 = $user1->getPlayer();
        $user2 = new User();
        $player2 = $user2->getPlayer();
        $lobby = new Lobby();
        $set = new Set();

        // P1 wins 1/0
        $match1 = new Match();
        $match1->setPlayer1($player1);
        $match1->setPlayer2($player2);
        $match1->setWinner($player1);
        $match1->setEnded();

        // P2 wins 1/1
        $match2 = new Match();
        $match2->setPlayer1($player1);
        $match2->setPlayer2($player2);
        $match2->setWinner($player2);
        $match2->setEnded();

        // Draw game
        $match3 = new Match();
        $match3->setPlayer1($player1);
        $match3->setPlayer2($player2);
        $match3->getResolution()->setValidationP1(MatchResolution::DRAW);
        $match3->getResolution()->setValidationP2(MatchResolution::DRAW);
        $match3->setEnded();

        // P1 wins 2/1
        $match4 = new Match();
        $match4->setPlayer1($player1);
        $match4->setPlayer2($player2);
        $match4->setWinner($player1);
        $match4->setEnded();

        // Next match
        $match5 = new Match();

        $set->addMatch($match1);
        $set->addMatch($match2);
        $set->addMatch($match3);
        $set->addMatch($match4);
        $set->addMatch($match5);
        $set->setWinner($player1);
        $set->setEnded();
        $lobby->addSet($set);

        $this->lobbyRepository->findByPlayerAndGame($player1, $game)->willReturn([$lobby]);
        $statsService = new StatsService(
            $this->lobbyRepository->reveal(),
            $this->matchRepository->reveal(),
            $this->characterRepository->reveal()
        );

        $stats = $statsService->getGameStatistics($player1, $game);

        $this->assertEquals($stats, [
            'wins' => 2,
            'loses' => 1,
            'draws' => 1,
            'ratio' => 50.0,
            'total' => 4,
        ]);
    }

    public function testgetGlobalStatistics(): void
    {
        $game = new Game();
        $user1 = new User();
        $player1 = $user1->getPlayer();
        $user2 = new User();
        $player2 = $user2->getPlayer();

        // Lobby 1
        $lobby1 = new Lobby();
        $set = new Set();

        // P1 wins 1/0
        $match1 = new Match();
        $match1->setPlayer1($player1);
        $match1->setPlayer2($player2);
        $match1->setWinner($player1);
        $match1->setEnded();

        // P2 wins 1/1
        $match2 = new Match();
        $match2->setPlayer1($player1);
        $match2->setPlayer2($player2);
        $match2->setWinner($player2);
        $match2->setEnded();

        // Draw game
        $match3 = new Match();
        $match3->setPlayer1($player1);
        $match3->setPlayer2($player2);
        $match3->getResolution()->setValidationP1(MatchResolution::DRAW);
        $match3->getResolution()->setValidationP2(MatchResolution::DRAW);
        $match3->setEnded();

        // P1 wins 2/1
        $match4 = new Match();
        $match4->setPlayer1($player1);
        $match4->setPlayer2($player2);
        $match4->setWinner($player1);
        $match4->setEnded();

        // Next match
        $match5 = new Match();

        $set->addMatch($match1);
        $set->addMatch($match2);
        $set->addMatch($match3);
        $set->addMatch($match4);
        $set->addMatch($match5);
        $set->setWinner($player1);
        $set->setEnded();
        $lobby1->addSet($set);

        //
        // Lobby 2
        //
        $lobby2 = new Lobby();
        $set = new Set();

        // P2 wins 0/1
        $match6 = new Match();
        $match6->setPlayer1($player1);
        $match6->setPlayer2($player2);
        $match6->setWinner($player2);
        $match6->setEnded();

        // P1 wins 1/1
        $match7 = new Match();
        $match7->setPlayer1($player1);
        $match7->setPlayer2($player2);
        $match7->setWinner($player1);
        $match7->setEnded();

        // Draw game
        $match8 = new Match();
        $match8->setPlayer1($player1);
        $match8->setPlayer2($player2);
        $match8->getResolution()->setValidationP1(MatchResolution::DRAW);
        $match8->getResolution()->setValidationP2(MatchResolution::DRAW);
        $match8->setEnded();

        // P2 wins 2/1
        $match9 = new Match();
        $match9->setPlayer1($player1);
        $match9->setPlayer2($player2);
        $match9->setWinner($player2);
        $match9->setEnded();

        $set->addMatch($match6);
        $set->addMatch($match7);
        $set->addMatch($match8);
        $set->addMatch($match9);
        $set->setWinner($player1);
        $set->setEnded();
        $lobby2->addSet($set);

        $matches = [$match1, $match2, $match3, $match4, $match5, $match6, $match7, $match8, $match9,];
        $this->matchRepository->findByPlayer($player1)->willReturn($matches);
        $statsService = new StatsService(
            $this->lobbyRepository->reveal(),
            $this->matchRepository->reveal(),
            $this->characterRepository->reveal()
        );

        $stats = $statsService->getGlobalStatistics($player2);

        $this->assertEquals($stats, [
            'globals' => [
                'wins' => 3,
                'loses' => 3,
                'draws' => 2,
                'ratio' => 38.0,
                'total' => 8,
            ]
        ]);

        $this->characterRepository->getCharactersStatistics($player2, $game)->willReturn([]);
        $stats2 = $statsService->getCharactersStatistics($player2, $game);

        $this->assertEmpty($stats2);
    }

    protected function tearDown(): void
    {
        $this->prophet->checkPredictions();
    }
}
