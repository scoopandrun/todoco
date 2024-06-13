<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/users', name: 'user')]
class UserController extends AbstractController
{
    #[Route(path: '', name: '.list', methods: ['GET'])]
    #[IsGranted(UserVoter::LIST)]
    public function list(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('user/list.html.twig', ['users' => $users]);
    }

    /**
     * This route is used when an admin wants to create a new user.
     */
    #[Route(path: '/create', name: '.create', methods: ['GET', 'POST'])]
    #[IsGranted(UserVoter::CREATE)]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        UserService $userService,
    ): Response {
        $user = new User();

        $form = $this->createForm(UserType::class, $user, [
            'method' => 'POST',
            'validation_groups' => ['Default', 'registration'],
            'new_password_label' => 'Mot de passe',
            'new_password_required' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userService->setPassword($user);

            $user->eraseCredentials();

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");
            return $this->redirectToRoute('user.list');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}', name: '.edit', methods: ['GET', 'PUT'], requirements: ['id' => '\d+'])]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

        $form = $this->createForm(UserType::class, $user, [
            'method' => 'PUT',
            'validation_groups' => ['Default', 'account_update'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié.");

            return $this->redirectToRoute('user.list');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route(path: '/me', name: '.me', methods: ['GET', 'PUT'])]
    public function me(
        Request $request,
        EntityManagerInterface $em,
        UserService $userService,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user, [
            'method' => 'PUT',
            'validation_groups' => ['Default', 'account_update'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userService->setPassword($user);

            $em->flush();

            $this->addFlash('success', "Votre compte a bien été modifié.");

            return $this->redirectToRoute('user.me');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }
}
