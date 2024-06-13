<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait UsersTrait
{
    private ?EntityManagerInterface $entityManager = null;
    private ?UserRepository $userRepository = null;
    private ?KernelBrowser $adminClient = null;

    private function getEntityManager(): EntityManagerInterface
    {
        if (is_null($this->entityManager)) {
            $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        }

        return $this->entityManager;
    }

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

    private function getUnauthenticatedClient(bool $followRedirects = true): KernelBrowser
    {
        $client = static::createClient();

        if ($followRedirects) {
            $client->followRedirects();
        }

        return $client;
    }

    private function getAuthenticatedClient(string $username, bool $followRedirects = true): KernelBrowser
    {
        $client = static::createClient();
        $user = $this->getUser($username);
        $client->loginUser($user);

        if ($followRedirects) {
            $client->followRedirects();
        }

        return $client;
    }

    private function getAdminClient(bool $followRedirects = true): KernelBrowser
    {
        if (is_null($this->adminClient)) {
            $this->adminClient = static::createClient();
            $this->adminClient->loginUser($this->getUser('Admin'));

            if ($followRedirects) {
                $this->adminClient->followRedirects();
            }
        }

        return $this->adminClient;
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
        $password = bin2hex(random_bytes(8));
        $email = 'user' . random_int(100, 999) . '@example.com';

        return $this->createUser($username, $password, $email, $persist);
    }
}
