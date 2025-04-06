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
use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Attendant;
use App\Entity\ServiceAttachment; // Adicione esta linha para importar a classe
use Symfony\Component\HttpFoundation\BinaryFileResponse; // Também adicione esta para o download
use Symfony\Component\HttpFoundation\File\UploadedFile;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

#[Route('/api/service')]
class ServiceController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ServiceManager $serviceManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        ServiceManager $serviceManager,
        private UserPasswordHasherInterface $passwordHasher

    ) {
        $this->entityManager = $entityManager;
        $this->serviceManager = $serviceManager;
    }



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

            $startDate = $request->query->get('start_date');
            $endDate = $request->query->get('end_date');

            $filters = [
                'title' => $request->query->get('title'),
                'status' => $request->query->get('status'),
                'priority' => $request->query->get('priority'),
                'start_date' => $startDate,
                'end_date' => $endDate
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
                    'attachments' => array_map(function ($attachment) {
                        return [
                            'id' => $attachment->getId(),
                            'filename' => $attachment->getFilename(),
                            'originalFilename' => $attachment->getOriginalFilename()
                        ];
                    }, $service->getAttachments()->toArray()),
                    'description' => $service->getDescription(),
                    'status' => $service->getStatus(),
                    'priority' => $service->getPriority(),
                    'sector' => [
                        'id' => $service->getSector()?->getId(),
                        'name' => $service->getSector()?->getName(),
                    ],
                    // Adicione aqui os dados da categoria
                    'category' => $service->getCategory() ? [
                        'id' => $service->getCategory()->getId(),
                        'name' => $service->getCategory()->getName(),
                        'description' => $service->getCategory()->getDescription()
                    ] : null,
                    // Adicione aqui os dados do tipo de serviço
                    'serviceType' => $service->getServiceType() ? [
                        'id' => $service->getServiceType()->getId(),
                        'name' => $service->getServiceType()->getName(),
                        'description' => $service->getServiceType()->getDescription()
                    ] : null,
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
    // Adicione este método ao ServiceController.php
    #[Route('/{id}', methods: ['GET'])]
    public function getService(int $id): JsonResponse
    {
        try {
            $service = $this->serviceManager->findById($id);

            if (!$service) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Service not found'
                ], 404);
            }

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'id' => $service->getId(),
                    'title' => $service->getTitle(),
                    'description' => $service->getDescription(),
                    'status' => $service->getStatus(),
                    'priority' => $service->getPriority(),
                    'sector' => [
                        'id' => $service->getSector()?->getId(),
                        'name' => $service->getSector()?->getName(),
                    ],
                    // Adicione aqui os dados da categoria
                    'category' => $service->getCategory() ? [
                        'id' => $service->getCategory()->getId(),
                        'name' => $service->getCategory()->getName(),
                        'description' => $service->getCategory()->getDescription()
                    ] : null,
                    // Adicione aqui os dados do tipo de serviço
                    'serviceType' => $service->getServiceType() ? [
                        'id' => $service->getServiceType()->getId(),
                        'name' => $service->getServiceType()->getName(),
                        'description' => $service->getServiceType()->getDescription()
                    ] : null,
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
                    'attachments' => array_map(function ($attachment) {
                        return [
                            'id' => $attachment->getId(),
                            'filename' => $attachment->getFilename(),
                            'originalFilename' => $attachment->getOriginalFilename()
                        ];
                    }, $service->getAttachments()->toArray())
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error fetching service: ' . $e->getMessage()
            ], 500);
        }
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

            // Garantir que $files seja um array, mesmo que vazio
            if ($files === null) {
                $files = [];
            } else if (!is_array($files)) {
                $files = [$files];
            }


            $data = [
                'title' => $request->request->get('title'),
                'description' => $request->request->get('description'),
                'priority' => $request->request->get('priority'),
                'sector_id' => $request->request->get('sector_id'),
                'requester_id' => $user,
                'category_id' => $request->request->get('category_id'),
                'service_type_id' => $request->request->get('service_type_id'),
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
                            'originalFilename' => $attachment->getOriginalFilename(),
                            'mimeType' => $attachment->getMimeType(),
                            'fileSize' => $attachment->getFileSize()
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
            // Obter parâmetros de filtragem
            $title = $request->query->get('title');
            $requester = $request->query->get('requester');
            $status = $request->query->get('status');
            $priority = $request->query->get('priority');
            $categoryId = $request->query->get('category_id');
            $serviceTypeId = $request->query->get('service_type_id');

            // Parâmetros de paginação
            $page = $request->query->get('page', 1);
            $perPage = $request->query->get('per_page', 10);

            // Usar o método específico do ServiceManager que já verifica se é admin
            $attendant = $this->entityManager->getRepository(Attendant::class)->find($id);
            if (!$attendant) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Atendente não encontrado'
                ], 404);
            }

            // Obter serviços usando a função apropriada
            $services = $this->serviceManager->getServicesByAttendant($id);


            // Aplicar filtros manualmente
            $filteredServices = [];
            foreach ($services as $service) {
                $keepService = true;

                // Filtro por título
                if ($title && !str_contains(strtolower($service->getTitle()), strtolower($title))) {
                    $keepService = false;
                }

                // Filtro por solicitante
                if (
                    $requester && $service->getRequester() &&
                    !str_contains(strtolower($service->getRequester()->getName()), strtolower($requester))
                ) {
                    $keepService = false;
                }

                // Filtro por status
                if ($status && $service->getStatus() !== $status) {
                    $keepService = false;
                }

                // Filtro por prioridade
                if ($priority && $service->getPriority() !== $priority) {
                    $keepService = false;
                }

                // Filtro por categoria
                if ($categoryId && (!$service->getCategory() || $service->getCategory()->getId() != $categoryId)) {
                    $keepService = false;
                }

                // Filtro por tipo de serviço
                if ($serviceTypeId && (!$service->getServiceType() || $service->getServiceType()->getId() != $serviceTypeId)) {
                    $keepService = false;
                }

                if ($keepService) {
                    $filteredServices[] = $service;
                }
            }

            // Ordenar por prioridade e depois por data (mais recente primeiro)
            usort($filteredServices, function ($a, $b) {
                $priorityOrder = [
                    'URGENTE' => 0,
                    'ALTA' => 1,
                    'NORMAL' => 2,
                    'BAIXA' => 3
                ];

                $priorityA = $priorityOrder[$a->getPriority()] ?? 4;
                $priorityB = $priorityOrder[$b->getPriority()] ?? 4;

                if ($priorityA === $priorityB) {
                    // Se prioridades iguais, ordena por data (mais recente primeiro)
                    return $b->getDateCreate() <=> $a->getDateCreate();
                }

                return $priorityA <=> $priorityB;
            });

            // Aplicar paginação
            $total = count($filteredServices);
            $paginatedServices = array_slice($filteredServices, ($page - 1) * $perPage, $perPage);

            // Formatação dos dados para a resposta
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
                    'category' => $service->getCategory() ? [
                        'id' => $service->getCategory()->getId(),
                        'name' => $service->getCategory()->getName(),
                        'description' => $service->getCategory()->getDescription()
                    ] : null,
                    // Adicionando informações de tipo de serviço
                    'serviceType' => $service->getServiceType() ? [
                        'id' => $service->getServiceType()->getId(),
                        'name' => $service->getServiceType()->getName(),
                        'description' => $service->getServiceType()->getDescription()
                    ] : null,
                    'dates' => [
                        'created' => $service->getDateCreate()?->format('Y-m-d H:i:s'),
                        'updated' => $service->getDateUpdate()?->format('Y-m-d H:i:s'),
                        'concluded' => $service->getDateConclusion()?->format('Y-m-d H:i:s'),
                    ],
                ];
            }, $paginatedServices);

            return new JsonResponse([
                'success' => true,
                'data' => $response,
                'meta' => [
                    'total' => $total,
                    'per_page' => (int)$perPage,
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

            if (isset($data['updateData'])) {
                $updateData = $data['updateData'];
            } else {
                $updateData = $data;
            }

            // Validação básica dos dados recebidos
            if (!isset($updateData['status']) || !isset($updateData['comment'])) {
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

            // Captura category_id e service_type_id se existirem
            $categoryId = $updateData['category_id'] ?? null;
            $serviceTypeId = $updateData['service_type_id'] ?? null;



            // Obter o usuário logado
            $user = $this->getUser();

            // Encontrar o atendente associado ao usuário logado
            $attendant = null;
            if ($user) {
                // Consulta direta ao banco de dados para encontrar o atendente
                $attendant = $this->entityManager->getRepository(Attendant::class)
                    ->createQueryBuilder('a')
                    ->where('a.user = :user')
                    ->setParameter('user', $user)
                    ->getQuery()
                    ->getOneOrNullResult();
            }

            // Atualiza o status do serviço e passa o atendente responsável
            $this->serviceManager->updateServiceStatus(
                service: $service,
                newStatus: $updateData['status'],
                comment: $updateData['comment'],
                attendant: $attendant,
                categoryId: $categoryId,
                serviceTypeId: $serviceTypeId
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


    #[Route('/admin/create', methods: ['POST'])]
    public function createByAdmin(Request $request): JsonResponse
    {

        try {

            // Verificar se o usuário logado é um atendente admin
            $user = $this->getUser();

            if (!$user) {
                throw new AccessDeniedException('Usuário não autenticado');
            }

            // error_log('Usuário autenticado: ' . $user->getId() . ' - ' . $user->getEmail());


            /* if ($user instanceof User && !$user->isIsAttendant()) {
                throw new AccessDeniedException('Apenas atendentes podem criar tickets para usuários 1 ');
            }
            */
            // Obter o atendente correspondente ao usuário
            $attendant = $this->entityManager->getRepository(Attendant::class)
                ->findOneBy(['user' => $user]);

            /*
            if (!$attendant || $attendant->getFunction() !== 'Admin') {
                throw new AccessDeniedException('Apenas administradores podem criar tickets para usuários 2 ');
            }
    */
            // Preparar dados para criação do ticket
            $jsonData = json_decode($request->getContent(), true);
            $files = $request->files->get('files');

            // Filtrar elementos vazios ou inválidos do array de arquivos
            if (is_array($files)) {
                $files = array_filter($files, function ($file) {
                    return !empty($file) && $file instanceof UploadedFile && $file->isValid();
                });
            } else {
                $files = []; // Garantir que $files seja um array vazio se não existir
            }

            $title = $request->request->get('title');
            $description = $request->request->get('description');
            $priority = $request->request->get('priority', 'NORMAL');
            $sector_id = $request->request->get('sector_id');
            $requester_id = $request->request->get('requester_id');
            $created_by_admin_id = $request->request->get('created_by_admin_id');
            $category_id = $request->request->get('category_id');
            $service_type_id = $request->request->get('service_type_id');

            $data = [
                'title' => $title,
                'description' => $description,
                'priority' => $priority,
                'sector_id' => $sector_id,
                'requester_id' => $requester_id, // Usar o ID do usuário solicitante
                'category_id' => $category_id,
                'service_type_id' => $service_type_id,
                'created_by_admin' => true,
                'created_by_admin_id' => $created_by_admin_id, // ID do atendente admin
                'files' => $files
            ];


            // Criar o serviço
            $service = $this->serviceManager->createService($data, $admin = true);

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
                    'created_by_admin' => $service->isCreatedByAdmin(),
                    'attachments' => array_map(function ($attachment) {
                        return [
                            'id' => $attachment->getId(),
                            'filename' => $attachment->getFilename(),
                            'originalFilename' => $attachment->getOriginalFilename()
                        ];
                    }, $service->getAttachments()->toArray())
                ]
            ], 201);
        } catch (AccessDeniedException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
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
    #[Route('/attachment/{id}', methods: ['GET'])]
    public function downloadAttachment(int $id): Response
    {
        try {
            $attachment = $this->entityManager->getRepository(ServiceAttachment::class)->find($id);

            if (!$attachment) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Anexo não encontrado'
                ], 404);
            }

            $filePath = $this->getParameter('uploads_directory') . '/' . $attachment->getFilename();

            if (!file_exists($filePath)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Arquivo não encontrado no servidor'
                ], 404);
            }

            return new BinaryFileResponse($filePath, 200, [
                'Content-Type' => $attachment->getMimeType(),
                'Content-Disposition' => 'attachment; filename="' . $attachment->getOriginalFilename() . '"'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erro ao baixar anexo: ' . $e->getMessage()
            ], 500);
        }
    }
}
