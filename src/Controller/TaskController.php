<?php
namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'task_list')]
    public function listAction(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $tasks = $doctrine->getRepository(Task::class)->findAll();
        
        foreach ($tasks as $task){
           if ($task->getIdUser() === null) {
            $anonymeUser = $doctrine->getRepository(User::class)->findOneBy(['username' => 'anonyme']);
            $task->setIdUser($anonymeUser);
            $entityManager->persist($task);
           } 
        }

        $entityManager->flush();

        return $this->render('task/list.html.twig', ['tasks' => $tasks, ]);
    }

    #[Route('/tasks/create', name: 'task_create')]
    public function createAction(Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $task = new Task();
        $task->setIdUser($user);
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();

            $em->persist($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/tasks/{id}/edit', name: 'task_edit')]
    public function editAction(Task $task, Request $request, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getManager()->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/tasks/{id}/toggle', name: 'task_toggle')]
    public function toggleTaskAction(Task $task, ManagerRegistry $doctrine): Response
    {
        $task->toggle(!$task->isDone());
        $doctrine->getManager()->flush();

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    #[Route('/tasks/{id}/delete', name: 'task_delete')]
    public function deleteTaskAction(Task $task, ManagerRegistry $doctrine): Response
    {
        $currentUser = $this->getUser();
        $anonymeUsername = 'anonyme';

        // Vérifier si la tâche est liée à l'utilisateur anonyme
        if ($task->getIdUser()->getUsername() === $anonymeUsername && $this->isGranted('ROLE_ADMIN')) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été supprimée.');
        } elseif ($task->getIdUser() === $currentUser) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été supprimée.');
        } else {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cette tâche.');
        }

        return $this->redirectToRoute('task_list');
    }
}
