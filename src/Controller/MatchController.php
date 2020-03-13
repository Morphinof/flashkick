<?php

namespace Flashkick\Controller;

use Flashkick\Entity\Match;
use Flashkick\Entity\Player;
use Flashkick\Services\MatchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MatchController extends AbstractController
{
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
    public function resolve(Match $match, Player $player, int $resolution, MatchService $matchService): JsonResponse
    {
        $matchService->resolve($match, $player, $resolution);

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
