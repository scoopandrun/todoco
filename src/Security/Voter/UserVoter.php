<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, User|null>
 */
final class UserVoter extends Voter
{
    public const string LIST = 'USER_LIST';
    public const string CREATE = 'USER_CREATE';
    public const string EDIT = 'USER_EDIT';
    public const string DELETE = 'USER_DELETE';

    #[\Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($subject === null) {
            return in_array($attribute, [self::LIST, self::CREATE]);
        }

        if ($subject instanceof User) {
            return in_array($attribute, [self::EDIT, self::DELETE]);
        }

        return false;
    }

    #[\Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var ?User */
        $user = $token->getUser();

        // If the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::LIST:
                return $user->isAdmin();

            case self::CREATE:
                // An admin can create an account
                return $user->isAdmin();

            case self::EDIT:
                return $user->isAdmin() || $user === $subject;

            case self::DELETE:
                return $user->isAdmin() || $user === $subject;
        }

        throw new \LogicException('This code should not be reached!'); // @codeCoverageIgnore
    }
}
