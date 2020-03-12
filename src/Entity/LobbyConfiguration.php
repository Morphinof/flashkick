<?php

declare(strict_types=1);

namespace Flashkick\Entity;

use Doctrine\ORM\Mapping as ORM;
use Flashkick\Traits\EntityTrait;

/**
 * @ORM\Entity(repositoryClass="Flashkick\Repository\LobbyConfigurationRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class LobbyConfiguration
{
    public const MODE_KOTH = 'king-of-the-hill';

    use EntityTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class)
     * @ORM\JoinColumn(name="admin_id", referencedColumnName="id")
     */
    private Player $admin;

    /**
     * @ORM\ManyToOne(targetEntity=Game::class)
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id")
     */
    private Game $game;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $mode = self::MODE_KOTH;

    /**
     * @ORM\Column(type="smallint")
     */
    private int $maxPlayers;

    /**
     * @ORM\Column(type="smallint")
     */
    private int $bestOf;

    public function getAdmin(): Player
    {
        return $this->admin;
    }

    public function setAdmin(Player $admin): void
    {
        $this->admin = $admin;
    }

    /**
     * @return Game
     */
    public function getGame(): Game
    {
        return $this->game;
    }

    public function setGame(Game $game): void
    {
        $this->game = $game;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): void
    {
        $this->mode = $mode;
    }

    public function getMaxPlayers(): int
    {
        return $this->maxPlayers;
    }

    public function setMaxPlayers(int $maxPlayers): void
    {
        $this->maxPlayers = $maxPlayers;
    }

    public function getBestOf(): int
    {
        return $this->bestOf;
    }

    public function setBestOf(int $bestOf): void
    {
        $this->bestOf = $bestOf;
    }
}
