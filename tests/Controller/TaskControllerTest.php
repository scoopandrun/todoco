<?php

namespace Tests\App\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private $user1;

    public function setUp(): void
    {
        $this->user1 = [
            'username' => 'User1',
            'password' => 'pass123',
        ];
    }

    public function testTasksPageIsUp(): void
    {
        // Given
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->user1['username'],
            'PHP_AUTH_PW' => $this->user1['password'],
        ]);

        // When
        $client->request('GET', '/tasks');

        // Then
        $this->assertResponseStatusCodeSame(200);
    }

    public function testUnauthenticatedAccessReturnsUnauthorizedResponse(): void
    {
        // Given
        $client = static::createClient();

        // When
        $client->request('GET', '/tasks/create');

        // Then
        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * @return int Info about the created task.
     */
    public function testTaskCanBeCreated(): array
    {
        // Given
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->user1['username'],
            'PHP_AUTH_PW' => $this->user1['password'],
        ]);
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
        $taskId = preg_replace('/[^0-9]/', '', $crawler->filter("a:contains('{$taskTitle}')")->attr('href'));

        // Return info of the created task
        return [
            'title' => $taskTitle,
            'content' => $taskContent,
            'id' => $taskId,
        ];
    }

    /**
     * @depends testTaskCanBeCreated
     * 
     * @param array $taskInfo Info of the task to edit.
     */
    public function testTaskCanBeEdited($taskInfo): void
    {
        // Given
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->user1['username'],
            'PHP_AUTH_PW' => $this->user1['password'],
        ]);
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
     * @depends testTaskCanBeCreated
     * 
     * @param array $taskId Info of the task to toggle.
     */
    public function testTaskCanBeToggledDone($taskInfo): void
    {
        // Given
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->user1['username'],
            'PHP_AUTH_PW' => $this->user1['password'],
        ]);
        $taskId = $taskInfo['id'];

        // When
        $crawler = $client->request('GET', '/tasks');
        $taskTitle = $crawler->filter("a[href='/tasks/{$taskId}/edit']")->text();
        $toggleForm = $crawler->filter("form[action='/tasks/{$taskId}/toggle']")->first()->form();
        $client->submit($toggleForm);

        // Then
        $this->assertResponseRedirects('/tasks');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "La tâche {$taskTitle} a bien été marquée comme faite.");
        // Check if the task is marked as done
        $checkSpan = $crawler
            ->filter("a[href='/tasks/{$taskId}/edit']")
            ->first()
            ->ancestors()
            ->eq(0)
            ->siblings();
        $this->assertCount(1, $checkSpan->filter('span.glyphicon-ok'));
        $this->assertCount(0, $checkSpan->filter('span.glyphicon-remove'));
    }

    /**
     * @depends testTaskCanBeCreated
     * 
     * @param array $taskId Info of the task to toggle.
     */
    public function testTaskCanBeToggledUndone($taskInfo): void
    {
        // Given
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->user1['username'],
            'PHP_AUTH_PW' => $this->user1['password'],
        ]);
        $taskId = $taskInfo['id'];

        // When
        $crawler = $client->request('GET', '/tasks');
        // $taskTitle = $crawler->filter("a[href='/tasks/{$taskId}/edit']")->text();
        $toggleForm = $crawler->filter("form[action='/tasks/{$taskId}/toggle']")->first()->form();
        $client->submit($toggleForm);

        // Then
        $this->assertResponseRedirects('/tasks');
        $crawler = $client->followRedirect();
        // $this->assertContains("La tâche {$taskTitle} a bien été marquée comme non terminée.", $crawler->filter('body')->text());
        // Check if the task is marked as undone
        $checkSpan = $crawler
            ->filter("a[href='/tasks/{$taskId}/edit']")
            ->first()
            ->ancestors()
            ->eq(0)
            ->siblings();
        $this->assertCount(0, $checkSpan->filter('span.glyphicon-ok'));
        $this->assertCount(1, $checkSpan->filter('span.glyphicon-remove'));
    }

    /**
     * @depends testTaskCanBeCreated
     * 
     * @param array $taskId Info of the task to delete.
     */
    public function testTaskCanBeDeleted($taskInfo): void
    {
        // Given
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->user1['username'],
            'PHP_AUTH_PW' => $this->user1['password'],
        ]);
        $taskId = $taskInfo['id'];

        // When
        $crawler = $client->request('GET', '/tasks');
        $taskTitle = $crawler->filter("a[href='/tasks/{$taskId}/edit']")->text();
        $deleteForm = $crawler->filter("form[action='/tasks/{$taskId}/delete']")->first()->form();
        $client->submit($deleteForm);

        // Then
        $this->assertResponseRedirects('/tasks');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextNotContains('body', $taskTitle);
    }
}
