<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

class UserInformationDTO
{
    public function __construct(
        #[Assert\NotBlank(groups: ['registration', 'account_update'])]
        #[Assert\Type('string')]
        #[Assert\Length(
            min: 3,
            max: 25,
            minMessage: "Votre nom d'utilisateur doit contenir au moins {{ limit }} caractères.",
            maxMessage: "Votre nom d'utilisateur doit contenir au maximum {{ limit }} caractères."
        )]
        public ?string $username = null,

        #[Assert\NotBlank(
            groups: ['registration', 'account_update'],
            message: 'Vous devez saisir une adresse email.',
        )]
        #[Assert\Email(message: "Le format de l'adresse n'est pas correcte.")]
        public ?string $email = null,

        #[Assert\When(
            expression: 'this.newPassword.password',
            groups: ['account_update'],
            constraints: [
                new Assert\Sequentially(
                    [
                        new Assert\Type('string'),
                        new Assert\NotBlank(message: 'Vous devez entrer votre mot de passe actuel pour définir un nouveau mot de passe.'),
                        new SecurityAssert\UserPassword(message: 'Votre mot de passe actuel est incorrect.'),
                    ],
                    groups: ['account_update'],
                ),
            ],
        )]
        #[\SensitiveParameter]
        public ?string $currentPassword = null,

        #[Assert\Valid()]
        public NewPasswordDTO $newPassword = new NewPasswordDTO(),

        #[Assert\Valid()]
        public RolesDTO $roles = new RolesDTO(),
    ) {
    }

    public function eraseCredentials(): void
    {
        $this->currentPassword = null;
        $this->newPassword->password = null;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword->password;
    }

    public function getRoles(): array
    {
        return $this->roles->roles;
    }
}
