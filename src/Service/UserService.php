<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->userRepository->findAll();
    }

    public function createUser(User $user): void
    {
        $this->setPassword($user);

        $user->eraseCredentials();

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function updateUser(User $user): void
    {
        $this->setPassword($user);

        $user->eraseCredentials();

        $this->entityManager->flush();
    }

    public function deleteUser(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    /**
     * Set the password of the user if a new password has been set.
     */
    private function setPassword(User $user): void
    {
        if ($user->getNewPassword()) {
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getNewPassword()));
        }
    }
}
