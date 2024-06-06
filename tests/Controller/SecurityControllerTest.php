<?php

namespace Tests\App\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageReturns200(): void
    {
        // Given
        $client = static::createClient();
        $method = 'GET';
        $url = '/login';

        // When
        $client->request($method, $url);

        // Then
        $this->assertResponseStatusCodeSame(200);
    }

    public function testAuthenticatedAccessToLoginPageRedirectsToHomepage(): void
    {
        // Given
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'User1',
            'PHP_AUTH_PW' => 'pass123',
        ]);
        $method = 'GET';
        $url = '/login';

        // When
        $client->request($method, $url);

        // Then
        $this->assertResponseRedirects('/');
    }

    public function testLoginWithInvalidCredentials(): void
    {
        // Given
        $client = static::createClient();
        $client->followRedirects();
        $method = 'GET';
        $url = '/login';
        $username = 'admin';
        $password = 'invalid';

        // When
        $crawler = $client->request($method, $url);
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = $username;
        $form['_password'] = $password;
        $crawler = $client->submit($form);

        // Then
        $this->assertSelectorTextContains('.alert-danger', 'Invalid credentials.');
    }

    public function testLoginWithValidCredentials(): void
    {
        // Given
        $client = static::createClient();
        $client->followRedirects();
        $method = 'GET';
        $url = '/login';
        $username = 'User1';
        $password = 'pass123';

        // When
        $crawler = $client->request($method, $url);
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = $username;
        $form['_password'] = $password;
        $crawler = $client->submit($form);

        // Then
        $this->assertSelectorTextContains('h1', 'Bienvenue sur Todo List');
    }
}
