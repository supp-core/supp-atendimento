<?php

namespace App\Repository;

use App\Entity\Attendant;
use App\Entity\Sector;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Service>
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    public function countByStatusForUser(User $user): array
    {
        $rows = $this->createQueryBuilder('s')
            ->select('s.status, COUNT(s.id) as total')
            ->where('s.requester = :user')
            ->setParameter('user', $user)
            ->groupBy('s.status')
            ->getQuery()
            ->getArrayResult();

        $result = ['NOVO' => 0, 'OPEN' => 0, 'IN_PROGRESS' => 0, 'RESOLVED' => 0, 'CONCLUDED' => 0, 'CANCELADO' => 0, 'RETORNO' => 0];
        foreach ($rows as $row) {
            if (array_key_exists($row['status'], $result)) {
                $result[$row['status']] = (int) $row['total'];
            }
        }
        return $result;
    }

    public function countByPriorityForUser(User $user): array
    {
        $rows = $this->createQueryBuilder('s')
            ->select('s.priority, COUNT(s.id) as total')
            ->where('s.requester = :user')
            ->setParameter('user', $user)
            ->groupBy('s.priority')
            ->getQuery()
            ->getArrayResult();

        $result = ['BAIXA' => 0, 'NORMAL' => 0, 'ALTA' => 0, 'URGENTE' => 0];
        foreach ($rows as $row) {
            if (array_key_exists($row['priority'], $result)) {
                $result[$row['priority']] = (int) $row['total'];
            }
        }
        return $result;
    }

    public function countActiveByStatusForAttendant(Attendant $attendant): array
    {
        $id = $attendant->getId();
        $rows = $this->createQueryBuilder('s')
            ->select('s.status, COUNT(s.id) as total')
            ->where(
                's.reponsible = :attendant OR EXISTS (SELECT sa FROM App\Entity\ServiceAttendant sa WHERE sa.service = s AND sa.attendant = :attendant)'
            )
            ->andWhere('s.status NOT IN (:excluded)')
            ->setParameter('attendant', $attendant)
            ->setParameter('excluded', ['CONCLUDED', 'CANCELADO'])
            ->groupBy('s.status')
            ->getQuery()
            ->getArrayResult();

        $result = ['NOVO' => 0, 'OPEN' => 0, 'IN_PROGRESS' => 0, 'RESOLVED' => 0, 'RETORNO' => 0];
        foreach ($rows as $row) {
            if (array_key_exists($row['status'], $result)) {
                $result[$row['status']] = (int) $row['total'];
            }
        }
        return $result;
    }

    public function countActiveAssignedToAttendant(Attendant $attendant): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where(
                's.reponsible = :attendant OR EXISTS (SELECT sa FROM App\Entity\ServiceAttendant sa WHERE sa.service = s AND sa.attendant = :attendant)'
            )
            ->andWhere('s.status NOT IN (:excluded)')
            ->setParameter('attendant', $attendant)
            ->setParameter('excluded', ['CONCLUDED', 'CANCELADO'])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countActiveInSector(Sector $sector): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.sector = :sector')
            ->andWhere('s.status NOT IN (:excluded)')
            ->setParameter('sector', $sector)
            ->setParameter('excluded', ['CONCLUDED', 'CANCELADO'])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countInProgressForAttendant(Attendant $attendant): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where(
                's.reponsible = :attendant OR EXISTS (SELECT sa FROM App\Entity\ServiceAttendant sa WHERE sa.service = s AND sa.attendant = :attendant)'
            )
            ->andWhere('s.status = :status')
            ->setParameter('attendant', $attendant)
            ->setParameter('status', 'IN_PROGRESS')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countUrgentActiveForAttendant(Attendant $attendant): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where(
                's.reponsible = :attendant OR EXISTS (SELECT sa FROM App\Entity\ServiceAttendant sa WHERE sa.service = s AND sa.attendant = :attendant)'
            )
            ->andWhere('s.priority = :priority')
            ->andWhere('s.status NOT IN (:excluded)')
            ->setParameter('attendant', $attendant)
            ->setParameter('priority', 'URGENTE')
            ->setParameter('excluded', ['CONCLUDED', 'CANCELADO'])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countSystemTotal(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countNoResponsible(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.reponsible IS NULL')
            ->andWhere('s.status NOT IN (:excluded)')
            ->setParameter('excluded', ['CONCLUDED', 'CANCELADO'])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countBySector(): array
    {
        $rows = $this->createQueryBuilder('s')
            ->select('sec.name as sector, COUNT(s.id) as total')
            ->join('s.sector', 'sec')
            ->where('s.status NOT IN (:excluded)')
            ->setParameter('excluded', ['CONCLUDED', 'CANCELADO'])
            ->groupBy('sec.id, sec.name')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getArrayResult();

        return array_map(fn($r) => ['sector' => $r['sector'], 'count' => (int) $r['total']], $rows);
    }

    public function countByStatusSystem(): array
    {
        $rows = $this->createQueryBuilder('s')
            ->select('s.status, COUNT(s.id) as total')
            ->groupBy('s.status')
            ->getQuery()
            ->getArrayResult();

        $result = ['NOVO' => 0, 'OPEN' => 0, 'IN_PROGRESS' => 0, 'RESOLVED' => 0, 'CONCLUDED' => 0, 'CANCELADO' => 0, 'RETORNO' => 0];
        foreach ($rows as $row) {
            if (array_key_exists($row['status'], $result)) {
                $result[$row['status']] = (int) $row['total'];
            }
        }
        return $result;
    }

    public function findByProject(int $projectId, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->orderBy('s.date_create', 'DESC');

        if (!empty($filters['status'])) {
            $qb->andWhere('s.status = :status')->setParameter('status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $qb->andWhere('s.priority = :priority')->setParameter('priority', $filters['priority']);
        }
        if (!empty($filters['date_from'])) {
            $qb->andWhere('s.date_create >= :date_from')->setParameter('date_from', new \DateTime($filters['date_from']));
        }
        if (!empty($filters['date_to'])) {
            $qb->andWhere('s.date_create <= :date_to')->setParameter('date_to', new \DateTime($filters['date_to'] . ' 23:59:59'));
        }

        return $qb->getQuery()->getResult();
    }
}
