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
}
