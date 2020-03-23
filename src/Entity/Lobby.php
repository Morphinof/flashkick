<?php

namespace Flashkick\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Flashkick\Traits\EntityWithUuidTrait;

/**
 * @ORM\Entity(repositoryClass="Flashkick\Repository\LobbyRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Lobby
{
    public const MODE_PUBLIC = 1;
    public const MODE_PRIVATE = 0;

    use EntityWithUuidTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class)
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     */
    private Player $creator;

    /**
     * @ORM\ManyToOne(targetEntity=LobbyConfiguration::class)
     * @ORM\JoinColumn(name="lobby_configuration_id", referencedColumnName="id")
     */
    private LobbyConfiguration $configuration;
//
//    /**
//     * @var Player[]|Collection
//     * @ORM\ManyToMany(targetEntity=Player::class)
//     * @ORM\JoinTable(
//     *     name="lobbies_players",
//     *     joinColumns={@ORM\JoinColumn(name="lobby_id", referencedColumnName="id")},
//     *     inverseJoinColumns={@ORM\JoinColumn(name="player_id", referencedColumnName="id", unique=true)}
//     * )
//     */
//    private Collection $players;

    /**
     * @var LobbyPlayer[]|Collection
     * @ORM\ManyToMany(targetEntity=LobbyPlayer::class, cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinTable(
     *     joinColumns={@ORM\JoinColumn(name="lobby_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="lobby_player_id", referencedColumnName="id", unique=true)}
     * )
     */
    private Collection $players;

    /**
     * @var Set[]|Collection
     * @ORM\ManyToMany(targetEntity=Set::class)
     * @ORM\JoinTable(
     *     name="lobbies_sets",
     *     joinColumns={@ORM\JoinColumn(name="lobby_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="set_id", referencedColumnName="id", unique=true)}
     * )
     */
    private Collection $sets;

    /**
     * @ORM\Column(type="smallint")
     */
    private int $mode = self::MODE_PUBLIC;

    public function __construct()
    {
        $this->configuration = new LobbyConfiguration();
        $this->players = new ArrayCollection();
        $this->sets = new ArrayCollection();
//        $this->rotation = new ArrayCollection();
    }

    public function getCreator(): Player
    {
        return $this->creator;
    }

    public function setCreator(Player $creator): void
    {
        $this->creator = $creator;
    }

    public function getConfiguration(): LobbyConfiguration
    {
        return $this->configuration;
    }

    public function setConfiguration(LobbyConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * @return LobbyPlayer|Collection
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    /**
     * @return Set[]|Collection
     */
    public function getSets(): Collection
    {
        return $this->sets;
    }

    public function addSet(Set $set): void
    {
        if (!$this->players->contains($set)) {
            $this->sets[] = $set;
        }
    }

    public function removeSet(Set $set): void
    {
        if ($this->sets->contains($set)) {
            $this->sets->removeElement($set);
        }
    }

    public function hasPlayer(Player $player): bool
    {
        foreach ($this->players as $lobbyPlayer) {
            if ($lobbyPlayer->getPlayer() === $player) {
                return true;
            }
        }

        return false;
    }

    public function addPlayer(Player $player): void
    {
        foreach ($this->players as $lobbyPlayer) {
            if ($lobbyPlayer->getPlayer() === $player) {
                return;
            }
        }

        $this->players[] = new LobbyPlayer($player, $this->players->count());
    }

    public function removePlayer(Player $player): void
    {
        foreach ($this->players as $lobbyPlayer) {
            if ($lobbyPlayer->getPlayer() === $player) {
                $this->players->removeElement($lobbyPlayer);
                return;
            }
        }
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function setMode(int $mode): void
    {
        $this->mode = $mode;
    }

    public function countMatches(): int
    {
        $count = 0;
        foreach ($this->sets as $set) {
            $count += $set->getMatches()->count();
        }

        return $count;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s [%d/%d]',
            $this->configuration->getGame(),
            $this->players->count(),
            $this->configuration->getMaxPlayers(),
        );
    }
}
