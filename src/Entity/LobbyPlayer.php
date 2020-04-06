<?php

declare(strict_types=1);

namespace Flashkick\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Flashkick\Traits\EntityTrait;

/**
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class LobbyPlayer
{
    use EntityTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class)
     * @ORM\JoinColumn(name="player_id", referencedColumnName="id")
     */
    private Player $player;

    /**
     * @ORM\Column(type="smallint")
     * @Gedmo\SortablePosition(value="DESC")
     */
    private int $position;

    public function __construct(Player $player, int $position = 0)
    {
        $this->player = $player;
        $this->position = $position;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }
}
