<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints\PasswordStrength;

#[\Attribute]
class PasswordRequirements extends Compound
{
    /**
     * Minimum password length.
     */
    public const int MIN_LENGTH = 10;

    /**
     * Maximum password length.
     * 
     * Symfony limit for security reasons = 4096.
     */
    public const int MAX_LENGTH = 4096;

    /**
     * Minimum password strength score.
     * 
     * This is a constant of \Symfony\Component\Validator\Constraints\PasswordStrength.
     * 
     * Current value = Medium (2).
     */
    public const int MIN_STRENGTH = PasswordStrength::STRENGTH_MEDIUM;

    #[\Override]
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Type('string'),
            new Assert\Length(
                min: static::MIN_LENGTH,
                max: static::MAX_LENGTH,
            ),
            new Assert\NotCompromisedPassword(),
            new Assert\PasswordStrength(minScore: static::MIN_STRENGTH),
        ];
    }
}
