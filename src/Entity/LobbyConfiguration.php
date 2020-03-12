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
     * @ORM\ManyToOne(targetEntity=Game::class)
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id")
     */
    private Game $game;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $mode = self::MODE_KOTH;

    public function getGame(): Game
    {
        return $this->game;
    }

    public function setGame(Game $game): void
    {
        $this->game = $game;
    }

    /**
     * @ORM\Column(type="smallint")
     */
    private int $bestOf = 3;

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): void
    {
        $this->mode = $mode;
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
