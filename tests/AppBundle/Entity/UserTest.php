<?php

use AppBundle\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetId()
    {
        // Given
        $user = new User();

        // Then
        $this->assertNull($user->getId());
    }

    public function testGetUsername()
    {
        // Given
        $user = new User();

        // Then
        $this->assertNull($user->getUsername());
    }

    public function testSetUsername()
    {
        // Given
        $user = new User();

        // When
        $username = 'test_username';
        $user->setUsername($username);

        // Then
        $this->assertEquals($username, $user->getUsername());
    }

    public function testGetPassword()
    {
        // Given
        $user = new User();

        // Then
        $this->assertNull($user->getPassword());
    }

    public function testSetPassword()
    {
        // Given
        $user = new User();
        $password = 'test_password';

        // When
        $user->setPassword($password);

        // Then
        $this->assertEquals($password, $user->getPassword());
    }

    public function testGetEmail()
    {
        // Given
        $user = new User();

        // Then
        $this->assertNull($user->getEmail());
    }

    public function testSetEmail()
    {
        // Given
        $user = new User();
        $email = 'test@example.com';

        // When
        $user->setEmail($email);

        // Then
        $this->assertEquals($email, $user->getEmail());
    }

    public function testGetRoles()
    {
        // Given
        $user = new User();
        $expectedRoles = ['ROLE_USER'];

        // Then
        $this->assertEquals($expectedRoles, $user->getRoles());
    }

    public function testEraseCredentials()
    {
        // Given
        $user = new User();

        // Then
        $this->assertNull($user->eraseCredentials());
    }
}
