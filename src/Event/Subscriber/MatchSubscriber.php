<?php

declare(strict_types=1);

namespace Flashkick\Event\Subscriber;

use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Flashkick\Entity\Match;
use Flashkick\Entity\Player;
use Flashkick\Entity\Set;
use Flashkick\Event\Match\MatchResolvedEvent;
use Flashkick\Repository\LobbyRepository;
use Flashkick\Repository\SetRepository;
use Flashkick\Service\LobbyService;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MatchSubscriber implements EventSubscriberInterface
{
    private SetRepository $setRepository;
    private LobbyRepository $lobbyRepository;
    private ManagerRegistry $registry;
    private LobbyService $lobbyService;

    public function __construct(
        SetRepository $setRepository,
        LobbyRepository $lobbyRepository,
        ManagerRegistry $registry,
        LobbyService $lobbyService
    ) {
        $this->setRepository = $setRepository;
        $this->lobbyRepository = $lobbyRepository;
        $this->registry = $registry;
        $this->lobbyService = $lobbyService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MatchResolvedEvent::NAME => [
                ['postMatchValidation', 0],
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function postMatchValidation(MatchResolvedEvent $event): void
    {
        $match = $event->getMatch();
        $lobby = $this->lobbyRepository->findByMatch($match);
        assert($lobby !== null);

        $set = $this->setRepository->getByMatch($match);

        // Need to create a new match and checks if the set is ended
        if ($set !== null) {
            $winsP1 = $set->countResolutionsByPlayer($match->getPlayer1());
            $winsP2 = $set->countResolutionsByPlayer($match->getPlayer2());
            $isWinner = $winsP1 === $set->getBestOf() - 1 || $winsP2 === $set->getBestOf() - 1;

            if ($isWinner) {
                $set->setWinner($match->getWinner());
            }

            // Close the set if won or draw
            $isDrawSet = $set->isDraw();
            if ($isDrawSet || $isWinner) {
                $set->setEnded();
            }

            // Creates a new match if needed
            if (!$isWinner && !$isDrawSet) {
                $next = $this->createMatch($match->getPlayer1(), $match->getPlayer2());
                $next->setPlayer1Character($match->getPlayer1Character());
                $next->setPlayer2Character($match->getPlayer2Character());
                $set->addMatch($next);

                $this->registry->getManager()->persist($next);
            }

            // Creates a new set with the next match if needed
            if ($isDrawSet || $set->getWinner() !== null) {
                $set->setWinner($match->getWinner());
                $set->setEnded();

                $newSet = new Set();
                $newSet->setUuid(Uuid::uuid4()->toString());
                $newSet->setBestOf($lobby->getConfiguration()->getBestOf());
                $lobby->addSet($newSet);

                $player1 = $match->getWinner() ?? $match->getPlayer1();
                $player2 = $this->lobbyService->getNextAdversary($lobby);

                if ($player2 !== null) {
                    $winnerCharacter = null;
                    if ($match->getPlayer1() === $match->getWinner()) {
                        $winnerCharacter = $match->getPlayer1Character();
                    }

                    if ($match->getPlayer2() === $match->getWinner()) {
                        $winnerCharacter = $match->getPlayer2Character();
                    }

                    $first = $this->createMatch($player1, $player2);
                    $first->setPlayer1Character($winnerCharacter);
                    $newSet->addMatch($first);

                    $this->registry->getManager()->persist($newSet);
                    $this->registry->getManager()->persist($first);
                }
            }
        }
    }

    private function createMatch(Player $player1, Player $player2): Match
    {
        $match = new Match();
        $match->setPlayer1($player1);
        $match->setPlayer2($player2);

        return $match;
    }
}
