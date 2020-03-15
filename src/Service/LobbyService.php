<?php

declare(strict_types=1);

namespace Flashkick\Service;

use Doctrine\Persistence\ManagerRegistry;
use Flashkick\Entity\Lobby;
use Flashkick\Entity\Match;
use Flashkick\Entity\Player;
use Flashkick\Entity\Set;
use LogicException;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
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

    public function getNextAdversary(Lobby $lobby): ?Player
    {
        /** @var Set $lastSet */
        $lastSet = $lobby->getSets()[$lobby->getSets()->count() - 1];
        assert($lastSet !== null);

        /** @var Match $lastMatch */
        $lastMatch = $lastSet->getMatches()->last();

        $players = $lobby->getPlayers();

        $players = $players->filter(static function (Player $player) use ($lobby, $lastMatch): Player {
            if ($player !== $lastMatch->getPlayer1() || $player !== $lastMatch->getPlayer2()) {
                return $player;
            }

            return null;
        });

        $players = array_filter($players->toArray());

        return $players[0] ?? null;
    }
}