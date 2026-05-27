<?php

namespace App\Service;

use App\Entity\Attendant;
use App\Entity\Service;
use App\Entity\ServiceHistory;
use Doctrine\ORM\EntityManagerInterface;

class ServiceEvolutionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceAttendantService $serviceAttendantService
    ) {}

    public function registerEvolution(
        Service $service,
        Attendant $author,
        string $comment,
        \DateTimeInterface $dateActivity
    ): ServiceHistory {
        $isAdmin = $author->getFunction() === 'Admin';

        if (!$isAdmin && !$this->serviceAttendantService->isAttendantLinked($service, $author)) {
            // Check if author is the responsible
            if ($service->getReponsible()?->getId() !== $author->getId()) {
                throw new \RuntimeException('Apenas atendentes vinculados à demanda podem registrar evoluções.');
            }
        }

        $currentStatus = $service->getStatus();

        $history = new ServiceHistory();
        $history->setService($service);
        $history->setResponsible($author);
        $history->setComment($comment);
        $history->setDateHistory($dateActivity);
        $history->setStatusPrev($currentStatus);
        $history->setStatusPost($currentStatus);
        $history->setType('EVOLUTION');

        $this->entityManager->persist($history);
        $this->entityManager->flush();

        return $history;
    }

    public function getEvolutions(Service $service): array
    {
        return $this->entityManager->getRepository(ServiceHistory::class)
            ->createQueryBuilder('sh')
            ->where('sh.service = :service')
            ->andWhere('sh.type = :type')
            ->setParameter('service', $service)
            ->setParameter('type', 'EVOLUTION')
            ->orderBy('sh.date_history', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function serializeHistory(ServiceHistory $h): array
    {
        return [
            'id' => $h->getId(),
            'type' => $h->getType(),
            'comment' => $h->getComment(),
            'date_history' => $h->getDateHistory()?->format('Y-m-d\TH:i:s'),
            'responsible' => [
                'id' => $h->getResponsible()?->getId(),
                'name' => $h->getResponsible()?->getName(),
            ],
        ];
    }
}
