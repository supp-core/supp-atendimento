<?php

namespace App\Controller;

use App\Entity\Service;
use App\Service\ServiceManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;  // Adicionando o import do Response

#[Route('/api/service')]
class ServiceController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ServiceManager $serviceManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        ServiceManager $serviceManager

    ) {
        $this->entityManager = $entityManager;
        $this->serviceManager = $serviceManager;
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {

            // Obtém o usuário logado
            $user = $this->getUser();

            if (!$user) {
                throw new BadRequestException('Usuário não autenticado');
            }


            $files = $request->files->get('files');

            if (!is_array($files)) {
                $files = [$files];
            }
            $data = [
                'title' => $request->request->get('title'),
                'description' => $request->request->get('description'),
                'priority' => $request->request->get('priority'),
                'sector_id' => $request->request->get('sector_id'),
                'requester_id' => $user,
                'files' => $files // Pega os arquivos
            ];

            // $data = json_decode($request->getContent(), true);
            //$data['requester_id'] = $user;
            $data['status'] = 'Novo'; // Status inicial do chamado
            $data['date_create'] = new \DateTime(); // Data de criação*/
            $service = $this->serviceManager->createService($data);

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'id' => $service->getId(),

                    'title' => $service->getTitle(),
                    'description' => $service->getDescription(),
                    'status' => $service->getStatus(),
                    'sector' => [
                        'id' => $service->getSector()->getId(),
                        'name' => $service->getSector()->getName()
                    ],
                    'priority' => $service->getPriority(),
                    'requester' => [
                        'id' => $service->getRequester()->getId(),
                        'name' => $service->getRequester()->getName(),
                        'email' => $service->getRequester()->getEmail()
                    ],
                    'dates' => [
                        'created' => $service->getDateCreate()->format('Y-m-d H:i:s')
                    ],
                    'attachments' => array_map(function ($attachment) {
                        return [
                            'id' => $attachment->getId(),
                            'filename' => $attachment->getFilename(),
                            'originalFilename' => $attachment->getOriginalFilename()
                        ];
                    }, $service->getAttachments()->toArray())
                ]
            ], 201);
        } catch (BadRequestException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    // In ServiceController.php
    // In ServiceController.php

    #[Route('/sector/{sector?}', methods: ['GET'])]
    public function listBySector(?string $sector = null): JsonResponse
    {
        try {
            // Get services based on whether a sector was specified
            $services = $sector
                ? $this->serviceManager->getServicesBySector($sector)
                : $this->serviceManager->getAllServices();

            // Transform the services into a format suitable for JSON response
            $response = array_map(function ($service) {
                return [
                    'id' => $service->getId(),
                    'title' => $service->getTitle(),
                    'description' => $service->getDescription(),
                    'status' => $service->getStatus(),
                    'sector' => [
                        'id' => $service->getSector()?->getId(),
                        'name' => $service->getSector()?->getName(),
                    ],
                    'requester' => [
                        'id' => $service->getRequester()?->getId(),
                        'name' => $service->getRequester()?->getName(),
                        'email' => $service->getRequester()?->getEmail(),
                    ],
                    'responsible' => [
                        'id' => $service->getReponsible()?->getId(),
                        'name' => $service->getReponsible()?->getName(),
                        'function' => $service->getReponsible()?->getFunction(),
                    ],
                    'dates' => [
                        'created' => $service->getDateCreate()?->format('Y-m-d H:i:s'),
                        'updated' => $service->getDateUpdate()?->format('Y-m-d H:i:s'),
                        'concluded' => $service->getDateConclusion()?->format('Y-m-d H:i:s'),
                    ],
                ];
            }, $services);

            return new JsonResponse([
                'success' => true,
                'data' => $response,
                'count' => count($response)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error fetching services: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/attendant/{id}', methods: ['GET'])]
    public function listByAttendant(int $id, Request $request): JsonResponse
    {
        try {
            // Obtém parâmetros de paginação
            $page = $request->query->get('page', 1);
            $perPage = $request->query->get('per_page', 10);

            // Cria query builder
            $queryBuilder = $this->entityManager->getRepository(Service::class)
                ->createQueryBuilder('s')
                ->join('s.reponsible', 'a')
                ->leftJoin('s.requester', 'u')
                ->leftJoin('s.sector', 'sect')
                ->select('s', 'sect', 'u', 'a')
                ->where('a.id = :attendantId')
                ->setParameter('attendantId', $id);
        

            // Aplicar filtro por título
            if ($title = $request->query->get('title')) {
                $queryBuilder->andWhere('s.title LIKE :title')
                    ->setParameter('title', '%' . $title . '%');
            }

            // Aplicar filtro por solicitante
            if ($requester = $request->query->get('requester')) {
                $queryBuilder->andWhere('u.name LIKE :requester')
                    ->setParameter('requester', '%' . $requester . '%');
            }

            // Aplicar filtro por status
            if ($status = $request->query->get('status')) {
                $queryBuilder->andWhere('s.status = :status')
                    ->setParameter('status', $status);
            }

            // Aplicar filtro por prioridade
            if ($priority = $request->query->get('priority')) {
                $queryBuilder->andWhere('s.priority = :priority')
                    ->setParameter('priority', $priority);
            }

           
            // Ordenação padrão
          $queryBuilder->orderBy('s.date_create', 'DESC');
 
          $totalBuilder = clone $queryBuilder;
            // Conta total de registros
            $total =  count($totalBuilder->getQuery()->getResult());

            // Aplica paginação
            $queryBuilder->setFirstResult(($page - 1) * $perPage)
                ->setMaxResults($perPage);

            $services = $queryBuilder->getQuery()->getResult();

            $response = array_map(function ($service) {
                return [
                    'id' => $service->getId(),
                    'title' => $service->getTitle(),
                    'description' => $service->getDescription(),
                    'status' => $service->getStatus(),
                    'priority' => $service->getPriority(),
                    'requester' => [
                        'id' => $service->getRequester()?->getId(),
                        'name' => $service->getRequester()?->getName(),
                        'email' => $service->getRequester()?->getEmail(),
                    ],
                    'sector' => [
                        'id' => $service->getSector()?->getId(),
                        'name' => $service->getSector()?->getName(),
                    ],
                    'dates' => [
                        'created' => $service->getDateCreate()?->format('Y-m-d H:i:s'),
                        'updated' => $service->getDateUpdate()?->format('Y-m-d H:i:s'),
                        'concluded' => $service->getDateConclusion()?->format('Y-m-d H:i:s'),
                    ],
                ];
            }, $services);

            return new JsonResponse([
                'success' => true,
                'data' => $response,
                'meta' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => (int)$page,
                    'last_page' => ceil($total / $perPage)
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error fetching services: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/status', methods: ['PUT'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        try {
            // Decodifica o corpo da requisição
            $data = json_decode($request->getContent(), true);

            // Validação básica dos dados recebidos
            if (!isset($data['status']) || !isset($data['comment'])) {
                throw new BadRequestException('Status and comment are required');
            }

            // Busca o serviço no ServiceManager
            $service = $this->serviceManager->findById($id);

            if (!$service) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Service not found'
                ], 404);
            }

            // Atualiza o status do serviço
            $this->serviceManager->updateServiceStatus(
                service: $service,
                newStatus: $data['status'],
                comment: $data['comment']
            );

            // Prepara a resposta com os dados atualizados
            return new JsonResponse([
                'success' => true,
                'data' => [
                    'id' => $service->getId(),
                    'title' => $service->getTitle(),
                    'status' => $service->getStatus(),
                    'dates' => [
                        'created' => $service->getDateCreate()->format('Y-m-d H:i:s'),
                        'updated' => $service->getDateUpdate()->format('Y-m-d H:i:s'),
                        'concluded' => $service->getDateConclusion()?->format('Y-m-d H:i:s')
                    ],
                    'responsible' => [
                        'id' => $service->getReponsible()?->getId(),
                        'name' => $service->getReponsible()?->getName()
                    ],
                    'history' => array_map(function ($history) {
                        return [
                            'date' => $history->getDateHistory()->format('Y-m-d H:i:s'),
                            'status_prev' => $history->getStatusPrev(),
                            'status_post' => $history->getStatusPost(),
                            'comment' => $history->getComment(),
                            'responsible' => [
                                'id' => $history->getResponsible()?->getId(),
                                'name' => $history->getResponsible()?->getName()
                            ]
                        ];
                    }, $service->getHistories()->toArray())
                ]
            ]);
        } catch (BadRequestException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }


    // Em ServiceController.php

    #[Route('/{id}/transfer', methods: ['PUT'])]
    public function transferToAttendant(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validação dos dados
            if (!isset($data['attendant_id']) || !isset($data['comment'])) {
                throw new BadRequestException('Attendant ID and comment are required');
            }

            // Buscar o serviço
            $service = $this->serviceManager->findById($id);

            if (!$service) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Service not found'
                ], 404);
            }

            // Transferir o ticket
            $this->serviceManager->transferTicket(
                service: $service,
                newAttendantId: $data['attendant_id'],
                comment: $data['comment']
            );

            // Preparar resposta
            return new JsonResponse([
                'success' => true,
                'data' => [
                    'id' => $service->getId(),
                    'title' => $service->getTitle(),
                    'status' => $service->getStatus(),
                    'responsible' => [
                        'id' => $service->getReponsible()?->getId(),
                        'name' => $service->getReponsible()?->getName(),
                        'function' => $service->getReponsible()?->getFunction()
                    ],
                    'dates' => [
                        'created' => $service->getDateCreate()->format('Y-m-d H:i:s'),
                        'updated' => $service->getDateUpdate()->format('Y-m-d H:i:s')
                    ],
                    'history' => array_map(function ($history) {
                        return [
                            'date' => $history->getDateHistory()->format('Y-m-d H:i:s'),
                            'status_prev' => $history->getStatusPrev(),
                            'status_post' => $history->getStatusPost(),
                            'comment' => $history->getComment(),
                            'responsible' => [
                                'id' => $history->getResponsible()?->getId(),
                                'name' => $history->getResponsible()?->getName()
                            ]
                        ];
                    }, $service->getHistories()->toArray())
                ]
            ]);
        } catch (BadRequestException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }




    // In ServiceManager.php

    public function getServicesByRequester(int $userId): array
    {
        $serviceRepository = $this->entityManager->getRepository(Service::class);

        $queryBuilder = $serviceRepository->createQueryBuilder('s')
            ->leftJoin('s.sector', 'sect')
            ->leftJoin('s.requester', 'u')
            ->leftJoin('s.reponsible', 'a')
            ->select('s', 'sect', 'u', 'a')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('s.date_create', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    // Then modify the ServiceController.php to use this new method:

    #[Route('/my-tickets', methods: ['GET'])]
    public function listUserTickets(Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $filters = [
                'title' => $request->query->get('title'),
                'status' => $request->query->get('status'),
                'priority' => $request->query->get('priority')
            ];

            // Removemos filtros vazios
            $filters = array_filter($filters, function ($value) {
                return !is_null($value) && $value !== '';
            });


            // Get pagination parameters from request
            $page = $request->query->get('page', 1);
            $perPage = $request->query->get('per_page', 10);

            $queryBuilder = $this->serviceManager->createQueryBuilderForUserTickets($user, $filters);

            // Get total items before applying pagination
            $total = count($queryBuilder->getQuery()->getResult());
            // Calcula os metadados da paginação
            $lastPage = max(1, ceil($total / $perPage));
            $currentPage = min($page, $lastPage); // Garante que não ultrapasse o número de páginas
            $offset = ($currentPage - 1) * $perPage;
            // Apply pagination
            $queryBuilder->setFirstResult(($page - 1) * $perPage)
                ->setMaxResults($perPage);

            $services = $queryBuilder->getQuery()->getResult();

            // Transform the services into a format suitable for JSON response
            $response = array_map(function ($service) {
                return [
                    'id' => $service->getId(),
                    'title' => $service->getTitle(),
                    'description' => $service->getDescription(),
                    'status' => $service->getStatus(),
                    'priority' => $service->getPriority(),
                    'sector' => [
                        'id' => $service->getSector()?->getId(),
                        'name' => $service->getSector()?->getName(),
                    ],
                    'requester' => [
                        'id' => $service->getRequester()?->getId(),
                        'name' => $service->getRequester()?->getName(),
                        'email' => $service->getRequester()?->getEmail(),
                    ],
                    'responsible' => [
                        'id' => $service->getReponsible()?->getId(),
                        'name' => $service->getReponsible()?->getName(),
                        'function' => $service->getReponsible()?->getFunction(),
                        'sector' => [
                            'id' => $service->getReponsible()?->getSector()?->getId(),
                            'name' => $service->getReponsible()?->getSector()?->getName()
                        ]
                    ],
                    'dates' => [
                        'created' => $service->getDateCreate()?->format('Y-m-d H:i:s'),
                        'updated' => $service->getDateUpdate()?->format('Y-m-d H:i:s'),
                        'concluded' => $service->getDateConclusion()?->format('Y-m-d H:i:s'),
                    ],
                ];
            }, $services);

            return new JsonResponse([
                'success' => true,
                'data' => $response,
                'meta' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage),

                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error fetching tickets: ' . $e->getMessage()
            ], 500);
        }
    }



    // Em ServiceController.php

    #[Route('/{id}/history', methods: ['GET'])]
    public function getServiceHistory(int $id): JsonResponse
    {
        try {
            $service = $this->serviceManager->findById($id);

            if (!$service) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Service not found'
                ], 404);
            }

            // Obtém o histórico ordenado por data
            $histories = $service->getHistories()->toArray();

            // Ordena o histórico pela data mais recente primeiro
            usort($histories, function ($a, $b) {
                return $b->getDateHistory() <=> $a->getDateHistory();
            });

            // Formata a resposta
            $response = array_map(function ($history) {
                return [
                    'id' => $history->getId(),
                    'date' => $history->getDateHistory()->format('Y-m-d H:i:s'),
                    'status_prev' => $history->getStatusPrev(),
                    'status_post' => $history->getStatusPost(),
                    'comment' => $history->getComment(),
                    'responsible' => [
                        'id' => $history->getResponsible()?->getId(),
                        'name' => $history->getResponsible()?->getName(),
                        'function' => $history->getResponsible()?->getFunction()
                    ],
                    'service' => [
                        'id' => $history->getService()->getId(),
                        'title' => $history->getService()->getTitle()
                    ]
                ];
            }, $histories);

            return new JsonResponse([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error fetching service history: ' . $e->getMessage()
            ], 500);
        }
    }
}
