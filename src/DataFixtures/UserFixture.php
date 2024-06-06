<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends Fixture
{
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        $user1 = (new User())
            ->setUsername('User1')
            ->setEmail('user1@example.com');
        $user1->setPassword($this->passwordEncoder->encodePassword($user1, 'pass123'));

        $manager->persist($user1);
        $manager->flush();
    }
}
