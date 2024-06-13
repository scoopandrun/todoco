<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\Depends;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    use UsersTrait;

    public function testTasksPageIsUp(): void
    {
        // Given
        $client = $this->getAuthenticatedClient('User1', followRedirects: false);

        // When
        $client->request('GET', '/tasks');

        // Then
        $this->assertResponseStatusCodeSame(200);
    }

    public function testUnauthenticatedAccessReturnsUnauthorizedResponse(): void
    {
        // Given
        $client = $this->getUnauthenticatedClient(followRedirects: false);

        // When
        $client->request('GET', '/tasks/create');

        // Then
        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * @return array<string, int|string> Info about the created task.
     */
    public function testTaskCanBeCreated(): array
    {
        // Given
        $client = $this->getAuthenticatedClient('User1', followRedirects: false);
        $taskTitle = 'Test task' . uniqid();
        $taskContent = 'Test task content';

        // When
        $crawler = $client->request('GET', '/tasks/create');
        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = $taskTitle;
        $form['task[content]'] = $taskContent;
        $client->submit($form);

        // Then
        $this->assertResponseRedirects('/tasks');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La tâche a bien été ajoutée.');
        $this->assertSelectorTextContains('body', $taskTitle);
        $this->assertSelectorTextContains('body', $taskContent);

        // Get the ID of the created task
        $taskId = (int) preg_replace('/[^0-9]/', '', $crawler->filter("a:contains('{$taskTitle}')")->attr('href'));

        // Return info of the created task
        return [
            'title' => $taskTitle,
            'content' => $taskContent,
            'id' => $taskId,
        ];
    }

    /**
     * @param array<string, int|string> $taskInfo Info of the task to toggle.
     */
    #[Depends('testTaskCanBeCreated')]
    public function testTaskCanBeToggledDone(array $taskInfo): void
    {
        // Given
        $client = $this->getAuthenticatedClient('User1', followRedirects: false);
        $taskId = $taskInfo['id'];
        $taskTitle = $taskInfo['title'];

        // When
        $crawler = $client->request('GET', '/tasks');
        $toggleForm = $crawler->filter("#task-{$taskId}-toggle")->form();
        $client->submit($toggleForm);

        // Then
        $this->assertResponseRedirects('/tasks');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "La tâche {$taskTitle} a bien été marquée comme faite.");
        // Check if the task is marked as done
        $icon = $crawler->filter("#task-{$taskId}-icon");
        $this->assertCount(1, $icon->filter('i.bi-check'));
        $this->assertCount(0, $icon->filter('i.bi-x'));
    }

    /**
     * @param array<string, int|string> $taskInfo Info of the task to toggle.
     */
    #[Depends('testTaskCanBeCreated')]
    #[Depends('testTaskCanBeToggledDone')]
    public function testTaskCanBeToggledUndone(array $taskInfo): void
    {
        // Given
        $client = $this->getAuthenticatedClient('User1', followRedirects: false);
        $taskId = $taskInfo['id'];
        $taskTitle = $taskInfo['title'];

        // When
        $crawler = $client->request('GET', '/tasks');
        $toggleForm = $crawler->filter("#task-{$taskId}-toggle")->first()->form();
        $client->submit($toggleForm);

        // Then
        $this->assertResponseRedirects('/tasks');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "La tâche {$taskTitle} a bien été marquée comme non terminée.");
        // Check if the task is marked as undone
        $icon = $crawler->filter("#task-{$taskId}-icon");
        $this->assertCount(0, $icon->filter('i.bi-check'));
        $this->assertCount(1, $icon->filter('i.bi-x'));
    }

    /**
     * @param array<string, int|string> $taskInfo Info of the task to edit.
     */
    #[Depends('testTaskCanBeCreated')]
    #[Depends('testTaskCanBeToggledUndone')]
    public function testTaskCanBeEdited(array $taskInfo): void
    {
        // Given
        $client = $this->getAuthenticatedClient('User1', followRedirects: false);
        $taskId = $taskInfo['id'];
        $taskTitle = $taskInfo['title'];
        $taskContent = $taskInfo['content'];
        $editedTaskTitle = $taskTitle . ' edited';
        $editedTaskContent = $taskContent . ' edited';

        // When
        $crawler = $client->request('GET', "/tasks/{$taskId}/edit");
        $form = $crawler->selectButton('Modifier')->form();
        $form['task[title]'] = $editedTaskTitle;
        $form['task[content]'] = $editedTaskContent;
        $client->submit($form);

        // Then
        $this->assertResponseRedirects('/tasks');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La tâche a bien été modifiée.');
        $this->assertSelectorTextContains('body', $editedTaskTitle);
        $this->assertSelectorTextContains('body', $editedTaskContent);
    }

    /**
     * @param array<string, int|string> $taskInfo Info of the task to delete.
     */
    #[Depends('testTaskCanBeCreated')]
    #[Depends('testTaskCanBeEdited')]
    public function testTaskCanNotBeDeletedByOtherUser(array $taskInfo): void
    {
        // Given
        $client = $this->getAuthenticatedClient('User2', followRedirects: false);
        $taskId = $taskInfo['id'];

        // When
        $client->request('DELETE', "/tasks/{$taskId}");

        // Then
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * @param array<string, int|string> $taskInfo Info of the task to delete.
     */
    #[Depends('testTaskCanBeCreated')]
    #[Depends('testTaskCanNotBeDeletedByOtherUser')]
    public function testTaskCanBeDeletedByAuthor(array $taskInfo): void
    {
        // Given
        $client = $this->getAuthenticatedClient('User1', followRedirects: false);
        $taskId = $taskInfo['id'];

        // When
        $crawler = $client->request('GET', '/tasks');
        $taskTitle = $crawler->filter("a[href='/tasks/{$taskId}/edit']")->text();
        $deleteForm = $crawler->filter("#task-{$taskId}-delete")->first()->form();
        $client->submit($deleteForm);

        // Then
        $this->assertResponseRedirects('/tasks');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextNotContains('body', $taskTitle);
    }

    public function testAnonymousTaskCannotBeDeletedByOtherUser(): void
    {
        // Given
        $client = $this->getAuthenticatedClient('User2', followRedirects: false);

        // When
        $client->request('DELETE', '/tasks/1');

        // Then
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAnonymousTaskCanBeDeletedByAdmin(): void
    {
        // Given
        $client = $this->getAdminClient(followRedirects: false);

        // When
        $client->request('DELETE', '/tasks/1');

        // Then
        $this->assertResponseRedirects('/tasks');
    }
}
