<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Difficulty|null find($id, $lockMode = null, $lockVersion = null)
 * @method Difficulty|null findOneBy(array $criteria, array $orderBy = null)
 * @method Difficulty[]    findAll()
 * @method Difficulty[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DifficultyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }
}
