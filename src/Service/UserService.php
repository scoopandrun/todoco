<?php

namespace App\Service;

use App\DTO\RolesDTO;
use App\DTO\UserInformationDTO;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function makeUserInformationDTOFromEntity(User $user): UserInformationDTO
    {
        return new UserInformationDTO(
            $user->getUsername(),
            $user->getEmail(),
            roles: new RolesDTO($user->getRoles()),
        );
    }

    public function fillInUserEntityFromUserInformationDTO(UserInformationDTO $userInformation, User $user): void
    {
        $user
            ->setUsername($userInformation->username)
            ->setEmail($userInformation->email);

        if ($userInformation->getRoles()) {
            $user->setRoles($userInformation->getRoles());
        }

        if ($userInformation->getNewPassword()) {
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $userInformation->getNewPassword()
                )
            );
        }
    }
}
