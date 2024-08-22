<?php

namespace Controller;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TaskControllerTest extends WebTestCase
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

    public function testApercuListeDesTaches()
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'kazedadmin']);
        $this->client->loginUser($user);

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_list'));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $tasks = $this->entityManager->getRepository(Task::class)->findBy(['isDone' => false]);

        foreach ($tasks as $task) {
            $this->assertGreaterThan(0, $crawler->filter('.task-title:contains("' . $task->getTitle() . '")')->count());
            if ($task->getIdUser() === null) {
                $anonymeUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'anonyme']);
                $this->assertEquals($anonymeUser, $task->getIdUser());
            }
        }
    }

    public function testEditionDeTache()
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'kazedadmin']);
        $this->client->loginUser($user);

        $task = new Task();
        $task->setTitle('Test Task');
        $task->setContent('This is a test task.');
        $task->setIdUser($user);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['title' => 'Test Task']);
        $taskID = $task->getId();

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_edit', ['id' => $taskID]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorExists('form[name="task"]', 'Le formulaire n\'est pas présent sur la page.');

        $form = $crawler->selectButton('Modifier')->form([
            'task[title]' => 'Nouveau titre',
            'task[content]' => 'Nouveau contenu',
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);


        $updatedTask = $this->entityManager->getRepository(Task::class)->find($taskID);
        $this->assertSame('Nouveau titre', $updatedTask->getTitle());
        $this->assertSame('Nouveau contenu', $updatedTask->getContent());

        $this->entityManager->remove($updatedTask);
        $this->entityManager->flush();
    }

    public function testModificationDeTacheNonTermineATermnine()
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'kazedadmin']);
        $this->client->loginUser($user);

        $task = new Task();
        $task->setTitle('Tache Test');
        $task->setContent('C\'est une tâche de test' );
        $task->setIdUser($user);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $taskID = $task->getId();

        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_toggle', ['id' => $taskID]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();

        $updatedTask = $this->entityManager->getRepository(Task::class)->find($taskID);
        $this->assertTrue($updatedTask->isDone());

        $this->entityManager->remove($updatedTask);
        $this->entityManager->flush();
    }

    public function testCreationTache()
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'kazedadmin']);
        $this->client->loginUser($user);

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_create'));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form[name="task"]');

        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'Nouvelle tâche1',
            'task[content]' => 'Nouveau contenu',
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();

        $taskRepository = $this->entityManager->getRepository(Task::class);
        $task = $taskRepository->findOneBy(['title' => 'Nouvelle tâche1']);
        $this->assertNotNull($task);
        $this->assertSame('Nouvelle tâche1', $task->getTitle());
        $this->assertSame('Nouveau contenu', $task->getContent());
        $this->assertSame($user->getId(), $task->getIdUser()->getId());
    }

    public function testSuppressionTache()
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'kazedadmin']);
        $this->client->loginUser($user);

        $task = new Task();
        $task->setTitle('Tâche à supprimer');
        $task->setContent('Contenu de la tâche à supprimer');
        $task->setIdUser($user);

        $entityManager = $this->entityManager;
        $entityManager->persist($task);
        $entityManager->flush();

        $taskId = $task->getId();

        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_delete', ['id' => $taskId]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();

        $taskRepository = $this->entityManager->getRepository(Task::class);
        $deletedTask = $taskRepository->find($taskId);
        $this->assertNull($deletedTask);
    }

    public function testApercuListeDesTachesTermine()
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'kazedadmin']);
        $this->client->loginUser($user);

        $completedTask = new Task();
        $completedTask->setTitle('Tâche terminée');
        $completedTask->setContent('Contenu de la tâche terminée');
        $completedTask->setIsDone(true);
        $completedTask->setIdUser($user);

        $this->entityManager->persist($completedTask);
        $this->entityManager->flush();

        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_list_done'));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}