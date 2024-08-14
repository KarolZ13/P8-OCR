<?php

namespace Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecurityControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;
    private ?UrlGeneratorInterface $urlGenerator = null;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->urlGenerator = $this->client->getContainer()->get('router.default');
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
    public function testPageDeConnexion()
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('login'));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testConnexionUtilisateurAvecBonIdentifiants()
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('login'));

        $this->client->submitForm('Se connecter', [
            '_username' => 'NomUtilisateur',
            '_password' => 'Motdepasse',
        ]);

        $this->client->followRedirect();
        $this->assertRouteSame('login');
    }

    public function testConnexionUtilisateurAvecMauvaisIdentifiants()
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('login'));

        $this->client->submitForm('Se connecter', [
            '_username' => null,
            '_password' => null,
        ]);

        $this->client->followRedirect();
        $this->assertRouteSame('login');
    }
}