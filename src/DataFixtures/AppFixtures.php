<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @codeCoverageIgnore
 */
class AppFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->generateUsers() as $user) {
            $manager->persist($user);
            $this->setReference((string) $user->getUsername(), $user);
        }

        foreach ($this->generateTasks() as $task) {
            $manager->persist($task);
        }

        $manager->flush();
    }

    /**
     * @return \Generator<int, User>
     */
    private function generateUsers(): \Generator
    {
        $admin = (new User())
            ->setUsername('Admin')
            ->setEmail('admin@example.com')
            ->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin'));
        yield $admin;

        $numberOfUsers = 2; // min = 1
        foreach (range(1, $numberOfUsers) as $i) {
            $user = (new User())
                ->setUsername('User' . $i)
                ->setEmail("user{$i}@example.com");
            $user->setPassword($this->passwordHasher->hashPassword($user, 'pass'));
            yield $user;
        }
    }

    /**
     * @return \Generator<int, Task>
     */
    private function generateTasks(): \Generator
    {
        $anonymousTask = (new Task())
            ->setTitle('Anonymous task')
            ->setContent('This task is created by anonymous user.');
        yield $anonymousTask;

        // Generate 20 tasks with random author and 'isDone' status
        for ($i = 0; $i < 20; $i++) {
            $task = (new Task())
                ->setTitle('Task ' . $i)
                ->setContent('This is task number ' . $i)
                ->setIsDone((bool) rand(0, 1));

            if ($userId = rand(0, 2)) {
                /** @var ?User */
                $author = $this->getReference('User' . $userId);
                $task->setAuthor($author);
            }

            yield $task;
        }
    }
}
