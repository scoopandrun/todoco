<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait UsersTrait
{
    private ?UserRepository $userRepository = null;
    private ?User $admin = null;
    private ?User $user1 = null;
    private ?User $user2 = null;
    private ?KernelBrowser $unauthenticatedClient = null;
    private ?KernelBrowser $adminClient = null;
    private ?KernelBrowser $user1Client = null;
    private ?KernelBrowser $user2Client = null;

    private function getUserRepository(): UserRepository
    {
        if (is_null($this->userRepository)) {
            $this->userRepository = static::getContainer()->get(UserRepository::class);
        }

        return $this->userRepository;
    }

    private function getAdminUser(): User
    {
        if (is_null($this->admin)) {
            $this->admin = $this->getUserRepository()->findOneBy(['username' => 'Admin']);
        }

        return $this->admin;
    }

    private function getUser1(): User
    {
        if (is_null($this->user1)) {
            $this->user1 = $this->getUserRepository()->findOneBy(['username' => 'User1']);
        }

        return $this->user1;
    }

    private function getUser2(): User
    {
        if (is_null($this->user2)) {
            $this->user2 = $this->getUserRepository()->findOneBy(['username' => 'User2']);
        }

        return $this->user2;
    }

    private function getUnauthenticatedClient(): KernelBrowser
    {
        if (is_null($this->unauthenticatedClient)) {
            $this->unauthenticatedClient = static::createClient();
        }

        return $this->unauthenticatedClient;
    }

    private function getAdminClient(): KernelBrowser
    {
        if (is_null($this->adminClient)) {
            $this->adminClient = static::createClient();
            $this->adminClient->loginUser($this->getAdminUser());
        }

        return $this->adminClient;
    }

    private function getUser1Client(): KernelBrowser
    {
        if (is_null($this->user1Client)) {
            $this->user1Client = static::createClient();
            $this->user1Client->loginUser($this->getUser1());
        }

        return $this->user1Client;
    }

    private function getUser2Client(): KernelBrowser
    {
        if (is_null($this->user2Client)) {
            $this->user2Client = static::createClient();
            $this->user2Client->loginUser($this->getUser2());
        }

        return $this->user2Client;
    }
}
