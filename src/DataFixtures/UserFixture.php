<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user1 = (new User())
            ->setUsername('User1')
            ->setEmail('user1@example.com');
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'pass123'));

        $admin = (new User())
            ->setUsername('Admin')
            ->setEmail('admin@example.com')
            ->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin'));

        $manager->persist($user1);
        $manager->persist($admin);
        $manager->flush();
    }
}
