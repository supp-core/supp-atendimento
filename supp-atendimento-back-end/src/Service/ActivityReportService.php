<?php

namespace App\Service;

use App\Entity\Attendant;
use App\Entity\ServiceHistory;
use Doctrine\ORM\EntityManagerInterface;

class ActivityReportService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function buildAttendantReport(
        int $attendantId,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
        ?int $projectId = null
    ): array {
        $attendant = $this->entityManager->getRepository(Attendant::class)->find($attendantId);
        if (!$attendant) {
            throw new \InvalidArgumentException('Atendente não encontrado.');
        }

        $qb = $this->entityManager->getRepository(ServiceHistory::class)
            ->createQueryBuilder('sh')
            ->join('sh.service', 's')
            ->where('sh.responsible = :attendant')
            ->andWhere('sh.comment IS NOT NULL')
            ->andWhere('sh.comment != :empty')
            ->andWhere('sh.date_history BETWEEN :from AND :to')
            ->setParameter('attendant', $attendant)
            ->setParameter('empty', '')
            ->setParameter('from', \DateTime::createFromImmutable($from))
            ->setParameter('to', \DateTime::createFromImmutable($to))
            ->orderBy('sh.date_history', 'ASC');

        if ($projectId) {
            $qb->andWhere('s.project = :project')->setParameter('project', $projectId);
        }

        $histories = $qb->getQuery()->getResult();

        $activities = array_map(function (ServiceHistory $h) {
            $service = $h->getService();
            return [
                'date' => $h->getDateHistory()?->format('Y-m-d'),
                'service_id' => $service?->getId(),
                'service_title' => $service?->getTitle(),
                'service_type' => $service?->getServiceType()?->getName() ?? 'Não definido',
                'category' => $service?->getCategory()?->getName() ?? 'Não definida',
                'description' => $h->getComment() ?? '',
                'status' => $service?->getStatus(),
            ];
        }, $histories);

        $byType = [];
        foreach ($activities as $activity) {
            $type = $activity['service_type'];
            $byType[$type] = ($byType[$type] ?? 0) + 1;
        }

        $byTypeArray = array_map(
            fn($type, $count) => ['type' => $type, 'count' => $count],
            array_keys($byType),
            array_values($byType)
        );

        $project = null;
        if ($projectId) {
            $p = $this->entityManager->getRepository(\App\Entity\Project::class)->find($projectId);
            if ($p) {
                $project = ['id' => $p->getId(), 'name' => $p->getName(), 'acronym' => $p->getAcronym()];
            }
        }

        return [
            'attendant' => [
                'id' => $attendant->getId(),
                'name' => $attendant->getName(),
                'function' => $attendant->getFunction(),
                'sector' => [
                    'id' => $attendant->getSector()?->getId(),
                    'name' => $attendant->getSector()?->getName(),
                ],
            ],
            'period' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
            'project' => $project,
            'activities' => $activities,
            'summary' => [
                'total_activities' => count($activities),
                'by_type' => $byTypeArray,
            ],
        ];
    }
}
