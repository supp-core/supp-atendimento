<?php

namespace App\Repository;

use App\Entity\Service;
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
