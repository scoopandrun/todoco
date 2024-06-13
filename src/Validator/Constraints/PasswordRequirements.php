<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
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

    /**
     * @param array<string, mixed> $options 
     * @return Constraint[] 
     */
    #[\Override]
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Type('string'),
            new Assert\Length(
                min: static::MIN_LENGTH,
                max: static::MAX_LENGTH,
                minMessage: "Le mot de passe doit contenir au moins {{ limit }} caractères.",
                maxMessage: "Le mot de passe doit contenir au maximum {{ limit }} caractères.",
            ),
            new Assert\NotCompromisedPassword(message: "Ce mot de passe a déjà été divulgué dans des fuites de données. Veuillez en choisir un autre."),
            new Assert\PasswordStrength(minScore: static::MIN_STRENGTH, message: "Le mot de passe est trop faible. Veuillez utiliser un mot de passe plus fort."),
        ];
    }
}
