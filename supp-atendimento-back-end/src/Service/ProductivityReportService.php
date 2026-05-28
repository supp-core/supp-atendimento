<?php

namespace App\Service;

use App\Entity\Attendant;
use App\Entity\Service;
use App\Entity\ServiceHistory;
use Doctrine\ORM\EntityManagerInterface;

class ProductivityReportService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function getConsolidatedReport(array $filters): array
    {
        $attendants = $this->entityManager->getRepository(Attendant::class)->findAll();
        $result = [];

        foreach ($attendants as $attendant) {
            $metrics = $this->buildMetrics($attendant, $filters);
            if ($metrics['total_assigned'] > 0 || $metrics['evolution_count'] > 0) {
                $result[] = [
                    'attendant' => [
                        'id' => $attendant->getId(),
                        'name' => $attendant->getName(),
                        'function' => $attendant->getFunction(),
                        'sector' => [
                            'id' => $attendant->getSector()?->getId(),
                            'name' => $attendant->getSector()?->getName(),
                        ],
                    ],
                    'metrics' => $metrics,
                ];
            }
        }

        return $result;
    }

    public function getAttendantReport(int $attendantId, array $filters): array
    {
        $attendant = $this->entityManager->getRepository(Attendant::class)->find($attendantId);
        if (!$attendant) {
            throw new \InvalidArgumentException('Atendente não encontrado.');
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
            'metrics' => $this->buildMetrics($attendant, $filters),
        ];
    }

    private function buildMetrics(Attendant $attendant, array $filters): array
    {
        $dateFrom = !empty($filters['date_from']) ? new \DateTime($filters['date_from']) : new \DateTime('-1 year');
        $dateTo = !empty($filters['date_to']) ? new \DateTime($filters['date_to'] . ' 23:59:59') : new \DateTime();

        $qb = $this->entityManager->getRepository(Service::class)
            ->createQueryBuilder('s')
            ->where('s.reponsible = :attendant OR EXISTS (SELECT sa FROM App\Entity\ServiceAttendant sa WHERE sa.service = s AND sa.attendant = :attendant)')
            ->andWhere('s.date_create BETWEEN :from AND :to')
            ->setParameter('attendant', $attendant)
            ->setParameter('from', $dateFrom)
            ->setParameter('to', $dateTo);

        if (!empty($filters['project_id'])) {
            $qb->andWhere('s.project = :project')->setParameter('project', (int) $filters['project_id']);
        }
        if (!empty($filters['sector_id'])) {
            $qb->andWhere('s.sector = :sector')->setParameter('sector', (int) $filters['sector_id']);
        }

        $services = $qb->getQuery()->getResult();

        $totalAssigned = count($services);
        $totalConcluded = 0;
        $totalInProgress = 0;
        $totalOpen = 0;
        $resolutionDays = [];
        $byPriority = ['BAIXA' => 0, 'NORMAL' => 0, 'ALTA' => 0, 'URGENTE' => 0];
        $byProject = [];
        $byServiceType = [];
        $monthlySeries = [];

        foreach ($services as $service) {
            switch ($service->getStatus()) {
                case 'CONCLUIDO':
                case 'CONCLUDED':
                    $totalConcluded++;
                    if ($service->getDateConclusion() && $service->getDateCreate()) {
                        $diff = $service->getDateCreate()->diff($service->getDateConclusion());
                        $resolutionDays[] = $diff->days;
                    }
                    $month = $service->getDateConclusion()?->format('Y-m');
                    if ($month) {
                        $monthlySeries[$month] = ($monthlySeries[$month] ?? 0) + 1;
                    }
                    break;
                case 'EM ANDAMENTO':
                case 'IN_PROGRESS':
                    $totalInProgress++;
                    break;
                case 'CANCELADO':
                    // cancelados não entram em nenhuma contagem de status
                    break;
                default:
                    // NOVO, OPEN, RESOLVED, RETORNO
                    $totalOpen++;
            }

            $priority = $service->getPriority();
            if (isset($byPriority[$priority])) {
                $byPriority[$priority]++;
            }

            if ($service->getProject()) {
                $acronym = $service->getProject()->getAcronym();
                $byProject[$acronym] = ($byProject[$acronym] ?? 0) + 1;
            }

            if ($service->getServiceType()) {
                $typeName = $service->getServiceType()->getName();
                $byServiceType[$typeName] = ($byServiceType[$typeName] ?? 0) + 1;
            }
        }

        $evolutionCount = (int) $this->entityManager->getRepository(ServiceHistory::class)
            ->createQueryBuilder('sh')
            ->select('COUNT(sh.id)')
            ->where('sh.responsible = :attendant')
            ->andWhere('sh.type = :type')
            ->andWhere('sh.date_history BETWEEN :from AND :to')
            ->setParameter('attendant', $attendant)
            ->setParameter('type', 'EVOLUTION')
            ->setParameter('from', $dateFrom)
            ->setParameter('to', $dateTo)
            ->getQuery()
            ->getSingleScalarResult();

        $avgResolutionDays = count($resolutionDays) > 0
            ? round(array_sum($resolutionDays) / count($resolutionDays), 1)
            : 0;

        ksort($monthlySeries);
        $monthlyArray = array_map(
            fn($month, $count) => ['month' => $month, 'concluded' => $count],
            array_keys($monthlySeries),
            array_values($monthlySeries)
        );

        $byProjectArray = array_map(
            fn($acronym, $count) => ['acronym' => $acronym, 'count' => $count],
            array_keys($byProject),
            array_values($byProject)
        );

        $byServiceTypeArray = array_map(
            fn($name, $count) => ['name' => $name, 'count' => $count],
            array_keys($byServiceType),
            array_values($byServiceType)
        );

        return [
            'total_assigned' => $totalAssigned,
            'total_concluded' => $totalConcluded,
            'total_in_progress' => $totalInProgress,
            'total_open' => $totalOpen,
            'avg_resolution_days' => $avgResolutionDays,
            'by_priority' => $byPriority,
            'by_project' => $byProjectArray,
            'by_service_type' => $byServiceTypeArray,
            'evolution_count' => $evolutionCount,
            'monthly_series' => $monthlyArray,
        ];
    }
}
