<?php

declare(strict_types=1);

namespace Flashkick\Twig;

use Flashkick\Entity\Game;
use Flashkick\Repository\LobbyRepository;
use Twig\Extension\RuntimeExtensionInterface;

class LobbyExtension implements RuntimeExtensionInterface
{
    private LobbyRepository $lobbyRepository;

    public function __construct(
        LobbyRepository $lobbyRepository
    )
    {
        $this->lobbyRepository = $lobbyRepository;
    }

    public function getByGame(Game $game): array
    {
        return $this->lobbyRepository->findByGame($game);
    }
}
