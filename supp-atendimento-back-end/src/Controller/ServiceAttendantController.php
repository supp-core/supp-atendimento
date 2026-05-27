<?php

namespace App\Controller;

use App\Entity\Attendant;
use App\Service\ServiceAttendantService;
use App\Service\ServiceEvolutionService;
use App\Service\ServiceManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/service')]
class ServiceAttendantController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceManager $serviceManager,
        private ServiceAttendantService $serviceAttendantService,
        private ServiceEvolutionService $serviceEvolutionService
    ) {}

    #[Route('/{id}/attendants', methods: ['GET'])]
    public function listAttendants(int $id): JsonResponse
    {
        $service = $this->serviceManager->findById($id);
        if (!$service) {
            return new JsonResponse(['success' => false, 'message' => 'Demanda não encontrada'], 404);
        }

        $attendants = $this->serviceAttendantService->listAttendants($service);

        return new JsonResponse([
            'success' => true,
            'data' => array_map([$this->serviceAttendantService, 'serialize'], $attendants)
        ]);
    }

    #[Route('/{id}/attendants', methods: ['POST'])]
    public function addAttendant(int $id, Request $request): JsonResponse
    {
        try {
            $service = $this->serviceManager->findById($id);
            if (!$service) {
                return new JsonResponse(['success' => false, 'message' => 'Demanda não encontrada'], 404);
            }

            $user = $this->getUser();
            $assignedBy = $this->entityManager->getRepository(Attendant::class)
                ->findOneBy(['user' => $user]);

            if (!$assignedBy) {
                return new JsonResponse(['success' => false, 'message' => 'Acesso negado.'], 403);
            }

            $data = json_decode($request->getContent(), true);
            $attendantId = $data['attendant_id'] ?? null;

            if (!$attendantId) {
                return new JsonResponse(['success' => false, 'message' => 'attendant_id é obrigatório.'], 400);
            }

            $sa = $this->serviceAttendantService->addAttendant($service, (int) $attendantId, $assignedBy);

            return new JsonResponse(['success' => true, 'data' => $this->serviceAttendantService->serialize($sa)], 201);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 409);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/attendants/{attendantId}', methods: ['DELETE'])]
    public function removeAttendant(int $id, int $attendantId): JsonResponse
    {
        try {
            $user = $this->getUser();
            $requestingAttendant = $this->entityManager->getRepository(Attendant::class)
                ->findOneBy(['user' => $user]);

            if (!$requestingAttendant || $requestingAttendant->getFunction() !== 'Admin') {
                return new JsonResponse(['success' => false, 'message' => 'Acesso negado. Apenas Admin pode remover atendentes.'], 403);
            }

            $service = $this->serviceManager->findById($id);
            if (!$service) {
                return new JsonResponse(['success' => false, 'message' => 'Demanda não encontrada'], 404);
            }

            $this->serviceAttendantService->removeAttendant($service, $attendantId);

            return new JsonResponse(['success' => true]);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/evolution', methods: ['POST'])]
    public function registerEvolution(int $id, Request $request): JsonResponse
    {
        try {
            $service = $this->serviceManager->findById($id);
            if (!$service) {
                return new JsonResponse(['success' => false, 'message' => 'Demanda não encontrada'], 404);
            }

            $user = $this->getUser();
            $author = $this->entityManager->getRepository(Attendant::class)
                ->findOneBy(['user' => $user]);

            if (!$author) {
                return new JsonResponse(['success' => false, 'message' => 'Acesso negado.'], 403);
            }

            $data = json_decode($request->getContent(), true);

            if (empty($data['comment'])) {
                return new JsonResponse(['success' => false, 'message' => 'O campo comment é obrigatório.'], 400);
            }

            $dateActivity = !empty($data['date_activity'])
                ? new \DateTime($data['date_activity'])
                : new \DateTime();

            $history = $this->serviceEvolutionService->registerEvolution(
                $service,
                $author,
                $data['comment'],
                $dateActivity
            );

            return new JsonResponse([
                'success' => true,
                'data' => $this->serviceEvolutionService->serializeHistory($history)
            ], 201);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/evolutions', methods: ['GET'])]
    public function listEvolutions(int $id): JsonResponse
    {
        $service = $this->serviceManager->findById($id);
        if (!$service) {
            return new JsonResponse(['success' => false, 'message' => 'Demanda não encontrada'], 404);
        }

        $evolutions = $this->serviceEvolutionService->getEvolutions($service);

        return new JsonResponse([
            'success' => true,
            'data' => array_map([$this->serviceEvolutionService, 'serializeHistory'], $evolutions)
        ]);
    }
}
