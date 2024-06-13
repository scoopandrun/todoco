<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    use UsersTrait;

    public function testUsersPageIsUp(): void
    {
        // Given
        $client = $this->getAdminClient(followRedirects: false);

        // When
        $client->request('GET', '/users');

        // Then
        $this->assertResponseIsSuccessful();
    }

    public function testUnauthenticatedAccessReturnsUnauthorizedResponse(): void
    {
        // Given
        $client = $this->getUnauthenticatedClient(followRedirects: false);

        // When
        $client->request('GET', '/users');

        // Then
        $this->assertResponseStatusCodeSame(401);
    }

    public function testNonAdminAccessReturnsForbiddenResponse(): void
    {
        // Given
        $client = $this->getAuthenticatedClient('User1', followRedirects: false);

        // When
        $client->request('GET', '/users');

        // Then
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * @return array<string, int|string> Info about the created user.
     */
    public function testAdminCanCreateAUser(): void
    {
        // Given
        $client = $this->getAdminClient(followRedirects: false);
        $user = $this->createRandomUser(persist: false);

        // When
        $crawler = $client->request('GET', '/users/create');
        $form = $crawler->selectButton('Créer un compte')->form();
        $form['user[username]'] = $user->getUsername();
        $form['user[newPassword][first]'] = $user->getPassword();
        $form['user[newPassword][second]'] = $user->getPassword();
        $form['user[email]'] = $user->getEmail();
        $client->submit($form);

        // Then
        $this->assertResponseRedirects('/users');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "L'utilisateur a bien été ajouté.");
        $this->assertSelectorTextContains('table', $user->getUsername());
        $this->assertSelectorTextContains('table', $user->getEmail());
    }

    public function testAdminCanEditAUser(): void
    {
        // Given
        $client = $this->getAdminClient(followRedirects: false);
        $user = $this->createRandomUser(persist: true);
        $editedUsername = $user->getUsername() . 'edited';
        $editedEmail = str_replace('@', 'edited@', $user->getEmail());

        // When
        $crawler = $client->request('GET', "/users/{$user->getId()}");

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[username]'] = $editedUsername;
        $form['user[email]'] = $editedEmail;

        $client->submit($form);

        // Then
        $this->assertResponseRedirects('/users');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "L'utilisateur a bien été modifié.");
        $this->assertSelectorTextContains('table', $editedUsername);
        $this->assertSelectorTextContains('table', $editedEmail);
    }

    public function testUserCanAccessTheirProfile(): void
    {
        // Given
        $client = $this->getAuthenticatedClient('User1', followRedirects: false);

        // When
        $client->request('GET', '/users/me');

        // Then
        $this->assertResponseIsSuccessful();
    }

    public function testUserCannotChangePasswordWithoutCurrentPassword(): void
    {
        // Given
        $client = $this->getAuthenticatedClient('User1', followRedirects: true);
        $newPassword = bin2hex(random_bytes(16));

        // When
        $crawler = $client->request('GET', '/users/me');
        $form = $crawler->selectButton('Modifier')->form();
        $form['user[newPassword][first]'] = $newPassword;
        $form['user[newPassword][second]'] = $newPassword;
        $client->submit($form);

        // Then
        $this->assertAnySelectorTextContains('.invalid-feedback', "Vous devez entrer votre mot de passe actuel pour définir un nouveau mot de passe.");
    }

    public function testUserCannotChangePasswordWithIncorrectCurrentPassword(): void
    {
        // Given
        $client = $this->getAuthenticatedClient('User1', followRedirects: true);
        $newPassword = bin2hex(random_bytes(16));
        $currentPassword = 'incorrect';

        // When
        $crawler = $client->request('GET', '/users/me');
        $form = $crawler->selectButton('Modifier')->form();
        $form['user[currentPassword]'] = $currentPassword;
        $form['user[newPassword][first]'] = $newPassword;
        $form['user[newPassword][second]'] = $newPassword;
        $client->submit($form);

        // Then
        $this->assertAnySelectorTextContains('.invalid-feedback', "Votre mot de passe actuel est incorrect.");
    }

    public function testUserCanEditTheirProfile(): void
    {
        // Given
        $client = $this->getUnauthenticatedClient(followRedirects: true);
        $user = $this->createRandomUser(persist: true);
        $client->loginUser($user);
        $editedUsername = $user->getUsername() . 'edited';
        $editedEmail = str_replace('@', 'edited@', $user->getEmail());
        $newPassword = bin2hex(random_bytes(16));

        // When
        $crawler = $client->request('GET', '/users/me');
        $form = $crawler->selectButton('Modifier')->form();
        $form['user[username]'] = $editedUsername;
        $form['user[email]'] = $editedEmail;
        $form['user[currentPassword]'] = $user->getCurrentPassword();
        $form['user[newPassword][first]'] = $newPassword;
        $form['user[newPassword][second]'] = $newPassword;
        $client->submit($form);

        // Then
        $this->assertSelectorTextContains('.alert-success', "Votre compte a bien été modifié.");
        $this->assertSelectorTextContains('h1', $editedUsername);
    }

    public function testUserCannotDeleteAnotherUser(): void
    {
        // Given
        $client = $this->getAuthenticatedClient('User1', followRedirects: false);
        $user = $this->createRandomUser(persist: true);

        // When
        $client->request('DELETE', "/users/{$user->getId()}");

        // Then
        $this->assertResponseStatusCodeSame(403);
    }

    public function testUserCanDeleteTheirOwnAccount(): void
    {
        // Given
        $client = $this->getUnauthenticatedClient(followRedirects: false);
        $user = $this->createRandomUser(persist: true);
        $client->loginUser($user);

        // When
        $crawler = $client->request('GET', '/users/me');
        $deleteForm = $crawler->filter('#delete-account')->form();
        $client->submit($deleteForm);

        // Then
        $this->assertResponseRedirects('/');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "Votre compte a bien été supprimé.");
    }

    public function testAdminCanDeleteAUserAccount(): void
    {
        // Given
        $client = $this->getAdminClient(followRedirects: false);
        $user = $this->createRandomUser(persist: true);

        // When
        $client->request('DELETE', "/users/{$user->getId()}");

        // Then
        $this->assertResponseRedirects('/users');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "L'utilisateur a bien été supprimé.");
        $this->assertSelectorTextNotContains('table', $user->getUsername());
        $this->assertSelectorTextNotContains('table', $user->getEmail());
    }
}
