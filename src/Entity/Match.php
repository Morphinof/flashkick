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
     * @ORM\ManyToOne(targetEntity=Character::class)
     * @ORM\JoinColumn(name="player_1_character", referencedColumnName="id", nullable=true)
     */
    private ?Character $player1Character;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class)
     * @ORM\JoinColumn(name="player_2", referencedColumnName="id")
     */
    private Player $player2;

    /**
     * @ORM\ManyToOne(targetEntity=Character::class)
     * @ORM\JoinColumn(name="player_2_character", referencedColumnName="id", nullable=true)
     */
    private ?Character $player2Character;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class)
     * @ORM\JoinColumn(name="winner", referencedColumnName="id", nullable=true)
     */
    private ?Player $winner;

    /**
     * @ORM\OneToOne(targetEntity=MatchResolution::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="resolution_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private MatchResolution $resolution;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $ended = false;

    public function __construct()
    {
        $this->resolution = new MatchResolution();
    }

    public function getPlayer1(): Player
    {
        return $this->player1;
    }

    public function setPlayer1(Player $player1): void
    {
        $this->player1 = $player1;
    }

    public function getPlayer1Character(): ?Character
    {
        return $this->player1Character;
    }

    public function setPlayer1Character(?Character $player1Character): void
    {
        $this->player1Character = $player1Character;
    }

    public function getPlayer2(): Player
    {
        return $this->player2;
    }

    public function setPlayer2(Player $player2): void
    {
        $this->player2 = $player2;
    }

    public function getPlayer2Character(): ?Character
    {
        return $this->player2Character;
    }

    public function setPlayer2Character(?Character $player2Character): void
    {
        $this->player2Character = $player2Character;
    }

    public function getWinner(): ?Player
    {
        return $this->winner;
    }

    public function setWinner(?Player $winner): void
    {
        $this->winner = $winner;
    }

    public function getResolution(): MatchResolution
    {
        return $this->resolution;
    }

    public function setResolution(MatchResolution $resolution): void
    {
        $this->resolution = $resolution;
    }

    public function isEnded(): bool
    {
        return $this->ended;
    }

    public function setEnded(bool $ended = true): void
    {
        $this->ended = $ended;
    }

    public function isPlayer(Player $player): bool
    {
        return $this->player1 === $player || $this->player2 === $player;
    }
}
