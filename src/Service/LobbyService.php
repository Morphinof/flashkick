<?php

declare(strict_types=1);

namespace Flashkick\Service;

use Doctrine\ORM\EntityManagerInterface;
use Flashkick\Entity\Lobby;
use Flashkick\Entity\Player;
use LogicException;
use RuntimeException;

class LobbyService
{
    private EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
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
        $this->manager->flush();
    }

    public function leave(Lobby $lobby, Player $player): void
    {
        $lobby->removePlayer($player);
        $this->manager->flush();
    }

    public function kick(Lobby $lobby, Player $player): void
    {
        if ($player !== $lobby->getCreator()) {
            throw new RuntimeException('Kick is restricted to lobby creator');
        }

        if ($player === $lobby->getCreator()) {
            throw new LogicException('Lobby creator cannot be kicked');
        }

        $lobby->removePlayer($player);
        $this->manager->flush();
    }
}