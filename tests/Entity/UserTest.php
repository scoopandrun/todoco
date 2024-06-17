<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Task;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
#[UsesClass(Task::class)]
class UserTest extends TestCase
{
    public function testGetId(): void
    {
        // Given
        $user = new User();

        // Then
        $this->assertNull($user->getId());
    }

    public function testGetUserIdentifier(): void
    {
        // Given
        $user = new User();
        $username = 'test_username';

        // When
        $user->setUsername($username);

        // Then
        $this->assertSame($username, $user->getUserIdentifier());
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

    public function testGetCurrentPassword(): void
    {
        // Given
        $user = new User();

        // Then
        $this->assertNull($user->getCurrentPassword());
    }

    public function testSetCurrentPassword(): void
    {
        // Given
        $user = new User();
        $password = 'test_password';

        // When
        $user->setCurrentPassword($password);

        // Then
        $this->assertEquals($password, $user->getCurrentPassword());
    }

    public function testGetPlainPassword(): void
    {
        // Given
        $user = new User();

        // Then
        $this->assertNull($user->getNewPassword());
    }

    public function testSetNewPassword(): void
    {
        // Given
        $user = new User();
        $password = 'test_password';

        // When
        $user->setNewPassword($password);

        // Then
        $this->assertEquals($password, $user->getNewPassword());
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

    public function testIsAdmin(): void
    {
        // Given
        $user = new User();

        // When
        $user->setRoles(['ROLE_ADMIN']);

        // Then
        $this->assertTrue($user->isAdmin());
    }

    public function testIsNotAdmin(): void
    {
        // Given
        $user = new User();

        // Then
        $this->assertFalse($user->isAdmin());
    }

    public function testEraseCredentials(): void
    {
        // Given
        $user = new User();
        $reflection = new \ReflectionClass($user);

        // When
        $user->eraseCredentials();

        // Then
        $this->assertNull($reflection->getProperty('newPassword')->getValue($user));
        $this->assertNull($reflection->getProperty('currentPassword')->getValue($user));
    }

    public function testGetTests(): void
    {
        // Given
        $user = new User();

        // Then
        $this->assertEmpty($user->getTasks());
    }

    public function testAddTask(): void
    {
        // Given
        $user = new User();
        $task = new Task();

        // When
        $user->addTask($task);

        // Then
        $this->assertContains($task, $user->getTasks());
    }
}
