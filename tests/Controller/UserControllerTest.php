<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\UX\Turbo\TurboBundle;

class UserControllerTest extends WebTestCase
{
    use ClientTrait;

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
        [$client] = $this->getAuthenticatedClient('User1', followRedirects: false);

        // When
        $client->request('GET', '/users');

        // Then
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanCreateAUser(): void
    {
        // Given
        $client = $this->getAdminClient(followRedirects: false);
        $newUser = $this->createRandomUser(persist: false);

        // When
        $crawler = $client->request('GET', '/users/create');
        $form = $crawler->selectButton('Créer un compte')->form();
        $form['user[username]'] = (string) $newUser->getUsername();
        $form['user[newPassword][first]'] = (string) $newUser->getPassword();
        $form['user[newPassword][second]'] = (string) $newUser->getPassword();
        $form['user[email]'] = (string) $newUser->getEmail();
        $client->submit($form);

        // Then
        $this->assertResponseRedirects('/users');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "L'utilisateur a bien été ajouté.");
        $this->assertSelectorTextContains('table', $newUser->getUsername());
        $this->assertSelectorTextContains('table', $newUser->getEmail());
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
        [$client] = $this->getAuthenticatedClient('User1', followRedirects: false);

        // When
        $client->request('GET', '/users/me');

        // Then
        $this->assertResponseIsSuccessful();
    }

    public function testUserCannotChangePasswordWithoutCurrentPassword(): void
    {
        // Given
        [$client] = $this->getAuthenticatedClient('User1', followRedirects: true);
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
        [$client] = $this->getAuthenticatedClient('User1', followRedirects: true);
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
        $form['user[currentPassword]'] = (string) $user->getCurrentPassword();
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
        [$client] = $this->getAuthenticatedClient('User1', followRedirects: false);
        $otherUser = $this->createRandomUser(persist: true);

        // When
        $client->request('DELETE', "/users/{$otherUser->getId()}");

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
        $this->assertResponseRedirects('/login');
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

    public function testDeletingUserWithStreamFormatRedirectsToStream(): void
    {
        // Given
        $client = $this->getAdminClient(followRedirects: false);
        $user = $this->createRandomUser(persist: true);

        // When
        $client->request('DELETE', "/users/{$user->getId()}", [], [], ['HTTP_ACCEPT' => TurboBundle::STREAM_MEDIA_TYPE]);

        // Then
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHasHeader('Content-Type', TurboBundle::STREAM_MEDIA_TYPE);
    }
}
