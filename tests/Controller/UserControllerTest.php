<?php

namespace Tests\App\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = [
            'username' => 'User1',
            'password' => 'pass123',
        ];
    }

    public function testUsersPageIsUp(): void
    {
        // Given
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->user['username'],
            'PHP_AUTH_PW' => $this->user['password'],
        ]);

        // When
        $client->request('GET', '/users');

        // Then
        $this->assertResponseIsSuccessful();
    }

    /**
     * @return (string|string[]|null)[] Info about the created user.
     */
    public function testUserCanBeCreated(): array
    {
        // Given
        $client = static::createClient();
        $newUsername = 'User2';
        $newUserPassword = 'pass123';
        $newUserEmail = 'user2@example.com';

        // When
        $crawler = $client->request('GET', '/users/create');

        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = $newUsername;
        $form['user[password][first]'] = $newUserPassword;
        $form['user[password][second]'] = $newUserPassword;
        $form['user[email]'] = $newUserEmail;

        $client->submit($form);

        // Then
        $this->assertResponseRedirects('/users');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'L\'utilisateur a bien été ajouté.');
        $this->assertSelectorTextContains('table', $newUsername);
        $this->assertSelectorTextContains('table', $newUserEmail);

        // Get ID of the created user
        $link = $crawler->filter('table')->filter("td:contains('{$newUsername}')")->siblings()->last()->children()->first()->attr('href');
        $userId = preg_replace('/[^0-9]/', '', $link);

        // Return user info
        return [
            'username' => $newUsername,
            'password' => $newUserPassword,
            'email' => $newUserEmail,
            'id' => $userId,
        ];
    }

    /**
     * @depends testUserCanBeCreated
     * 
     * @param array $userInfo User info.
     */
    public function testUserCanBeEdited($userInfo): void
    {
        // Given
        $client = static::createClient();
        $editedUsername = $userInfo['username'] . 'edited';
        $editedEmail = str_replace('@', 'edited@', $userInfo['email']);

        // When
        $crawler = $client->request('GET', "/users/{$userInfo['id']}/edit");

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[username]'] = $editedUsername;
        $form['user[password][first]'] = $userInfo['password'];
        $form['user[password][second]'] = $userInfo['password'];
        $form['user[email]'] = $editedEmail;

        $client->submit($form);

        // Then
        $this->assertResponseRedirects('/users');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'L\'utilisateur a bien été modifié.');
        $this->assertSelectorTextContains('table', $editedUsername);
        $this->assertSelectorTextContains('table', $editedEmail);
    }
}
