<?php

declare(strict_types=1);

namespace Flashkick\Twig;

use Flashkick\Repository\GameRepository;
use Twig\Extension\RuntimeExtensionInterface;

class GameExtension implements RuntimeExtensionInterface
{
    private GameRepository $gameRepository;

    public function __construct(
        GameRepository $gameRepository
    )
    {
        $this->gameRepository = $gameRepository;
    }

    public function getGames(): array
    {
        return $this->gameRepository->findAll();
    }
}
