<?php

// src/Entity/User.php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: 'json')]
    private array $roles = ['ROLE_USER'];  // Set default value

     // Novo campo para identificar se o usuário é um atendente
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isAttendant = false;

    /**
     * @var Collection<int, Service>
     */
    #[ORM\OneToMany(targetEntity: Service::class, mappedBy: 'requester')]
    private Collection $id_services;


    // Novo relacionamento reverso com Attendant
    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Attendant::class)]
    private ?Attendant $attendant = null;

    public function __construct()
    {
        $this->id_services = new ArrayCollection();
        $this->roles = ['ROLE_USER'];  // Initialize in constructor
        $this->isAttendant = false;

    }


   
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
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
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function isIsAttendant(): bool
    {
        return $this->isAttendant;
    }

    public function setIsAttendant(bool $isAttendant): self
    {
        $this->isAttendant = $isAttendant;
        return $this;
    }

    public function getAttendant(): ?Attendant
    {
        return $this->attendant;
    }

    public function setAttendant(?Attendant $attendant): self
    {
        $this->attendant = $attendant;
        return $this;
    }



    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getIdServices(): Collection
    {
        return $this->id_services;
    }

    public function addIdService(Service $idService): static
    {
        if (!$this->id_services->contains($idService)) {
            $this->id_services->add($idService);
            $idService->setRequester($this);
        }

        return $this;
    }

    public function removeIdService(Service $idService): static
    {
        if ($this->id_services->removeElement($idService)) {
            if ($idService->getRequester() === $this) {
                $idService->setRequester(null);
            }
        }

        return $this;
    }
}