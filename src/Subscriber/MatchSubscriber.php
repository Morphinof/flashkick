<?php

declare(strict_types=1);

namespace Flashkick\Subscriber;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Flashkick\Entity\Match;
use Flashkick\Events\Match\MatchResolvedEvent;
use Flashkick\Repository\SetRepository;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MatchSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $manager;
    private SetRepository $setRepository;

    public function __construct(EntityManagerInterface $manager, SetRepository $setRepository)
    {
        $this->manager = $manager;
        $this->setRepository = $setRepository;
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
                $next->setPlayer2($match->getPlayer2());
                $set->addMatch($next);
                $this->manager->flush();
            }

            if ($set->getMatches()->count() === $set->getBestOf()) {
                throw new RuntimeException(sprintf('SET ENDED, NEED TO CREATE A NEW SET'));
            }
        }
    }
}
