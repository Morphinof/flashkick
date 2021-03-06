<?php

declare(strict_types=1);

namespace Flashkick\Controller;

use Flashkick\Entity\Game;
use Flashkick\Entity\Lobby;
use Flashkick\Entity\Player;
use Flashkick\Repository\CharacterRepository;
use Flashkick\Service\LobbyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LobbyController extends AbstractController
{
    private LobbyService $lobbyService;
    private CharacterRepository $characterRepository;

    public function __construct(
        LobbyService $lobbyService,
        CharacterRepository $characterRepository
    ) {
        $this->lobbyService = $lobbyService;
        $this->characterRepository = $characterRepository;
    }

    /**
     * @Route("/lobbies/{game}", name="flashkick_lobbies")
     */
    public function index(Game $game): Response
    {
        $lobbies = $this->getDoctrine()->getRepository(Lobby::class)->findByGame($game);

        return $this->render('lobby/index.html.twig', [
            'lobbies' => $lobbies,
        ]);
    }

    /**
     * @Route("/lobbies/{lobby}/join", name="flashkick_lobby_join")
     */
    public function join(Lobby $lobby): Response
    {
        assert($this->getUser() !== null);

        $this->lobbyService->join($lobby, $this->getUser()->getPlayer());
        $characters = $this->characterRepository->findBy(['game' => $lobby->getConfiguration()->getGame()]);

        return $this->render('lobby/lobby.html.twig', [
            'lobby' => $lobby,
            'characters' => $characters,
        ]);
    }

    /**
     * @Route("/lobbies/{lobby}/leave", name="flashkick_lobby_leave")
     */
    public function leave(Lobby $lobby): Response
    {
        assert($this->getUser() !== null);

        $this->lobbyService->leave($lobby, $this->getUser()->getPlayer());

        return $this->redirectToRoute('flashkick_lobbies', ['game' => $lobby->getConfiguration()->getGame()->getId()]);
    }

    /**
     * @Route("/lobbies/{lobby}/kick/{player}", name="flashkick_lobby_kick")
     */
    public function kick(Lobby $lobby, Player $player): Response
    {
        assert($this->getUser() !== null);

        $this->lobbyService->kick($lobby, $player);

        return $this->render('lobby/lobby.html.twig', [
            'lobby' => $lobby,
        ]);
    }
}
