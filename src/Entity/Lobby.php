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

    /**
     * @ORM\ManyToMany(targetEntity=Player::class)
     * @ORM\JoinTable(
     *     name="lobbies_players",
     *     joinColumns={@ORM\JoinColumn(name="lobby_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="player_id", referencedColumnName="id", unique=true)}
     * )
     */
    private Collection $players;

    public function __construct()
    {
        $this->players = new ArrayCollection();
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
     * @return Collection|Player[]
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): void
    {
        if (!$this->players->contains($player)) {
            $this->players[] = $player;
        }
    }

    public function removePlayer(Player $player): void
    {
        if ($this->players->contains($player)) {
            $this->players->removeElement($player);
        }
    }

    public function __toString(): string
    {
        return sprintf(
            '%s - %s (%d/%d)',
            $this->uuid,
            $this->configuration->getGame(),
            $this->players->count(),
            $this->configuration->getMaxPlayers(),
        );
    }
}
