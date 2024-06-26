<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use Doctrine\Common\Collections\Collection;

class TaskService
{
    public function __construct(private TaskRepository $taskRepository)
    {
    }

    /**
     * Get tasks.
     * 
     * @param null|bool $isDone Filter for done tasks.
     * 
     * @return Task[]
     */
    public function getTasks(?bool $isDone = null): array
    {
        if (null !== $isDone) {
            $tasks = $this->taskRepository->findBy(['isDone' => $isDone]);
        } else {
            $tasks = $this->taskRepository->findAll();
        }

        return $tasks;
    }

    /**
     * Get tasks by user.
     * 
     * @param User $user 
     * 
     * @return Collection<int, Task>
     */
    public function getTasksByUser(User $user): Collection
    {
        return $user->getTasks();
    }
}
