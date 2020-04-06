<?php

declare(strict_types=1);

namespace Flashkick\Service\Tests\Functional\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Flashkick\Entity\Lobby;
use Flashkick\Entity\Match;
use Flashkick\Entity\Set;
use Flashkick\Entity\User;
use Flashkick\Repository\LobbyRepository;
use Flashkick\Service\LobbyService;
use LogicException;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class LobbyServiceTest extends KernelTestCase
{
    private Prophet $prophet;
    private ObjectProphecy $registry;
    private ObjectProphecy $tokenStorage;
    private ObjectProphecy $lobbyRepository;
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
        $this->em = $this->prophesize(EntityManager::class);
        $this->em->flush()->willReturn(null);
        $this->registry->getManager()->willReturn($this->em->reveal());
        $this->token = $this->prophesize(TokenInterface::class);

        $this->prophet = new Prophet;
    }

    public function testJoin(): void
    {
        $user = new User();
        $player = $user->getPlayer();
        $lobby = new Lobby();
        $lobby->setCreator($player);

        $this->lobbyRepository->findByPlayer($player)->willReturn($lobby);

        $lobbyService = new LobbyService($this->registry->reveal(), $this->tokenStorage->reveal(),
            $this->lobbyRepository->reveal());
        $lobbyService->join($lobby, $player);

        $this->assertTrue($lobby->hasPlayer($player));
    }

    public function testLeave(): void
    {
        $user = new User();
        $player = $user->getPlayer();
        $lobby = new Lobby();
        $lobby->setCreator($player);
        $lobby->addPlayer($player);

        $this->lobbyRepository->findByPlayer($player)->willReturn($lobby);
        $lobbyService = new LobbyService($this->registry->reveal(), $this->tokenStorage->reveal(),
            $this->lobbyRepository->reveal());
        $lobbyService->leave($lobby, $player);

        $this->assertFalse($lobby->hasPlayer($player));
    }

    public function testKickFailsRestrictedToCreator(): void
    {
        $creator = new User();
        $playerCreator = $creator->getPlayer();
        $user = new User();
        $player = $user->getPlayer();
        $lobby = new Lobby();
        $lobby->setCreator($player);
        $lobby->addPlayer($player);

        $this->token->getUser()->willReturn($creator);
        $this->tokenStorage->getToken()->willReturn($this->token->reveal());
        $lobbyService = new LobbyService($this->registry->reveal(), $this->tokenStorage->reveal(),
            $this->lobbyRepository->reveal());

        $this->expectException(RuntimeException::class);
        $lobbyService->kick($lobby, $playerCreator);
    }

    public function testKickSuccess(): void
    {
        $creator = new User();
        $playerCreator = $creator->getPlayer();
        $user = new User();
        $player = $user->getPlayer();
        $lobby = new Lobby();
        $lobby->setCreator($playerCreator);
        $lobby->addPlayer($player);

        $this->token->getUser()->willReturn($creator);
        $this->tokenStorage->getToken()->willReturn($this->token->reveal());

        $lobbyService = new LobbyService($this->registry->reveal(), $this->tokenStorage->reveal(),
            $this->lobbyRepository->reveal());

        $lobbyService->kick($lobby, $player);
        $this->assertFalse($lobby->hasPlayer($player));
    }

    public function testKickFailsCannotKickCreator(): void
    {
        $user = new User();
        $player = $user->getPlayer();
        $lobby = new Lobby();
        $lobby->setCreator($player);
        $lobby->addPlayer($player);

        $this->token->getUser()->willReturn($user);
        $this->tokenStorage->getToken()->willReturn($this->token->reveal());

        $lobbyService = new LobbyService($this->registry->reveal(), $this->tokenStorage->reveal(),
            $this->lobbyRepository->reveal());

        $this->expectException(LogicException::class);
        $lobbyService->kick($lobby, $player);
    }

    public function testLobbyIsFull(): void
    {
        $user = new User();
        $player = $user->getPlayer();
        $user2 = new User();
        $player2 = $user2->getPlayer();
        $lobby = new Lobby();
        $lobby->setCreator($player);
        $lobby->addPlayer($player);
        $lobby->getConfiguration()->setMaxPlayers(1);

        $lobbyService = new LobbyService($this->registry->reveal(), $this->tokenStorage->reveal(),
            $this->lobbyRepository->reveal());

        $lobbyService->join($lobby, $player);

        $this->assertTrue($lobby->hasPlayer($player));

        $this->expectException(RuntimeException::class);

        $lobbyService->join($lobby, $player2);
    }

    public function testGetNextAdversary(): void
    {
        $user = new User();
        $player = $user->getPlayer();
        $user2 = new User();
        $player2 = $user2->getPlayer();
        $user3 = new User();
        $player3 = $user3->getPlayer();
        $lobby = new Lobby();
        $lobby->setCreator($player);
        $lobby->addPlayer($player);
        $lobby->addPlayer($player2);
        $lobby->addPlayer($player3);

        $match = new Match();
        $match->setPlayer1($player);
        $match->setPlayer2($player2);
        $set = new Set();
        $set->addMatch($match);
        $lobby->addSet($set);
        $lobbyService = new LobbyService($this->registry->reveal(), $this->tokenStorage->reveal(),
            $this->lobbyRepository->reveal());
        $adversary = $lobbyService->getNextAdversary($lobby);

        $this->assertSame($adversary, $player3);

        $lobby->removePlayer($player3);
        $adversary = $lobbyService->getNextAdversary($lobby);
        $this->assertNull($adversary);
    }

    protected function tearDown(): void
    {
        $this->prophet->checkPredictions();
    }
}
