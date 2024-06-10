<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetId(): void
    {
        // Given
        $user = new User();

        // Then
        $this->assertNull($user->getId());
    }

    public function testGetUsername(): void
    {
        // Given
        $user = new User();

        // Then
        $this->assertNull($user->getUsername());
    }

    public function testSetUsername(): void
    {
        // Given
        $user = new User();

        // When
        $username = 'test_username';
        $user->setUsername($username);

        // Then
        $this->assertEquals($username, $user->getUsername());
    }

    public function testGetPassword(): void
    {
        // Given
        $user = new User();

        // Then
        $this->assertNull($user->getPassword());
    }

    public function testSetPassword(): void
    {
        // Given
        $user = new User();
        $password = 'test_password';

        // When
        $user->setPassword($password);

        // Then
        $this->assertEquals($password, $user->getPassword());
    }

    public function testGetEmail(): void
    {
        // Given
        $user = new User();

        // Then
        $this->assertNull($user->getEmail());
    }

    public function testSetEmail(): void
    {
        // Given
        $user = new User();
        $email = 'test@example.com';

        // When
        $user->setEmail($email);

        // Then
        $this->assertEquals($email, $user->getEmail());
    }

    public function testGetRoles(): void
    {
        // Given
        $user = new User();
        $expectedRoles = ['ROLE_USER'];

        // Then
        $this->assertEquals($expectedRoles, $user->getRoles());
    }

    public function testSetRoles(): void
    {
        // Given
        $user = new User();
        $roles = ['ROLE_ADMIN'];
        $expectedRoles = ['ROLE_ADMIN', 'ROLE_USER'];

        // When
        $user->setRoles($roles);

        // Then
        $this->assertEquals($expectedRoles, $user->getRoles());
    }

    public function testEraseCredentials(): void
    {
        // Given
        $user = new User();
        $reflection = new \ReflectionClass($user);

        // When
        $user->eraseCredentials();

        // Then
        $this->assertNull($reflection->getProperty('plainPassword')->getValue($user));
    }
}
