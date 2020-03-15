<?php

declare(strict_types=1);

namespace Flashkick\Event\Subscriber;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Flashkick\Entity\Match;
use Flashkick\Event\Match\MatchResolvedEvent;
use Flashkick\Repository\SetRepository;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MatchSubscriber implements EventSubscriberInterface
{
    private SetRepository $setRepository;
    private ManagerRegistry $registry;

    public function __construct(
        SetRepository $setRepository,
        ManagerRegistry $registry
    ) {
        $this->setRepository = $setRepository;
        $this->registry = $registry;
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
     * @throws NonUniqueResultException
     */
    public function postMatchValidation(MatchResolvedEvent $event): void
    {
        $match = $event->getMatch();
        // Need to create a new match and checks if the set is ended
        $set = $this->setRepository->getByMatch($match);

        if ($set !== null) {
            // TODO: dispatch a event to do those 2
            if ($set->getMatches()->count() < $set->getBestOf()) {
                $next = new Match();
                $next->setPlayer1($match->getPlayer1());
                $next->setPlayer1Character($match->getPlayer1Character());
                $next->setPlayer2($match->getPlayer2());
                $next->setPlayer2Character($match->getPlayer2Character());
                $set->addMatch($next);

                $this->registry->getManager()->persist($next);
            }

            if ($set->getMatches()->count() === $set->getBestOf()) {
                throw new RuntimeException(sprintf('SET ENDED, NEED TO CREATE A NEW SET'));
            }
        }
    }
}
