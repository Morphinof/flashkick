<?php

declare(strict_types=1);

namespace Flashkick\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FlashkickExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('flashkick_games', [GameExtension::class, 'getGames']),
            new TwigFunction('flashkick_lobbies_by_game', [LobbyExtension::class, 'getByGame']),
        ];
    }
}
