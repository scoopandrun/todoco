<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Task>
 */
class TaskVoter extends Voter
{
    public const LIST = 'TASK_LIST';
    public const CREATE = 'TASK_CREATE';
    public const EDIT = 'TASK_EDIT';
    public const TOGGLE = 'TASK_TOGGLE';
    public const DELETE = 'TASK_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($subject === null) {
            return in_array($attribute, [self::LIST, self::CREATE]);
        }

        if ($subject instanceof Task) {
            return in_array($attribute, [self::EDIT, self::TOGGLE, self::DELETE]);
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

        if (!is_null($subject) && !$subject instanceof Task) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::LIST:
                return true;

            case self::CREATE:
                return true;

            case self::EDIT:
                return true;

            case self::TOGGLE:
                return true;

            case self::DELETE:
                return (is_null($subject->getAuthor()) && $user->isAdmin()) || $user === $subject->getAuthor();
        }

        // @codeCoverageIgnoreStart
        throw new \LogicException('This code should not be reached!');
        // @codeCoverageIgnoreEnd
    }
}
