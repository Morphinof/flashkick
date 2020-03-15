<?php

declare(strict_types=1);

namespace Flashkick\Entity;

use Doctrine\ORM\Mapping as ORM;
use Flashkick\Traits\EntityTrait;

/**
 * @ORM\Entity(repositoryClass="Flashkick\Repository\CharacterRepository")
 * @ORM\Table(name="`character`")
 * @ORM\HasLifecycleCallbacks()
 */
class Character
{
    use EntityTrait;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $name;

    /**
     * @ORM\ManyToOne(targetEntity=Game::class)
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private Game $game;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function setGame(Game $game): void
    {
        $this->game = $game;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
