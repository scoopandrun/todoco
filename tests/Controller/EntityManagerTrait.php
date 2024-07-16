<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;

trait EntityManagerTrait
{
    private ?EntityManagerInterface $entityManager = null;

    private function getEntityManager(): EntityManagerInterface
    {
        if (null === $this->entityManager) {
            /** @var EntityManagerInterface */
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            $this->entityManager = $entityManager;
        }

        return $this->entityManager;
    }
}
