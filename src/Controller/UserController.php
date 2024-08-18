<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserController extends AbstractController
{
    #[Route('/users', name: 'user_list')]
    public function listAction(ManagerRegistry $doctrine): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) { 

            $this->addFlash('error', 'Accès refusé.');

            return $this->render('default/index.html.twig');
        }

        $users = $doctrine->getRepository(User::class)->findAll();

        return $this->render('user/list.html.twig', ['users' => $users]);
    }

    #[Route('/users/create', name: 'user_create')]
    public function createAction(Request $request, UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
                        
            $this->addFlash('error', 'Accès refusé.');

            return $this->render('default/index.html.twig');
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($password);

            $roles = $user->getRoles();
            if (empty($roles)) {
                $user->setRoles([User::ROLE_USER]);
            } else {
                if (!in_array(User::ROLE_USER, $roles, true)) {
                    $roles[] = User::ROLE_USER;
                    $user->setRoles($roles);
                }
            }

            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/users/{id}/edit', name: 'user_edit')]
    public function editAction(User $user, Request $request, UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->render('default/index.html.twig');
        }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($password);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
