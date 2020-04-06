<?php
//
//declare(strict_types=1);
//
//namespace Flashkick\Service\Tests\Functional\Controller;
//
//use Doctrine\ORM\EntityManagerInterface;
//use Flashkick\Entity\Lobby;
//use Flashkick\Entity\User;
//use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
//use Symfony\Component\HttpClient\HttpClient;
//use Symfony\Component\HttpFoundation\Cookie;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\HttpFoundation\Session\SessionInterface;
//use Symfony\Component\Routing\RouterInterface;
//use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
//use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
//use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
//use Symfony\Contracts\HttpClient\HttpClientInterface;
//
//class LobbyControllerTest extends WebTestCase
//{
//    private const USER_MORPHINOF = 'morphinof@flashkick.com';
//
//    private EntityManagerInterface $manager;
//    private RouterInterface $router;
//    private SessionInterface $session;
//    private TokenStorageInterface $token;
//    private HttpClientInterface $client;
//    private User $user;
//
//    public function __construct($name = null, array $data = [], $dataName = '')
//    {
//        parent::__construct($name, $data, $dataName);
//
//        $kernel = self::bootKernel();
//        $container = $kernel->getContainer();
//
//        $this->manager = $container->get('doctrine')->getManager();
//        $this->router = $container->get('router');
//        $this->session = $container->get('session');
//        $this->token = $container->get('security.token_storage');
//
//        /** @var User $user */
//        $this->user = $this->manager->getRepository(User::class)->findOneBy(['email' => self::USER_MORPHINOF]);
//    }
//
//    public function setUp(): void
//    {
//        $this->client = HttpClient::create([
//            'base_uri' => 'https://localhost:8000',
//            'verify_peer' => false,
//            'headers' => [
//                'Cookie' => $this->logIn($this->user)
//            ],
//        ]);
//    }
//
//    protected function logIn(User $user): string
//    {
//        $firewallName = 'main';
//
//        /** @var User $user */
//        $token = new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
//        $this->token->setToken($token);
//        $this->session->set('_security_' . $firewallName, serialize($token));
//        $this->session->save();
//
//       return (string) new Cookie($this->session->getName(), $this->session->getId());
//    }
//
//    /**
//     * @throws TransportExceptionInterface
//     */
//    public function testJoin(): void
//    {
//        /** @var Lobby $lobby */
//        $lobby = $this->manager->getRepository(Lobby::class)->findOneBy(['creator' => $this->user->getPlayer()]);
//
//        $url = $this->router->generate('flashkick_lobby_join', [
//            'lobby' => $lobby->getId(),
//        ]);
//        $response = $this->client->request(Request::METHOD_GET, $url);
//
//        $this->assertSame($response->getStatusCode(), Response::HTTP_OK);
//        $this->assertTrue($lobby->getPlayers()->contains($this->user->getPlayer()));
//    }
//}
