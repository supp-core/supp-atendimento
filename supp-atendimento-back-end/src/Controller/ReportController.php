<?php

namespace App\Controller;

use App\Entity\Attendant;
use App\Service\ActivityReportService;
use App\Service\ProductivityReportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/report')]
class ReportController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductivityReportService $productivityService,
        private ActivityReportService $activityService
    ) {}

    // RF-005: Consolidated productivity report (Admin only)
    #[Route('/productivity', methods: ['GET'])]
    public function productivity(Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();
            $attendant = $this->entityManager->getRepository(Attendant::class)->findOneBy(['user' => $user]);

            if (!$attendant || $attendant->getFunction() !== 'Admin') {
                return new JsonResponse(['success' => false, 'message' => 'Acesso negado. Apenas Admin.'], 403);
            }

            if (!$request->query->get('date_from') || !$request->query->get('date_to')) {
                return new JsonResponse(['success' => false, 'message' => 'date_from e date_to são obrigatórios.'], 400);
            }

            $filters = [
                'date_from' => $request->query->get('date_from'),
                'date_to' => $request->query->get('date_to'),
                'project_id' => $request->query->get('project_id'),
                'sector_id' => $request->query->get('sector_id'),
            ];

            $data = $this->productivityService->getConsolidatedReport($filters);

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'period' => ['from' => $filters['date_from'], 'to' => $filters['date_to']],
                    'attendants' => $data,
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // RF-005: Individual attendant productivity report
    #[Route('/productivity/{attendantId}', methods: ['GET'])]
    public function attendantProductivity(int $attendantId, Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();
            $requestingAttendant = $this->entityManager->getRepository(Attendant::class)
                ->findOneBy(['user' => $user]);

            if (!$requestingAttendant) {
                return new JsonResponse(['success' => false, 'message' => 'Acesso negado.'], 403);
            }

            $isAdmin = $requestingAttendant->getFunction() === 'Admin';
            if (!$isAdmin && $requestingAttendant->getId() !== $attendantId) {
                return new JsonResponse(['success' => false, 'message' => 'Acesso negado. Você só pode ver seu próprio relatório.'], 403);
            }

            if (!$request->query->get('date_from') || !$request->query->get('date_to')) {
                return new JsonResponse(['success' => false, 'message' => 'date_from e date_to são obrigatórios.'], 400);
            }

            $filters = [
                'date_from' => $request->query->get('date_from'),
                'date_to' => $request->query->get('date_to'),
                'project_id' => $request->query->get('project_id'),
                'sector_id' => $request->query->get('sector_id'),
            ];

            $data = $this->productivityService->getAttendantReport($attendantId, $filters);

            return new JsonResponse([
                'success' => true,
                'data' => array_merge(
                    $data,
                    ['period' => ['from' => $filters['date_from'], 'to' => $filters['date_to']]]
                )
            ]);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // RF-006: Activity report by attendant
    #[Route('/activity/{attendantId}', methods: ['GET'])]
    public function activityReport(int $attendantId, Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();
            $requestingAttendant = $this->entityManager->getRepository(Attendant::class)
                ->findOneBy(['user' => $user]);

            if (!$requestingAttendant) {
                return new JsonResponse(['success' => false, 'message' => 'Acesso negado.'], 403);
            }

            $isAdmin = $requestingAttendant->getFunction() === 'Admin';
            if (!$isAdmin && $requestingAttendant->getId() !== $attendantId) {
                return new JsonResponse(['success' => false, 'message' => 'Acesso negado. Você só pode ver seu próprio relatório.'], 403);
            }

            $dateFrom = $request->query->get('date_from');
            $dateTo = $request->query->get('date_to');

            if (!$dateFrom || !$dateTo) {
                return new JsonResponse(['success' => false, 'message' => 'date_from e date_to são obrigatórios.'], 400);
            }

            $projectId = $request->query->get('project_id') ? (int) $request->query->get('project_id') : null;

            $data = $this->activityService->buildAttendantReport(
                $attendantId,
                new \DateTimeImmutable($dateFrom),
                new \DateTimeImmutable($dateTo . ' 23:59:59'),
                $projectId
            );

            return new JsonResponse(['success' => true, 'data' => $data]);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
