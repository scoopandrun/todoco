<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    /**
     * Set the password of the user if a new password has been set.
     */
    public function setPassword(User $user): void
    {
        if ($user->getNewPassword()) {
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getNewPassword()));
        }
    }
}
