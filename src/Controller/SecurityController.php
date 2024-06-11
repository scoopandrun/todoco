<?php

namespace App\Controller;

use App\DTO\UserInformationDTO;
use App\Entity\User;
use App\Form\RegistrationForm;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * This route is used when a new user wants to create an account.
     */
    #[Route(path: '/signup', name: 'signup', methods: ['GET', 'POST'])]
    public function signup(
        Request $request,
        EntityManagerInterface $em,
        UserService $userService,
    ): Response {
        // If a user is already connected, they should not be able to create a new account
        if ($this->getUser()) {
            return $this->redirectToRoute('homepage');
        }

        $userInformationDTO = new UserInformationDTO();

        $form = $this->createForm(RegistrationForm::class, $userInformationDTO, ['method' => 'POST']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = new User();

            $userService->fillInUserEntityFromUserInformationDTO($userInformationDTO, $user);

            $userInformationDTO->eraseCredentials();

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', "Votre compte a bien été créé.");
            return $this->redirectToRoute('login');
        }

        return $this->render('security/signup.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('homepage');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @codeCoverageIgnore
     */
    #[Route(path: '/logout', name: 'logout', methods: ['GET'])]
    public function logout(): Response
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
