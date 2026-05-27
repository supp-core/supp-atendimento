<?php

namespace App\Entity;

use App\Repository\ServiceAttendantRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceAttendantRepository::class)]
#[ORM\UniqueConstraint(name: 'uq_service_attendant', columns: ['service_id', 'attendant_id'])]
class ServiceAttendant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Service $service = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Attendant $attendant = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Attendant $assigned_by = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $assigned_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): static
    {
        $this->service = $service;
        return $this;
    }

    public function getAttendant(): ?Attendant
    {
        return $this->attendant;
    }

    public function setAttendant(?Attendant $attendant): static
    {
        $this->attendant = $attendant;
        return $this;
    }

    public function getAssignedBy(): ?Attendant
    {
        return $this->assigned_by;
    }

    public function setAssignedBy(?Attendant $assigned_by): static
    {
        $this->assigned_by = $assigned_by;
        return $this;
    }

    public function getAssignedAt(): ?\DateTimeInterface
    {
        return $this->assigned_at;
    }

    public function setAssignedAt(\DateTimeInterface $assigned_at): static
    {
        $this->assigned_at = $assigned_at;
        return $this;
    }
}
