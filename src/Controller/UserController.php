<?php

namespace App\Controller;

use App\DTO\UserInformationDTO;
use App\Entity\User;
use App\Form\RegistrationForm;
use App\Form\UserAccountForm;
use App\Repository\UserRepository;
use App\Security\Voter\UsersVoter;
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
    #[IsGranted(UsersVoter::LIST)]
    public function list(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('user/list.html.twig', ['users' => $users]);
    }

    /**
     * This route is used when an admin wants to create a new user.
     */
    #[Route(path: '/create', name: '.create', methods: ['GET', 'POST'])]
    #[IsGranted(UsersVoter::CREATE)]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        UserService $userService,
    ): Response {
        $userInformationDTO = new UserInformationDTO();

        $form = $this->createForm(RegistrationForm::class, $userInformationDTO, ['method' => 'POST']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = new User();

            $userService->fillInUserEntityFromUserInformationDTO($userInformationDTO, $user);

            $userInformationDTO->eraseCredentials();

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
        UserService $userService,
    ): Response {
        $this->denyAccessUnlessGranted(UsersVoter::EDIT, $user);

        $userInformationDTO = $userService->makeUserInformationDTOFromEntity($user);

        $form = $this->createForm(UserAccountForm::class, $userInformationDTO, ['method' => 'PUT']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userService->fillInUserEntityFromUserInformationDTO($userInformationDTO, $user);

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
        $user = $this->getUser();

        $userInformationDTO = $userService->makeUserInformationDTOFromEntity($user);

        $form = $this->createForm(UserAccountForm::class, $userInformationDTO, ['method' => 'PUT']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userService->fillInUserEntityFromUserInformationDTO($userInformationDTO, $user);

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
