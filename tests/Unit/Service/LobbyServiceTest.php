<?php

declare(strict_types=1);

namespace Flashkick\Service\Tests\Functional\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Flashkick\Entity\Lobby;
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

    protected function tearDown(): void
    {
        $this->prophet->checkPredictions();
    }
}
