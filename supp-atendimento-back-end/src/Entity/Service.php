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



    // Definimos as constantes para as prioridades permitidas
    public const PRIORITY_LOW = 'BAIXA';
    public const PRIORITY_NORMAL = 'NORMAL';
    public const PRIORITY_HIGH = 'ALTA';
    public const PRIORITY_URGENT = 'URGENTE';


    // Lista de prioridades válidas para validação
    public const VALID_PRIORITIES = [
        self::PRIORITY_LOW,
        self::PRIORITY_NORMAL,
        self::PRIORITY_HIGH,
        self::PRIORITY_URGENT
    ];


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

    #[ORM\Column(length: 20, nullable: false, options: ['default' => 'NORMAL'])]
    private string $priority = self::PRIORITY_NORMAL;

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


    #[ORM\OneToMany(mappedBy: 'service', targetEntity: ServiceAttachment::class, cascade: ['persist', 'remove'])]
    private Collection $attachments;


    // Adicionar nova propriedade para rastrear se foi criado por admin
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $createdByAdmin = false;

    // Adicionar relação ao atendente admin que criou o ticket
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'created_by_admin_id', referencedColumnName: 'id', nullable: true)]
    private ?Attendant $createdByAdminAttendant = null;

    public function __construct()
    {
        $this->attachments = new ArrayCollection();
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

    // Método setter com validação
    public function setPriority(string $priority): self
    {
        if (!in_array($priority, self::VALID_PRIORITIES)) {
            throw new \InvalidArgumentException('Prioridade inválida. Valores permitidos: BAIXA, NORMAL, ALTA, URGENTE');
        }

        $this->priority = $priority;
        return $this;
    }

    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(ServiceAttachment $attachment): self
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setService($this);
        }
        return $this;
    }

    public function removeAttachment(ServiceAttachment $attachment): self
    {
        if ($this->attachments->removeElement($attachment)) {
            if ($attachment->getService() === $this) {
                $attachment->setService(null);
            }
        }
        return $this;
    }

    public function isCreatedByAdmin(): bool
    {
        return $this->createdByAdmin;
    }

    public function setCreatedByAdmin(bool $createdByAdmin): self
    {
        $this->createdByAdmin = $createdByAdmin;
        return $this;
    }

    public function getCreatedByAdminAttendant(): ?Attendant
    {
        return $this->createdByAdminAttendant;
    }

    public function setCreatedByAdminAttendant(?Attendant $attendant): self
    {
        $this->createdByAdminAttendant = $attendant;
        return $this;
    }
}
