<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Security\Voter\TaskVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/tasks', name: 'task')]
class TaskController extends AbstractController
{
    #[Route(path: '', name: '.list', methods: ['GET'])]
    #[IsGranted(TaskVoter::LIST)]
    public function list(TaskRepository $taskRepository): Response
    {
        $tasks = $taskRepository->findAll();
        return $this->render('task/list.html.twig', ['tasks' => $tasks]);
    }

    #[Route(path: '/create', name: '.create', methods: ['GET', 'POST'])]
    #[IsGranted(TaskVoter::CREATE)]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();

        $form = $this->createForm(TaskType::class, $task, ['method' => 'POST']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $author */
            $author = $this->getUser();
            $task->setAuthor($author);

            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été ajoutée.');

            return $this->redirectToRoute('task.list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}/edit', name: '.edit', methods: ['GET', 'PUT'])]
    public function edit(Task $task, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(TaskVoter::EDIT, $task);

        $form = $this->createForm(TaskType::class, $task, ['method' => 'PUT']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task.list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form,
            'task' => $task,
        ]);
    }

    #[Route(path: '/{id}', name: '.toggle', methods: ['PATCH'])]
    public function toggle(Task $task, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(TaskVoter::TOGGLE, $task);

        $task->setIsDone(!$task->isDone());
        $entityManager->flush();

        $status = $task->isDone() ? 'faite' : 'non terminée';
        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme %s.', $task->getTitle(), $status));

        return $this->redirectToRoute('task.list');
    }

    #[Route(path: '/{id}', name: '.delete', methods: ['DELETE'])]
    public function delete(Task $task, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(TaskVoter::DELETE, $task, "Vous ne pouvez pas supprimer une tâche que vous n'avez pas créée.");

        $entityManager->remove($task);
        $entityManager->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task.list');
    }
}
