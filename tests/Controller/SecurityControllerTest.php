<?php

namespace Tests\App\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageReturns200()
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

    public function testLoginWithInvalidCredentials()
    {
        // Given
        $client = static::createClient();
        $client->followRedirects();
        $method = 'GET';
        $url = '/';
        $username = 'admin';
        $password = 'invalid';

        // When
        $crawler = $client->request($method, $url);
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = $username;
        $form['_password'] = $password;
        $crawler = $client->submit($form);

        // Then
        $this->assertTrue($crawler->filter('.alert-danger')->count() > 0);
        $this->assertContains('Invalid credentials.', $crawler->html());
    }

    public function testLoginWithValidCredentials()
    {
        // Given
        $client = static::createClient();
        $client->followRedirects();
        $method = 'GET';
        $url = '/';
        $username = 'User1';
        $password = 'pass123';

        // When
        $crawler = $client->request($method, $url);
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = $username;
        $form['_password'] = $password;
        $crawler = $client->submit($form);

        // Then
        $this->assertContains('Bienvenue sur Todo List', $crawler->html());
    }
}
