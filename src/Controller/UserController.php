<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Security\Voter\UserVoter;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[Route('/users', name: 'user')]
class UserController extends AbstractController
{
    /**
     * The current request.
     */
    private Request $request;

    public function __construct(
        RequestStack $requestStack,
        private readonly UserService $userService,
    ) {
        $currentRequest = $requestStack->getCurrentRequest();

        if (null === $currentRequest) {
            throw new \LogicException('The request cannot be null.');
        }

        $this->request = $currentRequest;
    }

    #[Route(path: '', name: '.list', methods: ['GET'])]
    #[IsGranted(UserVoter::LIST)]
    public function list(): Response
    {
        $users = $this->userService->getUsers();

        return $this->render('user/list.html.twig', ['users' => $users]);
    }

    /**
     * This route is used when an admin wants to create a new user.
     */
    #[Route(path: '/create', name: '.create', methods: ['GET', 'POST'])]
    #[IsGranted(UserVoter::CREATE)]
    public function create(): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user, [
            'method' => 'POST',
            'validation_groups' => ['Default', 'registration'],
            'new_password_label' => 'Mot de passe',
            'new_password_required' => true,
        ]);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->createUser($user);

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");
            return $this->redirectToRoute('user.list');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}', name: '.edit', methods: ['GET', 'PUT'], requirements: ['id' => '\d+'])]
    public function edit(User $user): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

        $form = $this->createForm(UserType::class, $user, [
            'method' => 'PUT',
            'validation_groups' => ['Default', 'account_update'],
        ]);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->updateUser($user);

            $this->addFlash('success', "L'utilisateur a bien été modifié.");

            return $this->redirectToRoute('user.list');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route(path: '/{id}', name: '.delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(User $user, TokenStorageInterface $tokenStorage): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::DELETE, $user);

        $userId = $user->getId();

        $this->userService->deleteUser($user);

        $flashType = 'success';
        $flashMessage = "L'utilisateur a bien été supprimé.";

        // If the user is deleting their own account, log them out
        if ($user === $this->getUser()) {
            $this->request->getSession()->invalidate();
            $tokenStorage->setToken(null);
            $this->addFlash($flashType, "Votre compte a bien été supprimé.");
            return $this->redirectToRoute('security.login');
        }

        // If the request is an AJAX request, return a stream response
        if ($this->request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
            $this->request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->render(
                'user/_delete.stream.html.twig',
                [
                    'id' => $userId,
                    'message' => $flashMessage,
                    'type' => $flashType,
                ]
            );
        }

        // If the user is an admin, redirect them to the list of users
        $this->addFlash($flashType, $flashMessage);

        return $this->redirectToRoute('user.list');
    }

    #[Route(path: '/me', name: '.me', methods: ['GET', 'PUT'])]
    public function me(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user, [
            'method' => 'PUT',
            'validation_groups' => ['Default', 'account_update'],
        ]);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->updateUser($user);

            $this->addFlash('success', "Votre compte a bien été modifié.");

            return $this->redirectToRoute('user.me');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }
}
