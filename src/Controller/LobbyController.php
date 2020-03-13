<?php

declare(strict_types=1);

namespace Flashkick\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Flashkick\Entity\Game;
use Flashkick\Entity\Lobby;
use Flashkick\Entity\Player;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class LobbyController extends AbstractController
{
    private EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
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
        $lobby->addPlayer($this->getUser()->getPlayer());
        $this->manager->flush();

        return $this->render('lobby/lobby.html.twig', [
            'lobby' => $lobby,
        ]);
    }

    /**
     * @Route("/lobbies/{lobby}/leave", name="flashkick_lobby_leave")
     */
    public function leave(Lobby $lobby): Response
    {
        $lobby->removePlayer($this->getUser()->getPlayer());
        $this->manager->flush();

        return $this->redirectToRoute('flashkick_lobbies', ['game' => $lobby->getConfiguration()->getGame()->getId()]);
    }

    /**
     * @Route("/lobbies/{lobby}/kick/{player}", name="flashkick_lobby_kick")
     */
    public function kick(Lobby $lobby, Player $player): Response
    {
        if ($this->getUser()->getPlayer() !== $lobby->getCreator()) {
            throw new AccessDeniedException('Action restricted to lobby creator');
        }

        if ($player === $lobby->getCreator()) {
            throw new AccessDeniedException('Lobby creator cannot be kicked');
        }

        $lobby->removePlayer($player);
        $this->manager->flush();

        return $this->render('lobby/lobby.html.twig', [
            'lobby' => $lobby,
        ]);
    }
}
