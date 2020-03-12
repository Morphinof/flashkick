<?php

declare(strict_types=1);

namespace Flashkick\Controller;

use Flashkick\Entity\Game;
use Flashkick\Entity\Lobby;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LobbyController extends AbstractController
{
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
}
