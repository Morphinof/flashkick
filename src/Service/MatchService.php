<?php

declare(strict_types=1);

namespace Flashkick\Service;

use Doctrine\Persistence\ManagerRegistry;
use Flashkick\Entity\Character;
use Flashkick\Entity\Match;
use Flashkick\Entity\MatchResolution;
use Flashkick\Entity\Player;
use Flashkick\Event\Match\MatchResolvedEvent;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MatchService
{
    private ManagerRegistry $registry;
    private EventDispatcherInterface $dispatcher;

    public function __construct(ManagerRegistry $registry, EventDispatcherInterface $dispatcher)
    {
        $this->registry = $registry;
        $this->dispatcher = $dispatcher;
    }

    public function resolve(Match $match, Player $player, int $validation): void
    {
        if (!$match->isPlayer($player)) {
            throw new InvalidArgumentException('Invalid player');
        }

        if (!in_array($validation, MatchResolution::VALIDATIONS, true)) {
            throw new InvalidArgumentException(sprintf('Invalid validation "%s"', $validation));
        }

        $resolution = $match->getResolution();
        if ($match->getPlayer1() === $player) {
            $resolution->setValidationP1($validation);
        }

        if ($match->getPlayer2() === $player) {
            $resolution->setValidationP2($validation);
        }

        $this->checkConflicts($match);

        if ($match->getResolution()->getValidationP1() !== null && $match->getResolution()->getValidationP2() !== null) {
            $this->end($match);
            $this->dispatcher->dispatch(new MatchResolvedEvent($match), MatchResolvedEvent::NAME);
        }

        $this->registry->getManager()->flush();
    }

    public function end(Match $match): void
    {
        $match->setEnded();
        if ($match->getResolution()->getValidationP1() === MatchResolution::WIN) {
            $match->setWinner($match->getPlayer1());
        } else if ($match->getResolution()->getValidationP2() === MatchResolution::WIN) {
            $match->setWinner($match->getPlayer2());
        }

        // Not sure to keep this logic, maybe it better to trigger a conflict
        if ($match->getWinner() !== null) {
            if ($match->getResolution()->getValidationP1() === MatchResolution::DRAW) {
                $match->getResolution()->setValidationP1(MatchResolution::LOOSE);
            }

            if ($match->getResolution()->getValidationP2() === MatchResolution::DRAW) {
                $match->getResolution()->setValidationP2(MatchResolution::LOOSE);
            }
        }

        $this->registry->getManager()->flush();
    }

    public function reset(Match $match): void
    {
        $resolution = $match->getResolution();
        $resolution->setValidationP1(null);
        $resolution->setValidationP2(null);

        $this->registry->getManager()->flush();
    }

    private function checkConflicts(Match $match): void
    {
        $resolution = $match->getResolution();
        if ($resolution->getValidationP1() === $resolution->getValidationP2()) {
            if ($resolution->getValidationP1() !== MatchResolution::DRAW) {
                throw new LogicException(sprintf('Conflict detected on resolution of match %s', $match->getUuid()));
            }
        }
    }

    public function selectCharacter(Match $match, Player $player, ?Character $character = null): void
    {
        if ($match->getPlayer1() === $player) {
            $match->setPlayer1Character($character);
        }

        if ($match->getPlayer2() === $player) {
            $match->setPlayer2Character($character);
        }

        $this->registry->getManager()->flush();
    }
}