<?php

namespace App\Entity;

use App\Repository\ServiceHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceHistoryRepository::class)]
class ServiceHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Relação ManyToOne com Service - cada histórico pertence a um serviço
    #[ORM\ManyToOne(inversedBy: 'histories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Service $service = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_history = null;

    #[ORM\Column(length: 30)]
    private ?string $status_prev = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $status_post = null;

    #[ORM\ManyToOne]
    private ?Attendant $responsible = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    // Novo método para gerenciar a relação com Service
    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getDateHistory(): ?\DateTimeInterface
    {
        return $this->date_history;
    }

    public function setDateHistory(\DateTimeInterface $date_history): self
    {
        $this->date_history = $date_history;
        return $this;
    }

    public function getStatusPrev(): ?string
    {
        return $this->status_prev;
    }

    public function setStatusPrev(string $status_prev): self
    {
        $this->status_prev = $status_prev;
        return $this;
    }

    public function getStatusPost(): ?string
    {
        return $this->status_post;
    }

    public function setStatusPost(?string $status_post): self
    {
        $this->status_post = $status_post;
        return $this;
    }

    public function getResponsible(): ?Attendant
    {
        return $this->responsible;
    }

    public function setResponsible(?Attendant $responsible): self
    {
        $this->responsible = $responsible;
        return $this;
    }
}