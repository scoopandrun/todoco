<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, User>
 */
class UsersVoter extends Voter
{
    public const LIST = 'USER_LIST';
    public const CREATE = 'USER_CREATE';
    public const EDIT = 'USER_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($subject === null) {
            return in_array($attribute, [self::LIST, self::CREATE]);
        }

        if ($subject instanceof User) {
            return in_array($attribute, [self::EDIT]);
        }

        return false;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var ?User */
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        if (!is_null($subject) && !$subject instanceof User) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::LIST:
                return $user->isAdmin();

            case self::CREATE:
                return $user->isAdmin();

            case self::EDIT:
                return $user->isAdmin() || $user === $subject;
        }

        // @codeCoverageIgnoreStart
        throw new \LogicException('This code should not be reached!');
        // @codeCoverageIgnoreEnd
    }
}
