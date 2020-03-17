<?php

declare(strict_types=1);

namespace Flashkick\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Flashkick\Traits\EntityWithUuidTrait;

/**
 * @ORM\Entity(repositoryClass="Flashkick\Repository\SetRepository")
 * @ORM\Table(name="`set`")
 * @ORM\HasLifecycleCallbacks()
 */
class Set
{
    use EntityWithUuidTrait;

    /**
     * @ORM\Column(type="smallint")
     */
    private int $bestOf = 3;

    /**
     * @ORM\ManyToMany(targetEntity=Match::class)
     * @ORM\JoinTable(
     *     name="sets_matches",
     *     joinColumns={@ORM\JoinColumn(name="set_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="match_id", referencedColumnName="id", unique=true)}
     * )
     */
    private Collection $matches;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class)
     * @ORM\JoinColumn(name="winner", referencedColumnName="id", nullable=true)
     */
    private ?Player $winner = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $ended = false;

    public function __construct()
    {
        $this->matches = new ArrayCollection();
    }

    public function getBestOf(): int
    {
        return $this->bestOf;
    }

    public function setBestOf(int $bestOf): void
    {
        $this->bestOf = $bestOf;
    }

    public function getMatches(): Collection
    {
        return $this->matches;
    }

    public function addMatch(Match $match): void
    {
        if (!$this->matches->contains($match)) {
            $this->matches[] = $match;
        }
    }

    public function removeMatch(Set $set): void
    {
        if ($this->matches->contains($set)) {
            $this->matches->removeElement($set);
        }
    }

    public function getWinner(): ?Player
    {
        return $this->winner;
    }

    public function setWinner(?Player $winner): void
    {
        $this->winner = $winner;
    }

    public function isEnded(): bool
    {
        return $this->ended;
    }

    public function setEnded(bool $ended = true): void
    {
        $this->ended = $ended;
    }

    public function countResolutionsByPlayer(?Player $player = null, int $resolution = MatchResolution::WIN): int
    {
        $count = 0;
        foreach ($this->matches as $match) {
            if ($player === $match->getPlayer1() && $match->getResolution()->getValidationP1() === $resolution) {
                ++$count;
            }

            if ($player === $match->getPlayer2() && $match->getResolution()->getValidationP2() === $resolution) {
                ++$count;
            }
        }

        return $count;
    }
}
