<?php

namespace Flashkick\Controller;

use Flashkick\Entity\Character;
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
    private MatchService $matchService;

    public function __construct(
        LobbyRepository $lobbyRepository,
        MatchService $matchService
    )
    {
        $this->lobbyRepository = $lobbyRepository;
        $this->matchService = $matchService;
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
     * @Route("/match/{match}/select-character/{player}/{character}", name="match_select_character", defaults={"character"=null})
     */
    public function selectCharacter(Match $match, Player $player, ?Character $character): Response
    {
        $lobby = $this->lobbyRepository->getByMatch($match);
        assert($lobby !== null);

        $this->matchService->selectCharacter($match, $player, $character);

        return $this->redirectToRoute('flashkick_lobby_join', ['lobby' => $lobby->getId()]);
    }

    /**
     * @Route("/match/{match}/resolve/{player}/{resolution}", name="match_resolve")
     */
    public function resolve(Match $match, Player $player, int $resolution, MatchService $matchService): Response
    {
        $lobby = $this->lobbyRepository->findByMatch($match);
        assert($lobby !== null);

        $matchService->resolve($match, $player, $resolution);

        return $this->redirectToRoute('flashkick_lobby_join', ['lobby' => $lobby->getId()]);
    }
}
