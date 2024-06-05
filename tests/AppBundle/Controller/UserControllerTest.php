<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UserControllerTest extends WebTestCase
{
    private $user;

    public function setUp()
    {
        parent::setUp();
        $this->user = [
            'username' => 'User1',
            'password' => 'pass123',
        ];
    }

    public function testUsersPageIsUp()
    {
        // Given
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->user['username'],
            'PHP_AUTH_PW' => $this->user['password'],
        ]);

        // When
        $client->request('GET', '/users');

        // Then
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testUserCanBeCreated()
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
        $response = $client->getResponse();

        // Then
        $this->assertInstanceOf(RedirectResponse::class, $response);
        /** @var RedirectResponse $response */
        $this->assertEquals('/users', $response->getTargetUrl());
        $crawler = $client->followRedirect();
        $this->assertContains("L'utilisateur a bien été ajouté.", $crawler->filter('div.alert-success')->text());
        $this->assertContains($newUsername, $crawler->filter('table')->text());
        $this->assertContains($newUserEmail, $crawler->filter('table')->text());

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
    public function testUserCanBeEdited($userInfo)
    {
        // Given
        $client = static::createClient();
        $editedUsername = $userInfo['username'] . 'edited';
        $editedEmail = str_replace('@', 'edited@', $userInfo['email']);

        // When
        $crawler = $client->request('GET', "/users/{$userInfo['id']}/edit");

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[username]'] = $editedUsername;
        $form['user[email]'] = $editedEmail;

        $client->submit($form);
        $response = $client->getResponse();

        // Then
        $this->assertInstanceOf(RedirectResponse::class, $response);
        /** @var RedirectResponse $response */
        $this->assertEquals('/users', $response->getTargetUrl());
        $crawler = $client->followRedirect();
        $this->assertContains("L'utilisateur a bien été modifié", $crawler->filter('div.alert-success')->text());
        $this->assertContains($editedUsername, $crawler->filter('table')->text());
        $this->assertContains($editedEmail, $crawler->filter('table')->text());
    }
}
