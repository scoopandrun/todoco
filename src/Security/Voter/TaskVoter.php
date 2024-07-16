<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Task|null>
 */
final class TaskVoter extends Voter
{
    public const string LIST = 'TASK_LIST';
    public const string CREATE = 'TASK_CREATE';
    public const string EDIT = 'TASK_EDIT';
    public const string TOGGLE = 'TASK_TOGGLE';
    public const string DELETE = 'TASK_DELETE';

    #[\Override]
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
                return $this->canList($user);

            case self::CREATE:
                return $this->canCreate($user);

            case self::EDIT:
                /** @var Task $subject */
                return $this->canEdit($user, $subject);

            case self::TOGGLE:
                /** @var Task $subject */
                return $this->canToggle($user, $subject);

            case self::DELETE:
                /** @var Task $subject */
                return $this->canDelete($user, $subject);
        }

        throw new \LogicException('This code should not be reached!'); // @codeCoverageIgnore
    }

    private function canList(User $user): bool
    {
        return true;
    }

    private function canCreate(User $user): bool
    {
        return true;
    }

    private function canEdit(User $user, Task $task): bool
    {
        return true;
    }

    private function canToggle(User $user, Task $task): bool
    {
        return true;
    }

    private function canDelete(User $user, Task $task): bool
    {
        return $user->isAdmin() || $user === $task->getAuthor();
    }
}
