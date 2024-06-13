<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\User;

trait TasksTrait
{
    use EntityManagerTrait;

    private function createTask(
        string $title,
        string $content,
        ?User $author = null,
        bool $done = false,
        bool $persist = true,
    ): Task {
        $task = (new Task())
            ->setTitle($title)
            ->setContent($content)
            ->setAuthor($author)
            ->setIsDone($done);

        if ($persist) {
            $this->getEntityManager()->persist($task);
            $this->getEntityManager()->flush();
        }

        return $task;
    }

    private function createRandomTask(?User $author = null, bool $isDone = false, bool $persist = true): Task
    {
        $title = 'TestTask' . random_int(100, 999);
        $content = 'Test ' . bin2hex(random_bytes(16));

        return $this->createTask($title, $content, $author, $isDone, $persist);
    }
}
