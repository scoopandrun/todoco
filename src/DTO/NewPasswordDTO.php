<?php

namespace App\DTO;

use App\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

class NewPasswordDTO
{
    public function __construct(
        #[Assert\NotBlank(groups: ['registration'])]
        #[AppAssert\PasswordRequirements([
            'groups' => ['registration', 'account_update']
        ])]
        #[\SensitiveParameter]
        public ?string $password = null,
    ) {
    }
}
