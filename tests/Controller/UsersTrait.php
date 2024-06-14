<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait UsersTrait
{
    use EntityManagerTrait;

    private ?UserRepository $userRepository = null;

    private function getUserRepository(): UserRepository
    {
        if (is_null($this->userRepository)) {
            $this->userRepository = static::getContainer()->get(UserRepository::class);
        }

        return $this->userRepository;
    }

    private function getUser(string $username): User
    {
        return $this->getUserRepository()->findOneBy(['username' => $username]);
    }

    private function createUser(
        string $username,
        string $password,
        string $email,
        bool $persist = true,
    ): User {
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = (new User())
            ->setUsername($username)
            ->setCurrentPassword($password)
            ->setNewPassword($password)
            ->setEmail($email);

        $user->setPassword($passwordHasher->hashPassword($user, $user->getNewPassword()));

        if ($persist) {
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();
        }

        return $user;
    }

    private function createRandomUser(bool $persist = true): User
    {
        $username = 'User' . random_int(100, 999);
        $password = bin2hex(random_bytes(16));
        $email = 'user' . random_int(100, 999) . '@example.com';

        return $this->createUser($username, $password, $email, $persist);
    }
}
