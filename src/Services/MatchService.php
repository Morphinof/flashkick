<?php

declare(strict_types=1);

namespace Flashkick\Services;

use Doctrine\ORM\EntityManagerInterface;
use Flashkick\Entity\Match;
use Flashkick\Entity\MatchResolution;
use Flashkick\Entity\Player;
use InvalidArgumentException;
use LogicException;

class MatchService
{
    private EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
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
        } else {
            $resolution->setValidationP2($validation);
        }

        $this->checkConflicts($match);

        $this->manager->flush();
    }

    public function reset(Match $match): void
    {
        $resolution = $match->getResolution();
        $resolution->setValidationP1(null);
        $resolution->setValidationP2(null);

        $this->manager->flush();
    }

    private function checkConflicts(Match $match): void
    {
        $resolution = $match->getResolution();
        if ($resolution->getValidationP1() === $resolution->getValidationP2()) {
            if ($resolution->getDateValidationP1() !== MatchResolution::DRAW) {
                $this->reset($match);

                throw new LogicException(sprintf('Conflict detected on resolution of match %s', $match->getUuid()));
            }
        }
    }
}