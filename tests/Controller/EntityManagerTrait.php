<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;

trait EntityManagerTrait
{
    private ?EntityManagerInterface $entityManager = null;

    private function getEntityManager(): EntityManagerInterface
    {
        if (null === $this->entityManager) {
            $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        }

        return $this->entityManager;
    }
}
