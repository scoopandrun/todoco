<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Security\Voter\TaskVoter;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[Route('/tasks', name: 'task')]
class TaskController extends AbstractController
{
    /**
     * The current request.
     */
    private Request $request;

    public function __construct(
        RequestStack $requestStack,
        private TaskService $taskService,
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * List tasks.
     * 
     * The URL can contain a query parameter 'done' to filter tasks by their status.
     */
    #[Route(path: '', name: '.list', methods: ['GET'])]
    #[IsGranted(TaskVoter::LIST)]
    public function list(): Response
    {
        $isDone = $this->getIsDoneQueryParameter();

        $tasks = $this->taskService->getTasks($isDone);

        return $this->render('task/list.html.twig', ['tasks' => $tasks]);
    }

    /**
     * List tasks by user.
     */
    #[Route(path: '/user/{id}', name: '.list-by-user', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted(TaskVoter::LIST)]
    public function listByUser(User $user): Response
    {
        $isDone = $this->getIsDoneQueryParameter();

        $tasks = $this->taskService->getTasksByUser($user, $isDone);

        return $this->render('task/list-by-user.html.twig', [
            'tasks' => $tasks,
            'user' => $user,
        ]);
    }

    #[Route(path: '/create', name: '.create', methods: ['GET', 'POST'])]
    #[IsGranted(TaskVoter::CREATE)]
    public function create(): Response
    {
        $task = new Task();

        $form = $this->createForm(TaskType::class, $task, ['method' => 'POST']);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskService->createTask($task);

            $this->addFlash('success', 'La tâche a bien été ajoutée.');

            return $this->redirectToList();
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}/edit', name: '.edit', methods: ['GET', 'PUT'])]
    public function edit(Task $task): Response
    {
        $this->denyAccessUnlessGranted(TaskVoter::EDIT, $task);

        $form = $this->createForm(TaskType::class, $task, ['method' => 'PUT']);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskService->updateTask($task);

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToList();
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form,
            'task' => $task,
        ]);
    }

    #[Route(path: '/{id}', name: '.toggle', methods: ['PATCH'])]
    public function toggle(Task $task): Response
    {
        $this->denyAccessUnlessGranted(TaskVoter::TOGGLE, $task);

        $this->taskService->toggleTask($task);

        $status = $task->isDone() ? 'faite' : 'non terminée';
        $flashType = 'success';
        $flashMessage = sprintf('La tâche %s a bien été marquée comme %s.', $task->getTitle(), $status);

        // If the request is an AJAX request, return a stream response
        if ($this->request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
            $this->request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->render(
                'task/_toggle.stream.html.twig',
                [
                    'task' => $task,
                    'message' => $flashMessage,
                    'type' => $flashType,
                ]
            );
        }

        $this->addFlash($flashType, sprintf('La tâche %s a bien été marquée comme %s.', $task->getTitle(), $status));

        return $this->redirectToList();
    }

    #[Route(path: '/{id}', name: '.delete', methods: ['DELETE'])]
    public function delete(Task $task): Response
    {
        $this->denyAccessUnlessGranted(TaskVoter::DELETE, $task, "Vous ne pouvez pas supprimer une tâche que vous n'avez pas créée.");

        $taskId = $task->getId();
        $authorId = $task->getAuthor()?->getId();

        $this->taskService->deleteTask($task);

        $flashType = 'success';
        $flashMessage = 'La tâche a bien été supprimée.';

        // If the request is an AJAX request, return a stream response
        if ($this->request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
            $this->request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->render(
                'task/_delete.stream.html.twig',
                [
                    'id' => $taskId,
                    'message' => $flashMessage,
                    'type' => $flashType,
                ]
            );
        }

        $this->addFlash($flashType, $flashMessage);

        // Check if the request comes from the list page or the list by user page
        $referer = $this->request->headers->get('referer') ?? '';
        if (strpos($referer, '/tasks/user/') !== false) {
            return $this->redirectToRoute('task.list-by-user', ['id' => $authorId]);
        }

        return $this->redirectToList();
    }

    /**
     * Redirect to the task list page with the query parameter 'done' if it exists.
     */
    private function redirectToList(): Response
    {
        /** @var ?bool $queryParameterDone */
        $queryParameterDone = $this->request->getSession()->get('done');
        $redirectUrl = $this->generateUrl('task.list') . ((null === $queryParameterDone) ? '' : '?done=' . (int) $queryParameterDone);

        return $this->redirect($redirectUrl);
    }

    private function getIsDoneQueryParameter(): ?bool
    {
        $isDone = $this->request->query->get('done');
        $isDone = (null === $isDone) ? null : (bool) $isDone;

        // Save query parameter to session
        $this->request->getSession()->set('done', $isDone);

        return $isDone;
    }
}
