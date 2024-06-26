<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class TaskService
{
    public function __construct(
        private TaskRepository $taskRepository,
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {
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
     * @return Task[]
     */
    public function getTasksByUser(User $user, ?bool $isDone = null): array
    {
        if (null !== $isDone) {
            $tasks = $this->taskRepository->findBy([
                'author' => $user,
                'isDone' => $isDone,
            ]);
        } else {
            $tasks = $user->getTasks()->toArray();
        }

        return $tasks;
    }

    public function createTask(Task $task): void
    {
        /** @var User $author */
        $author = $this->security->getUser();
        $task->setAuthor($author);
        $this->entityManager->persist($task);
        $this->entityManager->flush();
    }

    public function updateTask(Task $task): void
    {
        $this->entityManager->flush();
    }

    public function toggleTask(Task $task): void
    {
        $task->setIsDone(!$task->isDone());
        $this->entityManager->flush();
    }

    public function deleteTask(Task $task): void
    {
        $this->entityManager->remove($task);
        $this->entityManager->flush();
    }
}
