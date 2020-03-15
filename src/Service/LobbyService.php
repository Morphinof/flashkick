<?php

declare(strict_types=1);

namespace Flashkick\Service;

use Doctrine\Persistence\ManagerRegistry;
use Flashkick\Entity\Lobby;
use Flashkick\Entity\Player;
use LogicException;
use RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LobbyService
{
    private ManagerRegistry $registry;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        ManagerRegistry $registry,
        TokenStorageInterface $tokenStorage
    ) {
        $this->registry = $registry;
        $this->tokenStorage = $tokenStorage;
    }

    public function join(Lobby $lobby, Player $player): void
    {
        if ($lobby->getPlayers()->contains($player)) {
            return;
        }

        if ($lobby->getConfiguration()->getMaxPlayers() === $lobby->getPlayers()->count()) {
            $message = sprintf(
                'Lobby %s is full (%d/%d)',
                $lobby->getUuid(),
                $lobby->getPlayers()->count(),
                $lobby->getConfiguration()->getMaxPlayers(),
            );

            throw new RuntimeException($message);
        }

        $lobby->addPlayer($player);
        $this->registry->getManager()->flush();
    }

    public function leave(Lobby $lobby, Player $player): void
    {
        $lobby->removePlayer($player);
        $this->registry->getManager()->flush();
    }

    public function kick(Lobby $lobby, Player $player): void
    {
        $token = $this->tokenStorage->getToken();
        assert($token !== null);

        $user = $token->getUser();
        assert($user !== null);

        if ($user->getPlayer() !== $lobby->getCreator()) {
            throw new RuntimeException('Kick is restricted to lobby creator');
        }

        if ($player === $lobby->getCreator()) {
            throw new LogicException('Lobby creator cannot be kicked');
        }

        $lobby->removePlayer($player);
        $this->registry->getManager()->flush();
    }
}