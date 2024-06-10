<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\Voter\UsersVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/users', name: 'user')]
class UserController extends AbstractController
{
    #[Route(path: '', name: '.list', methods: ['GET'])]
    #[IsGranted(UsersVoter::LIST)]
    public function list(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('user/list.html.twig', ['users' => $users]);
    }

    #[Route(path: '/create', name: '.create', methods: ['GET', 'POST'])]
    #[IsGranted(UsersVoter::CREATE)]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();

        $form = $this->createForm(UserType::class, $user, ['method' => 'POST']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('user.list');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}/edit', name: '.edit', methods: ['GET', 'PUT'])]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $this->denyAccessUnlessGranted(UsersVoter::EDIT, $user);

        $form = $this->createForm(UserType::class, $user, ['method' => 'PUT']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

            $em->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié.");

            return $this->redirectToRoute('user.list');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }
}
