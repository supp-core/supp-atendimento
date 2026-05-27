<?php

namespace App\Controller;

use App\Entity\Attendant;
use App\Entity\Service;
use App\Repository\ProjectRepository;
use App\Service\ProjectService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/project')]
class ProjectController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProjectRepository $projectRepository,
        private ProjectService $projectService
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $projects = $this->projectRepository->findAll();
        return new JsonResponse([
            'success' => true,
            'data' => array_map([$this->projectService, 'serialize'], $projects)
        ]);
    }

    #[Route('/{id}/services', methods: ['GET'])]
    public function listServices(int $id, Request $request): JsonResponse
    {
        try {
            $project = $this->projectRepository->findById($id);
            if (!$project) {
                return new JsonResponse(['success' => false, 'message' => 'Projeto não encontrado'], 404);
            }

            $filters = array_filter([
                'status' => $request->query->get('status'),
                'priority' => $request->query->get('priority'),
                'date_from' => $request->query->get('date_from'),
                'date_to' => $request->query->get('date_to'),
            ]);

            $services = $this->entityManager->getRepository(Service::class)
                ->findByProject($id, $filters);

            return new JsonResponse([
                'success' => true,
                'data' => array_map(function ($service) {
                    return [
                        'id' => $service->getId(),
                        'title' => $service->getTitle(),
                        'status' => $service->getStatus(),
                        'priority' => $service->getPriority(),
                        'responsible' => [
                            'id' => $service->getReponsible()?->getId(),
                            'name' => $service->getReponsible()?->getName(),
                        ],
                        'requester' => [
                            'id' => $service->getRequester()?->getId(),
                            'name' => $service->getRequester()?->getName(),
                        ],
                        'serviceType' => $service->getServiceType() ? [
                            'id' => $service->getServiceType()->getId(),
                            'name' => $service->getServiceType()->getName(),
                        ] : null,
                        'dates' => [
                            'created' => $service->getDateCreate()?->format('Y-m-d H:i:s'),
                            'concluded' => $service->getDateConclusion()?->format('Y-m-d H:i:s'),
                        ],
                    ];
                }, $services),
                'meta' => [
                    'total' => count($services),
                    'project' => $this->projectService->serialize($project),
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $project = $this->projectRepository->findById($id);
        if (!$project) {
            return new JsonResponse(['success' => false, 'message' => 'Projeto não encontrado'], 404);
        }
        return new JsonResponse(['success' => true, 'data' => $this->projectService->serialize($project)]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();
            $attendant = $this->entityManager->getRepository(Attendant::class)->findOneBy(['user' => $user]);

            if (!$attendant || $attendant->getFunction() !== 'Admin') {
                return new JsonResponse(['success' => false, 'message' => 'Acesso negado. Apenas administradores podem criar projetos.'], 403);
            }

            $data = json_decode($request->getContent(), true);
            $project = $this->projectService->createProject($data, $attendant);

            return new JsonResponse(['success' => true, 'data' => $this->projectService->serialize($project)], 201);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();
            $attendant = $this->entityManager->getRepository(Attendant::class)->findOneBy(['user' => $user]);

            if (!$attendant || $attendant->getFunction() !== 'Admin') {
                return new JsonResponse(['success' => false, 'message' => 'Acesso negado.'], 403);
            }

            $project = $this->projectRepository->findById($id);
            if (!$project) {
                return new JsonResponse(['success' => false, 'message' => 'Projeto não encontrado'], 404);
            }

            $data = json_decode($request->getContent(), true);
            $this->projectService->updateProject($project, $data);

            return new JsonResponse(['success' => true, 'data' => $this->projectService->serialize($project)]);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/status', methods: ['PATCH'])]
    public function patchStatus(int $id, Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();
            $attendant = $this->entityManager->getRepository(Attendant::class)->findOneBy(['user' => $user]);

            if (!$attendant || $attendant->getFunction() !== 'Admin') {
                return new JsonResponse(['success' => false, 'message' => 'Acesso negado.'], 403);
            }

            $project = $this->projectRepository->findById($id);
            if (!$project) {
                return new JsonResponse(['success' => false, 'message' => 'Projeto não encontrado'], 404);
            }

            $data = json_decode($request->getContent(), true);
            $project->setStatus($data['status'] ?? $project->getStatus());
            $this->projectRepository->save($project);

            return new JsonResponse(['success' => true, 'data' => $this->projectService->serialize($project)]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $user = $this->getUser();
            $attendant = $this->entityManager->getRepository(Attendant::class)->findOneBy(['user' => $user]);

            if (!$attendant || $attendant->getFunction() !== 'Admin') {
                return new JsonResponse(['success' => false, 'message' => 'Acesso negado.'], 403);
            }

            $project = $this->projectRepository->findById($id);
            if (!$project) {
                return new JsonResponse(['success' => false, 'message' => 'Projeto não encontrado'], 404);
            }

            if (!$this->projectService->canDelete($project)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Não é possível excluir um projeto com demandas vinculadas.'
                ], 409);
            }

            $this->projectRepository->remove($project);
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
