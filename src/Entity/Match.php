<?php

declare(strict_types=1);

namespace Flashkick\Entity;

use Doctrine\ORM\Mapping as ORM;
use Flashkick\Traits\EntityWithUuidTrait;

/**
 * @ORM\Entity(repositoryClass="Flashkick\Repository\MatchRepository")
 * @ORM\Table(name="`match`")
 * @ORM\HasLifecycleCallbacks()
 */
class Match
{
    use EntityWithUuidTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class)
     * @ORM\JoinColumn(name="player_1", referencedColumnName="id")
     */
    private Player $player1;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class)
     * @ORM\JoinColumn(name="player_2", referencedColumnName="id")
     */
    private Player $player2;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class)
     * @ORM\JoinColumn(name="winner", referencedColumnName="id", nullable=true)
     */
    private ?Player $winner;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $ended = false;

    public function getPlayer1(): Player
    {
        return $this->player1;
    }

    public function setPlayer1(Player $player1): void
    {
        $this->player1 = $player1;
    }

    public function getPlayer2(): Player
    {
        return $this->player2;
    }

    public function setPlayer2(Player $player2): void
    {
        $this->player2 = $player2;
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
}
