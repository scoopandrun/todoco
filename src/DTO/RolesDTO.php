<?php

namespace App\DTO;

class RolesDTO
{
    public function __construct(
        public ?array $roles = ['ROLE_USER'],
    ) {
    }
}
