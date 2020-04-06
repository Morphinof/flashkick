<?php

declare(strict_types=1);

namespace Flashkick\Tests\Unit\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Flashkick\Entity\Character;
use Flashkick\Entity\Lobby;
use Flashkick\Entity\Match;
use Flashkick\Entity\MatchResolution;
use Flashkick\Entity\Player;
use Flashkick\Entity\Set;
use Flashkick\Entity\User;
use Flashkick\Event\Match\MatchResolvedEvent;
use Flashkick\Repository\LobbyRepository;
use Flashkick\Service\MatchService;
use InvalidArgumentException;
use LogicException;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class MatchServiceTest extends KernelTestCase
{
    private Prophet $prophet;
    private ObjectProphecy $registry;
    private ObjectProphecy $tokenStorage;
    private ObjectProphecy $lobbyRepository;
    private ObjectProphecy $em;
    private ObjectProphecy $token;
    private ObjectProphecy $dispatcher;

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
        $this->em = $this->prophesize(EntityManager::class);
        $this->em->flush()->willReturn(null);
        $this->registry->getManager()->willReturn($this->em->reveal());
        $this->token = $this->prophesize(TokenInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->prophet = new Prophet;
    }

    public function testResolveMatch(): void
    {
        $creator = new User();
        $playerCreator = $creator->getPlayer();
        $user2 = new User();
        $player2 = $user2->getPlayer();
        $user3 = new User();
        $player3 = $user3->getPlayer();
        $user4 = new User();
        $player4 = $user4->getPlayer();
        $lobby = new Lobby();
        $lobby->setCreator($playerCreator);
        $lobby->addPlayer($playerCreator);
        $lobby->addPlayer($player2);
        $lobby->addPlayer($player3);
        $lobby->addPlayer($player4);

        $set = new Set();
        $match = new Match();
        $match->setPlayer1($playerCreator);
        $match->setPlayer2($player2);
        $set->addMatch($match);
        $lobby->addSet($set);

        $event = new MatchResolvedEvent($match);
        $this->dispatcher->dispatch($event, MatchResolvedEvent::NAME)->willReturn($event)->shouldBeCalled();

        $matchService = new MatchService($this->registry->reveal(), $this->dispatcher->reveal());
        $matchService->resolve($match, $playerCreator, MatchResolution::WIN);
        $matchService->resolve($match, $player2, MatchResolution::LOOSE);

        $this->assertTrue($match->isEnded());

        $match = new Match();
        $match->setPlayer1($playerCreator);
        $match->setPlayer2($player2);
        $set->addMatch($match);

        $event = new MatchResolvedEvent($match);
        $this->dispatcher->dispatch($event, MatchResolvedEvent::NAME)->willReturn($event)->shouldBeCalled();

        $matchService->resolve($match, $playerCreator, MatchResolution::WIN);
        $matchService->resolve($match, $player2, MatchResolution::LOOSE);

        $this->assertTrue($match->isEnded());
    }

    public function testResolveWithBadPlayer(): void
    {
        $creator = new User();
        $playerCreator = $creator->getPlayer();
        $user2 = new User();
        $player2 = $user2->getPlayer();
        $user3 = new User();
        $player3 = $user3->getPlayer();
        $lobby = new Lobby();
        $lobby->setCreator($playerCreator);
        $lobby->addPlayer($playerCreator);
        $lobby->addPlayer($player2);
        $lobby->addPlayer($player3);

        $match = new Match();
        $match->setPlayer1($playerCreator);
        $match->setPlayer2($player2);

        $matchService = new MatchService($this->registry->reveal(), $this->dispatcher->reveal());

        $this->expectException(InvalidArgumentException::class);
        $matchService->resolve($match, $player3, MatchResolution::WIN);
    }

    public function testResolveBadResolutionValue(): void
    {
        $creator = new User();
        $playerCreator = $creator->getPlayer();
        $user2 = new User();
        $player2 = $user2->getPlayer();
        $lobby = new Lobby();
        $lobby->setCreator($playerCreator);
        $lobby->addPlayer($playerCreator);
        $lobby->addPlayer($player2);

        $match = new Match();
        $match->setPlayer1($playerCreator);
        $match->setPlayer2($player2);

        $matchService = new MatchService($this->registry->reveal(), $this->dispatcher->reveal());

        $this->expectException(InvalidArgumentException::class);
        $matchService->resolve($match, $playerCreator, -1);
    }

    public function testCheckConflicts(): void
    {
        $creator = new User();
        $playerCreator = $creator->getPlayer();
        $user2 = new User();
        $player2 = $user2->getPlayer();
        $lobby = new Lobby();
        $lobby->setCreator($playerCreator);
        $lobby->addPlayer($playerCreator);
        $lobby->addPlayer($player2);

        $match = new Match();
        $match->setPlayer1($playerCreator);
        $match->setPlayer2($player2);

        $matchService = new MatchService($this->registry->reveal(), $this->dispatcher->reveal());
        $matchService->resolve($match, $playerCreator, MatchResolution::WIN);
        $this->expectException(LogicException::class);
        $matchService->resolve($match, $player2, MatchResolution::WIN);
    }

    public function testEnd(): void
    {
        $creator = new User();
        $playerCreator = $creator->getPlayer();
        $user2 = new User();
        $player2 = $user2->getPlayer();
        $lobby = new Lobby();
        $lobby->setCreator($playerCreator);
        $lobby->addPlayer($playerCreator);
        $lobby->addPlayer($player2);

        $match = new Match();
        $match->setPlayer1($playerCreator);
        $match->setPlayer2($player2);

        $matchService = new MatchService($this->registry->reveal(), $this->dispatcher->reveal());
        $event = new MatchResolvedEvent($match);
        $this->dispatcher->dispatch($event, MatchResolvedEvent::NAME)->willReturn($event)->shouldBeCalled();

        $matchService->resolve($match, $playerCreator, MatchResolution::WIN);
        $matchService->resolve($match, $player2, MatchResolution::LOOSE);
        $matchService->end($match);

        $this->assertSame($match->getWinner(), $playerCreator);

        $event = new MatchResolvedEvent($match);
        $this->dispatcher->dispatch($event, MatchResolvedEvent::NAME)->willReturn($event)->shouldBeCalled();

        $matchService->reset($match);
        $matchService->resolve($match, $playerCreator, MatchResolution::LOOSE);
        $matchService->resolve($match, $player2, MatchResolution::WIN);
        $matchService->end($match);

        $event = new MatchResolvedEvent($match);
        $this->dispatcher->dispatch($event, MatchResolvedEvent::NAME)->willReturn($event)->shouldBeCalled();

        $matchService->reset($match);
        $matchService->resolve($match, $playerCreator, MatchResolution::DRAW);
        $matchService->resolve($match, $player2, MatchResolution::WIN);
        $matchService->end($match);

        $this->assertSame($match->getResolution()->getValidationP1(), MatchResolution::LOOSE);

        $event = new MatchResolvedEvent($match);
        $this->dispatcher->dispatch($event, MatchResolvedEvent::NAME)->willReturn($event)->shouldBeCalled();

        $matchService->reset($match);
        $matchService->resolve($match, $playerCreator, MatchResolution::WIN);
        $matchService->resolve($match, $player2, MatchResolution::DRAW);
        $matchService->end($match);

        $this->assertSame($match->getResolution()->getValidationP2(), MatchResolution::LOOSE);
    }

    public function testResetMatch(): void
    {
        $match = new Match();
        $match->getResolution()->setValidationP1(MatchResolution::WIN);
        $match->getResolution()->setValidationP2(MatchResolution::WIN);

        $this->assertNotNull($match->getResolution()->getValidationP1());
        $this->assertNotNull($match->getResolution()->getValidationP2());

        $matchService = new MatchService($this->registry->reveal(), $this->dispatcher->reveal());
        $matchService->reset($match);

        $this->assertNull($match->getResolution()->getValidationP1());
        $this->assertNull($match->getResolution()->getValidationP2());
    }

    public function testSelectCharacter(): void
    {
        $player1 = new Player();
        $player2 = new Player();
        $character1 = new Character();
        $character2 = new Character();
        $match = new Match();
        $match->setPlayer1($player1);
        $match->setPlayer2($player2);

        $matchService = new MatchService($this->registry->reveal(), $this->dispatcher->reveal());
        $matchService->selectCharacter($match, $player1, $character1);
        $this->assertSame($match->getPlayer1Character(), $character1);

        $matchService->selectCharacter($match, $player2, $character2);
        $this->assertSame($match->getPlayer2Character(), $character2);
    }

    protected function tearDown(): void
    {
        $this->prophet->checkPredictions();
    }
}