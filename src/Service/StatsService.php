<?php

declare(strict_types=1);

namespace Flashkick\Service;

use Flashkick\Entity\Character;
use Flashkick\Entity\Game;
use Flashkick\Entity\Player;
use Flashkick\Repository\CharacterRepository;
use Flashkick\Repository\LobbyRepository;
use Flashkick\Repository\MatchRepository;

class StatsService
{
    private LobbyRepository $lobbyRepository;
    private MatchRepository $matchRepository;
    private CharacterRepository $characterRepository;

    public function __construct(
        LobbyRepository $lobbyRepository,
        MatchRepository $matchRepository,
        CharacterRepository $characterRepository
    ) {
        $this->matchRepository = $matchRepository;
        $this->lobbyRepository = $lobbyRepository;
        $this->characterRepository = $characterRepository;
    }

    public function getGlobalStatistics(Player $player): iterable
    {
        $globals = $this->getGlobalWinsLosesDraws($player);

        return ['globals' => $globals,];
    }

    public function getGameStatistics(Player $player, Game $game): iterable
    {
        $wins = 0;
        $loses = 0;
        $draws = 0;
        $lobbies = $this->lobbyRepository->findByPlayerAndGame($player, $game);

        foreach ($lobbies as $lobby) {
            foreach ($lobby->getSets() as $set) {
                foreach ($set->getMatches() as $match) {
                    if (!$match->isEnded()) {
                        continue;
                    }

                    if ($match->getWinner() === $player) {
                        ++$wins;
                        continue;
                    }

                    if ($match->isDraw()) {
                        ++$draws;
                        continue;
                    }

                    ++$loses;
                }
            }
        }

        return [
            'wins' => $wins,
            'loses' => $loses,
            'draws' => $draws,
            'ratio' => $wins > 0 ? round($wins / ($wins + $loses + $draws) * 100) : 0,
            'total' => $wins + $loses + $draws,
        ];
    }

    public function getCharactersStatistics(Player $player, Game $game): iterable
    {
        return $this->characterRepository->getCharactersStatistics($player, $game);
    }

    public function getGlobalWinsLosesDraws(Player $player): iterable
    {
        $wins = 0;
        $loses = 0;
        $draws = 0;
        $matches = $this->matchRepository->findByPlayer($player);

        foreach ($matches as $match) {
            if (!$match->isEnded()) {
                continue;
            }

            if ($match->getWinner() === $player) {
                ++$wins;
                continue;
            }

            if ($match->isDraw()) {
                ++$draws;
                continue;
            }

            ++$loses;
        }

        return [
            'wins' => $wins,
            'loses' => $loses,
            'draws' => $draws,
            'ratio' => $wins > 0 ? round($wins / ($wins + $loses + $draws) * 100) : 0,
            'total' => $wins + $loses + $draws,
        ];
    }
}