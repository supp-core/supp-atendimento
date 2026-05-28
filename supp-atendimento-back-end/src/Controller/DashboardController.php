<?php

namespace App\Controller;

use App\Entity\Attendant;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dashboard')]
class DashboardController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceRepository $serviceRepository
    ) {}

    #[Route('/user-stats', methods: ['GET'])]
    public function userStats(): JsonResponse
    {
        try {
            $user = $this->getUser();

            $byStatus = $this->serviceRepository->countByStatusForUser($user);
            $byPriority = $this->serviceRepository->countByPriorityForUser($user);

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'by_status' => $byStatus,
                    'by_priority' => $byPriority,
                    'total' => array_sum($byStatus),
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/attendant-stats', methods: ['GET'])]
    public function attendantStats(): JsonResponse
    {
        try {
            $user = $this->getUser();
            $attendant = $this->entityManager->getRepository(Attendant::class)->findOneBy(['user' => $user]);

            if (!$attendant) {
                return new JsonResponse(['success' => false, 'message' => 'Atendente não encontrado.'], 403);
            }

            $sector = $attendant->getSector();

            $data = [
                'assigned_to_me' => $this->serviceRepository->countActiveAssignedToAttendant($attendant),
                'in_my_sector' => $sector ? $this->serviceRepository->countActiveInSector($sector) : 0,
                'in_progress' => $this->serviceRepository->countInProgressForAttendant($attendant),
                'urgent' => $this->serviceRepository->countUrgentActiveForAttendant($attendant),
                'by_status' => $this->serviceRepository->countActiveByStatusForAttendant($attendant),
            ];

            if ($attendant->getFunction() === 'Admin') {
                $data['admin'] = [
                    'system_total' => $this->serviceRepository->countSystemTotal(),
                    'no_responsible' => $this->serviceRepository->countNoResponsible(),
                    'by_sector' => $this->serviceRepository->countBySector(),
                    'by_status_system' => $this->serviceRepository->countByStatusSystem(),
                ];
            }

            return new JsonResponse(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
