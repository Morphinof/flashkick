<?php

declare(strict_types=1);

namespace Flashkick\Service;

use Doctrine\Persistence\ManagerRegistry;
use Flashkick\Entity\Lobby;
use Flashkick\Entity\LobbyPlayer;
use Flashkick\Entity\Match;
use Flashkick\Entity\Player;
use Flashkick\Entity\Set;
use Flashkick\Repository\LobbyRepository;
use LogicException;
use RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LobbyService
{
    private ManagerRegistry $registry;
    private TokenStorageInterface $tokenStorage;
    private LobbyRepository $lobbyRepository;

    public function __construct(
        ManagerRegistry $registry,
        TokenStorageInterface $tokenStorage,
        LobbyRepository $lobbyRepository
    ) {
        $this->registry = $registry;
        $this->tokenStorage = $tokenStorage;
        $this->lobbyRepository = $lobbyRepository;
    }

    public function join(Lobby $lobby, Player $player): void
    {
        $this->autoLeave($player);

        if ($lobby->hasPlayer($player)) {
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
        $lastSet = $lobby->getSets()[$lobby->getSets()->count() - 2] ?? $lobby->getSets()->first();
        assert($lastSet !== null);

        /** @var Match $lastMatch */
        $lastMatch = $lastSet->getMatches()->last();

        $players = $lobby->getPlayers();

        $adversaries = $players->filter(static function (LobbyPlayer $lobbyPlayer) use ($lastMatch): ?Player {
            if (!in_array($lobbyPlayer->getPlayer(), [$lastMatch->getPlayer1(), $lastMatch->getPlayer2()], true)) {
                return $lobbyPlayer->getPlayer();
            }

            return null;
        });

        if ($adversaries->count() > 0) {
            return $adversaries->first()->getPlayer();
        }

//        $nextPlayer = $lastMatch->getPlayer1();
//        if ($adversaries->count() === 0 && $lastMatch->getWinner() === $nextPlayer) {
//            $nextPlayer = $lastMatch->getPlayer2();
//        }

//        if ($lobby->getPlayers()->contains($nextPlayer)) {
//            return $nextPlayer;
//        }

        return null;
    }

    private function autoLeave(Player $player): void
    {
        $lobby = $this->lobbyRepository->findByPlayer($player);

        if ($lobby !== null) {
            $this->leave($lobby, $player);
        }
    }
}