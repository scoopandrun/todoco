<?php

namespace App\Entity;

use App\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse email est déjà utilisée.')]
#[UniqueEntity(fields: ['username'], message: "Ce nom d'utilisateur est déjà utilisé.")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 25, unique: true)]
    #[Assert\NotBlank(message: "Vous devez saisir un nom d'utilisateur.")]
    #[Assert\Type('string')]
    #[Assert\Length(
        min: 3,
        max: 25,
        minMessage: "Le nom d'utilisateur doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom d'utilisateur doit contenir au maximum {{ limit }} caractères."
    )]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 254)]
    #[Assert\NotBlank(message: 'Vous devez saisir une adresse email.')]
    #[Assert\Email(message: "Le format de l'adresse n'est pas correcte.")]
    private ?string $email = null;

    /**
     * @var null|array<string>
     */
    #[ORM\Column]
    private ?array $roles = [];

    #[Assert\When(
        expression: 'this.getNewPassword()',
        groups: ['account_update'],
        constraints: [
            new Assert\Sequentially(
                [
                    new Assert\Type('string'),
                    new Assert\NotBlank(message: 'Vous devez entrer votre mot de passe actuel pour définir un nouveau mot de passe.'),
                    new SecurityAssert\UserPassword(message: 'Votre mot de passe actuel est incorrect.'),
                ],
                groups: ['account_update'],
            ),
        ],
    )]
    private ?string $currentPassword = null;

    #[Assert\NotBlank(groups: ['registration'])]
    #[AppAssert\PasswordRequirements()]
    private ?string $newPassword = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $password = null;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'author')]
    private Collection $tasks;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getCurrentPassword(): ?string
    {
        return $this->currentPassword;
    }

    public function setCurrentPassword(#[\SensitiveParameter] string $currentPassword): static
    {
        $this->currentPassword = $currentPassword;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(#[\SensitiveParameter] string $newPassword): static
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles ?? [];
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array<string> $roles 
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles());
    }

    public function eraseCredentials(): void
    {
        $this->newPassword = null;
        $this->currentPassword = null;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setAuthor($this);
        }

        return $this;
    }
}
