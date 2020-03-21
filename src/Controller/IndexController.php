<?php

namespace Flashkick\Controller;

use Flashkick\Entity\Game;
use Flashkick\Repository\GameRepository;
use Flashkick\Service\StatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    private StatsService $statsService;
    private GameRepository $gameRepository;

    public function __construct(StatsService $statsService, GameRepository $gameRepository)
    {
        $this->statsService = $statsService;
        $this->gameRepository = $gameRepository;
    }

    /**
     * @Route(path="/", name="homepage")
     */
    public function index(): Response
    {
        $user = $this->getUser();
        assert($user !== null);
        $player = $user->getPlayer();
        $sf5 = $this->gameRepository->findOneBy(['name' => Game::GAME_SF5]);
        assert($sf5 !== null);

        $globalStats = $this->statsService->getGlobalStatistics($player);
        $sf5Stats = $this->statsService->getGameStatistics($player, $sf5);

        return $this->render('index/index.html.twig', [
            'global_stats' => $globalStats,
            'sf5_stats' => $sf5Stats,
        ]);
    }
}
