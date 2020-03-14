<?php

namespace Flashkick\Controller;

use Flashkick\Entity\Match;
use Flashkick\Entity\Player;
use Flashkick\Repository\LobbyRepository;
use Flashkick\Service\MatchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MatchController extends AbstractController
{
    private LobbyRepository $lobbyRepository;

    public function __construct(LobbyRepository $lobbyRepository)
    {
        $this->lobbyRepository = $lobbyRepository;
    }

    /**
     * @Route("/match/{match}/details", name="match_details")
     */
    public function index(Match $match): Response
    {
        return $this->render('match/details.html.twig', [
            'match' => $match
        ]);
    }

    /**
     * @Route("/match/{match}/resolve/{player}/{resolution}", name="match_resolve")
     */
    public function resolve(Match $match, Player $player, int $resolution, MatchService $matchService): Response
    {
        $lobby = $this->lobbyRepository->getByMatch($match);

        assert($lobby !== null);

        $matchService->resolve($match, $player, $resolution);

        return $this->redirectToRoute('flashkick_lobby_join', ['lobby' => $lobby->getId()]);
    }
}
