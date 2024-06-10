<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    use UsersTrait;

    public function testLoginPageReturns200(): void
    {
        // Given
        $client = $this->getUnauthenticatedClient();
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
        $client = $this->getUser1Client();
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
        $client = $this->getUnauthenticatedClient();
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
