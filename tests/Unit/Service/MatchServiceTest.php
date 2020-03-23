<?php
//
//declare(strict_types=1);
//
//namespace Flashkick\Tests\Unit\Service;
//
//
//use Doctrine\ORM\EntityManager;
//use Doctrine\ORM\OptimisticLockException;
//use Doctrine\ORM\ORMException;
//use Doctrine\Persistence\ManagerRegistry;
//use Flashkick\Entity\Lobby;
//use Flashkick\Entity\Match;
//use Flashkick\Entity\Set;
//use Flashkick\Entity\User;
//use Flashkick\Repository\LobbyRepository;
//use Flashkick\Service\LobbyService;
//use Prophecy\Prophecy\ObjectProphecy;
//use Prophecy\Prophet;
//use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
//use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
//use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
//
//class MatchServiceTest extends KernelTestCase
//{
//    private Prophet $prophet;
//    private ObjectProphecy $registry;
//    private ObjectProphecy $tokenStorage;
//    private ObjectProphecy $lobbyRepository;
//    private ObjectProphecy $em;
//    private ObjectProphecy $token;
//
//    /**
//     * @throws ORMException
//     * @throws OptimisticLockException
//     */
//    protected function setUp(): void
//    {
//        self::$kernel = self::bootKernel();
//
//        $this->registry = $this->prophesize(ManagerRegistry::class);
//        $this->tokenStorage = $this->prophesize(TokenStorageInterface::class);
//        $this->lobbyRepository = $this->prophesize(LobbyRepository::class);
//        $this->em = $this->prophesize(EntityManager::class);
//        $this->em->flush()->willReturn(null);
//        $this->registry->getManager()->willReturn($this->em->reveal());
//        $this->token = $this->prophesize(TokenInterface::class);
//
//        $this->prophet = new Prophet;
//    }
//
//    public function testGetNextAdversary(): void
//    {
//        $creator = new User();
//        $playerCreator = $creator->getPlayer();
//        $user2 = new User();
//        $player2 = $user2->getPlayer();
//        $user3 = new User();
//        $player3 = $user3->getPlayer();
//        $user4 = new User();
//        $player4 = $user4->getPlayer();
//        $lobby = new Lobby();
//        $lobby->setCreator($playerCreator);
//        $lobby->addPlayer($playerCreator);
//        $lobby->addPlayer($player2);
//        $lobby->addPlayer($player3);
//        $lobby->addPlayer($player4);
//
//        $set = new Set();
//        $match1 = new Match();
//        $match1->setPlayer1($playerCreator);
//        $match1->setPlayer2($player2);
//        $match1->setWinner($playerCreator);
//
//        $match2 = new Match();
//        $match2->setPlayer1($playerCreator);
//        $match2->setPlayer2($player2);
//        $match2->setWinner($playerCreator);
//
//        $set->addMatch($match1);
//        $set->addMatch($match2);
//        $set->setWinner($playerCreator);
//        $set->setEnded();
//
//        $lobby->addSet($set);
//
//        $lobbyService = new LobbyService($this->registry->reveal(), $this->tokenStorage->reveal(),
//            $this->lobbyRepository->reveal());
//
//        $adversary = $lobbyService->getNextAdversary($lobby);
////        dd($adversary, $adversary === $playerCreator, $adversary === $player2, $adversary === $player3, $adversary === $player4);
//
//        $this->assertSame($adversary, $player3);
//
//        $set = new Set();
//        $match1 = new Match();
//        $match1->setPlayer1($playerCreator);
//        $match1->setPlayer2($player3);
//        $match1->setWinner($player3);
//
//        $match2 = new Match();
//        $match2->setPlayer1($playerCreator);
//        $match2->setPlayer2($player3);
//        $match2->setWinner($player3);
//
//        $set->addMatch($match1);
//        $set->addMatch($match2);
//        $set->setWinner($player3);
//        $set->setEnded();
//
//        $lobby->addSet($set);
//
//        $adversary = $lobbyService->getNextAdversary($lobby);
//        dd($adversary, $adversary === $playerCreator, $adversary === $player2, $adversary === $player3, $adversary === $player4);
//        $this->assertSame($adversary, $player4);
//    }
//
//    protected function tearDown(): void
//    {
//        $this->prophet->checkPredictions();
//    }
//}