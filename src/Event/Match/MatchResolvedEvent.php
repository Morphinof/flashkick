<?php

declare(strict_types=1);

namespace Flashkick\Event\Match;

use Flashkick\Entity\Match;
use Symfony\Contracts\EventDispatcher\Event;

class MatchResolvedEvent extends Event
{
    public const NAME = 'flashkick.event.match.resolved';

    protected Match $match;

    public function __construct(Match $match)
    {
        $this->match = $match;
    }

    public function getMatch(): Match
    {
        return $this->match;
    }
}