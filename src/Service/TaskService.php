<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\TaskRepository;
use Doctrine\Common\Collections\Collection;

class TaskService
{
    public function __construct(private TaskRepository $taskRepository)
    {
    }

    public function getTasks(?bool $isDone = null): array
    {
        if (!is_null($isDone)) {
            $tasks = $this->taskRepository->findBy(['isDone' => $isDone]);
        } else {
            $tasks = $this->taskRepository->findAll();
        }

        return $tasks;
    }
}
