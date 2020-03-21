<?php

declare(strict_types=1);

namespace Flashkick\Service;

use Flashkick\Entity\Game;
use Flashkick\Entity\Player;
use Flashkick\Repository\LobbyRepository;
use Flashkick\Repository\MatchRepository;

class StatsService
{
    private LobbyRepository $lobbyRepository;
    private MatchRepository $matchRepository;

    public function __construct(
        LobbyRepository $lobbyRepository,
        MatchRepository $matchRepository
    ) {
        $this->matchRepository = $matchRepository;
        $this->lobbyRepository = $lobbyRepository;
    }

    public function getGlobalStatistics(Player $player): iterable
    {
        $globals = $this->getGlobalWinsLosesDraws($player);

        return ['global_wld' => $globals,];
    }

    public function getGameStatistics(Player $player, Game $game): iterable
    {
        $wins = 0;
        $losses = 0;
        $draws = 0;
        $lobbies = $this->lobbyRepository->findByPlayerAndGame($player, $game);

        foreach ($lobbies as $lobby) {
            foreach ($lobby->getSets() as $set) {
                foreach ($set->getMatches() as $match) {
                    if ($match->getWinner() === $player) {
                        ++$wins;
                        continue;
                    }

                    if ($match->isDraw()) {
                        ++$draws;
                        continue;
                    }

                    ++$losses;
                }
            }
        }

        return [
            'wins' => $wins,
            'losses' => $losses,
            'draws' => $draws,
            'ratio' => $wins / ($losses + $draws) * 100,
            'total' => $wins + $losses + $draws,
        ];
    }

    public function getGlobalWinsLosesDraws(Player $player): iterable
    {
        $wins = 0;
        $losses = 0;
        $draws = 0;
        $matches = $this->matchRepository->findByPlayer($player);

        foreach ($matches as $match) {
            if ($match->getWinner() === $player) {
                ++$wins;
                continue;
            }

            if ($match->isDraw()) {
                ++$draws;
                continue;
            }

            ++$losses;
        }

        return [
            'wins' => $wins,
            'losses' => $losses,
            'draws' => $draws,
            'ratio' => $wins / ($losses + $draws) * 100,
            'total' => $wins + $losses + $draws,
        ];
    }
}