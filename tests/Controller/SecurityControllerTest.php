<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    use ClientTrait, UsersTrait;

    public function testLoginPageReturns200(): void
    {
        // Given
        $client = $this->getUnauthenticatedClient(followRedirects: false);

        // When
        $client->request('GET', '/login');

        // Then
        $this->assertResponseStatusCodeSame(200);
    }

    public function testAuthenticatedAccessToLoginPageRedirectsToHomepage(): void
    {
        // Given
        [$client] = $this->getAuthenticatedClient('User1', followRedirects: false);

        // When
        $client->request('GET', '/login');

        // Then
        $this->assertResponseRedirects('/');
    }

    public function testLoginWithInvalidCredentials(): void
    {
        // Given
        $client = $this->getUnauthenticatedClient(followRedirects: true);
        $username = 'admin';
        $password = 'invalid';

        // When
        $crawler = $client->request('GET', '/login');
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
        $client = $this->getUnauthenticatedClient(followRedirects: true);
        $username = 'User1';
        $password = 'pass';

        // When
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = $username;
        $form['_password'] = $password;
        $crawler = $client->submit($form);

        // Then
        $this->assertSelectorTextContains('h1', 'Bienvenue sur Todo List');
    }

    public function testSignupPageIsUp(): void
    {
        // Given
        $client = $this->getUnauthenticatedClient(followRedirects: false);

        // When
        $client->request('GET', '/signup');

        // Then
        $this->assertResponseStatusCodeSame(200);
    }

    public function testSignupPageIsNotAccessibleByConnectedUser(): void
    {
        // Given
        [$client] = $this->getAuthenticatedClient('User1', followRedirects: false);

        // When
        $client->request('GET', '/signup');

        // Then
        $this->assertResponseRedirects('/');
    }

    public function testSignupWithInvalidData(): void
    {
        // Given
        $client = $this->getUnauthenticatedClient(followRedirects: true);
        $username = 'U1';
        $password = 'pass';
        $email = 'invalid';

        // When
        $crawler = $client->request('GET', '/signup');
        $form = $crawler->selectButton('Créer un compte')->form();
        $form['user[username]'] = $username;
        $form['user[newPassword][first]'] = $password;
        $form['user[newPassword][second]'] = $password;
        $form['user[email]'] = $email;
        $crawler = $client->submit($form);

        // Then
        $this->assertAnySelectorTextContains('.invalid-feedback', "Le nom d'utilisateur doit contenir au moins 3 caractères.");
        $this->assertAnySelectorTextContains('.invalid-feedback', "Le format de l'adresse n'est pas correcte.");
        $this->assertAnySelectorTextContains('.invalid-feedback', "Le mot de passe doit contenir au moins 10 caractères.");
        $this->assertAnySelectorTextContains('.invalid-feedback', "Le mot de passe est trop faible. Veuillez utiliser un mot de passe plus fort.");
        // Note: The NotCompromisedPassword constraint cannot be used in test mode (https://symfony.com/doc/6.4/reference/constraints/NotCompromisedPassword.html)
    }

    public function testSignupWithExistingUsernameAndEmail(): void
    {
        // Given
        $client = $this->getUnauthenticatedClient(followRedirects: true);
        $user1 = $this->getUser('User1');
        $username = $user1->getUsername();
        $email = $user1->getEmail();

        // When
        $crawler = $client->request('GET', '/signup');
        $form = $crawler->selectButton('Créer un compte')->form();
        $form['user[username]'] = (string) $username;
        $form['user[email]'] = (string) $email;
        $crawler = $client->submit($form);

        // Then
        $this->assertAnySelectorTextContains('.invalid-feedback', "Ce nom d'utilisateur est déjà utilisé.");
        $this->assertAnySelectorTextContains('.invalid-feedback', 'Cette adresse email est déjà utilisée.');
    }

    public function testSignupWithValidData(): void
    {
        // Given
        $client = $this->getUnauthenticatedClient(followRedirects: true);
        $user = $this->createRandomUser(persist: false);

        // When
        $crawler = $client->request('GET', '/signup');
        $form = $crawler->selectButton('Créer un compte')->form();
        $form['user[username]'] = (string) $user->getUsername();
        $form['user[newPassword][first]'] = (string) $user->getNewPassword();
        $form['user[newPassword][second]'] = (string) $user->getNewPassword();
        $form['user[email]'] = (string) $user->getEmail();
        $crawler = $client->submit($form);

        // Then
        $this->assertSelectorTextContains('.alert-success', 'Votre compte a bien été créé.');
    }
}
