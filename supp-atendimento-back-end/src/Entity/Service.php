<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 30)]
    private ?string $status = null;

    #[ORM\Column(length: 20, options: ['default' => 'NORMAL'])]
    private ?string $priority = 'NORMAL';

    #[ORM\ManyToOne(inversedBy: 'id_service')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sector $sector = null;

    #[ORM\ManyToOne(inversedBy: 'id_services')]
    private ?User $requester = null;

    #[ORM\ManyToOne]
    private ?Attendant $reponsible = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_create = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_update = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_conclusion = null;


    #[ORM\OneToMany(mappedBy: 'service', targetEntity: ServiceHistory::class, orphanRemoval: true)]
    private Collection $histories;

    public function __construct()
    {
        $this->serviceHistory = new ArrayCollection();
    }

    

    public function getHistories(): Collection
    {
        return $this->histories;
    }

    public function addHistory(ServiceHistory $history): self
    {
        if (!$this->histories->contains($history)) {
            $this->histories->add($history);
            $history->setService($this); // Agora usa setService
        }
        return $this;
    }

    public function removeHistory(ServiceHistory $history): self
    {
        if ($this->histories->removeElement($history)) {
            if ($history->getService() === $this) {
                $history->setService(null);
            }
        }
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSector(): ?Sector
    {
        return $this->sector;
    }

    public function setSector(?Sector $sector): static
    {
        $this->sector = $sector;

        return $this;
    }

    public function getRequester(): ?User
    {
        return $this->requester;
    }

    public function setRequester(?User $requester): static
    {
        $this->requester = $requester;

        return $this;
    }

    public function getReponsible(): ?Attendant
    {
        return $this->reponsible;
    }

    public function setReponsible(?Attendant $reponsible): static
    {
        $this->reponsible = $reponsible;

        return $this;
    }

    public function getDateCreate(): ?\DateTimeInterface
    {
        return $this->date_create;
    }

    public function setDateCreate(\DateTimeInterface $date_create): static
    {
        $this->date_create = $date_create;

        return $this;
    }

    public function getDateUpdate(): ?\DateTimeInterface
    {
        return $this->date_update;
    }

    public function setDateUpdate(?\DateTimeInterface $date_update): static
    {
        $this->date_update = $date_update;

        return $this;
    }

    public function getDateConclusion(): ?\DateTimeInterface
    {
        return $this->date_conclusion;
    }

    public function setDateConclusion(?\DateTimeInterface $date_conclusion): static
    {
        $this->date_conclusion = $date_conclusion;

        return $this;
    }

    public function getIdService(): ?ServiceHistory
    {
        return $this->id_service;
    }

    public function setIdService(?ServiceHistory $id_service): static
    {
        $this->id_service = $id_service;

        return $this;
    }

    public function addServiceHistory(ServiceHistory $history): self
    {
        if (!$this->serviceHistory->contains($history)) {
            $this->serviceHistory->add($history);
            $history->setService($this);
        }

        return $this;
    }

    public function removeServiceHistory(ServiceHistory $history): self
    {
        if ($this->serviceHistory->removeElement($history)) {
            if ($history->getService() === $this) {
                $history->setService(null);
            }
        }

        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): static
    {
        $this->priority = $priority;

        return $this;
    }
}
