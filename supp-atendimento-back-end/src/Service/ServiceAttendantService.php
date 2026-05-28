<?php

namespace App\Service;

use App\Entity\Attendant;
use App\Entity\Service;
use App\Entity\ServiceAttendant;
use Doctrine\ORM\EntityManagerInterface;

class ServiceAttendantService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function addAttendant(Service $service, int $attendantId, Attendant $assignedBy): ServiceAttendant
    {
        $attendant = $this->entityManager->getRepository(Attendant::class)->find($attendantId);
        if (!$attendant) {
            throw new \InvalidArgumentException('Atendente não encontrado.');
        }

        $existing = $this->entityManager->getRepository(ServiceAttendant::class)
            ->findOneBy(['service' => $service, 'attendant' => $attendant]);

        if ($existing) {
            throw new \RuntimeException('Atendente já vinculado a esta demanda.');
        }

        $sa = new ServiceAttendant();
        $sa->setService($service);
        $sa->setAttendant($attendant);
        $sa->setAssignedBy($assignedBy);
        $sa->setAssignedAt(new \DateTime());

        $this->entityManager->persist($sa);
        $this->entityManager->flush();

        return $sa;
    }

    public function removeAttendant(Service $service, int $attendantId): void
    {
        $attendant = $this->entityManager->getRepository(Attendant::class)->find($attendantId);
        if (!$attendant) {
            throw new \InvalidArgumentException('Atendente não encontrado.');
        }

        $sa = $this->entityManager->getRepository(ServiceAttendant::class)
            ->findOneBy(['service' => $service, 'attendant' => $attendant]);

        if (!$sa) {
            throw new \InvalidArgumentException('Vínculo não encontrado.');
        }

        $this->entityManager->remove($sa);
        $this->entityManager->flush();
    }

    public function clearAttendants(Service $service): void
    {
        $entries = $this->entityManager->getRepository(ServiceAttendant::class)
            ->findBy(['service' => $service]);

        foreach ($entries as $entry) {
            $this->entityManager->remove($entry);
        }
        $this->entityManager->flush();
    }

    public function listAttendants(Service $service): array
    {
        return $this->entityManager->getRepository(ServiceAttendant::class)
            ->findBy(['service' => $service], ['assigned_at' => 'ASC']);
    }

    public function isAttendantLinked(Service $service, Attendant $attendant): bool
    {
        return (bool) $this->entityManager->getRepository(ServiceAttendant::class)
            ->findOneBy(['service' => $service, 'attendant' => $attendant]);
    }

    public function serialize(ServiceAttendant $sa): array
    {
        return [
            'id' => $sa->getId(),
            'attendant' => [
                'id' => $sa->getAttendant()?->getId(),
                'name' => $sa->getAttendant()?->getName(),
                'function' => $sa->getAttendant()?->getFunction(),
            ],
            'assigned_by' => [
                'id' => $sa->getAssignedBy()?->getId(),
                'name' => $sa->getAssignedBy()?->getName(),
            ],
            'assigned_at' => $sa->getAssignedAt()?->format('Y-m-d\TH:i:s'),
        ];
    }
}
