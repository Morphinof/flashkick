<?php

declare(strict_types=1);

namespace Flashkick\Services;

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
        if ($lobby->getConfiguration()->getMaxPlayers() === $lobby->getPlayers()->count()) {
            throw new RuntimeException(sprintf('Lobby %s is full', $lobby->getUuid()));
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