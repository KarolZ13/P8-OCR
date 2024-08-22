<?php

namespace Controller;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;
    private ?UrlGeneratorInterface $urlGenerator = null;
    private ?EntityManagerInterface $entityManager = null;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->urlGenerator = $this->client->getContainer()->get('router.default');
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    protected function restoreExceptionHandler(): void
    {
        while (true) {
            $previousHandler = set_exception_handler(static fn() => null);
            restore_exception_handler();
            if ($previousHandler === null) {
                break;
            }
            restore_exception_handler();
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->restoreExceptionHandler();
    }

    public function testListesDesUtilisateursEnUtilisateur()
    {
        $this->entityManager->getRepository(Task::class)->findAll();

        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_list'));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testListesDesUtilisateursEnAdministrateur()
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'kazedadmin']);

        $this->client->loginUser($user);

        $this->entityManager->getRepository(Task::class)->findAll();

        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_list'));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testCreationUtilisateurEnAdministrateur()
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'kazedadmin']);
        $this->client->loginUser($user);

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_create'));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form[name="user"]');

        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'Utilisateur',
            'user[password][first]' => 'MotDePasse',
            'user[password][second]' => 'MotDePasse',
            'user[email]' => 'utilisateur@test.fr',
            'user[roles]' => ['ROLE_ADMIN'],
        ]);
        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();

        $this->entityManager->clear();
        $userRepository = $this->entityManager->getRepository(User::class);
        $newUser = $userRepository->findOneBy(['username' => 'Utilisateur']);
        $this->assertNotNull($newUser);
        $this->assertSame('Utilisateur', $newUser->getUsername());
        $this->assertSame('utilisateur@test.fr', $newUser->getEmail());
        $this->assertContains('ROLE_ADMIN', $newUser->getRoles());

        $passwordHasher = $this->getContainer()->get(UserPasswordHasherInterface::class);
        $this->assertTrue($passwordHasher->isPasswordValid($newUser, 'MotDePasse'));
    }


    public function testCreationUtilisateurEnUtilisateur()
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'olivie25']);

        $this->client->loginUser($user);

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_create'));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form[name="user"]');

        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'Utilisateur',
            'user[password][first]' => 'MotDePasse',
            'user[password][second]' => 'MotDePasse',
            'user[email]' => 'utilisateur@test.fr',
            'user[roles]' => ['ROLE_ADMIN'],
        ]);
        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();

        $this->entityManager->clear();
        $userRepository = $this->entityManager->getRepository(User::class);
        $newUser = $userRepository->findOneBy(['username' => 'Utilisateur']);
        $this->assertNotNull($newUser);
        $this->assertSame('Utilisateur', $newUser->getUsername());
        $this->assertSame('utilisateur@test.fr', $newUser->getEmail());
        $this->assertContains('ROLE_ADMIN', $newUser->getRoles());

        $passwordHasher = $this->getContainer()->get(UserPasswordHasherInterface::class);
        $this->assertTrue($passwordHasher->isPasswordValid($newUser, 'MotDePasse'));
    }

    public function testCreationUtilisateurSansRoleUser()
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'kazedadmin']);
        $this->client->loginUser($user);

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_create'));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'UtilisateurSansRole',
            'user[password][first]' => 'MotDePasse',
            'user[password][second]' => 'MotDePasse',
            'user[email]' => 'utilisateursansrole@test.fr',
            'user[roles]' => [],
        ]);
        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect();

        $userRepository = $this->entityManager->getRepository(User::class);
        $newUser = $userRepository->findOneBy(['username' => 'UtilisateurSansRole']);
        $this->assertNotNull($newUser);
        $this->assertContains('ROLE_USER', $newUser->getRoles());
    }

    public function testEditionUtilisateurEnRoleUtilisateur()
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'olivie25']);

        $this->client->loginUser($user);

        $newUser = new User();
        $newUser->setUsername('TestUtilisateur1');
        $newUser->setRoles(["ROLE_USER"]);
        $newUser->setPassword('TestUtilisateur');
        $newUser->setEmail('TestUtilisateur1@test.fr');
        $this->entityManager->persist($newUser);
        $this->entityManager->flush();

        $newUserId = $newUser->getId();

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_edit', ['id' => $newUserId]));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form[name="user"]');

        $form = $crawler->selectButton('Modifier')->form([
            'user[username]' => 'Utilisateur11',
            'user[password][first]' => 'MotDePasse',
            'user[password][second]' => 'MotDePasse',
            'user[email]' => 'utilisateur11@test.fr',
            'user[roles]' => ['ROLE_ADMIN'],
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();

        $updatedUser = $this->entityManager->getRepository(User::class)->find($newUser);

        $this->entityManager->remove($updatedUser);
        $this->entityManager->flush();
    }


    public function testEditionUtilisateurEnRoleAdministrateur()
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'kazedadmin']);

        $this->client->loginUser($user);

        $newUser = new User();
        $newUser->setUsername('TestUtilisateur');
        $newUser->setRoles(["ROLE_USER"]);
        $newUser->setPassword('TestUtilisateur');
        $newUser->setEmail('TestUtilisateur@test.fr');
        $this->entityManager->persist($newUser);
        $this->entityManager->flush();

        $newUserId = $newUser->getId();

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_edit', ['id' => $newUserId]));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form[name="user"]');

        $form = $crawler->selectButton('Modifier')->form([
            'user[username]' => 'Utilisateur1',
            'user[password][first]' => 'MotDePasse',
            'user[password][second]' => 'MotDePasse',
            'user[email]' => 'utilisateur1@test.fr',
            'user[roles]' => ['ROLE_ADMIN'],
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();
    }
}