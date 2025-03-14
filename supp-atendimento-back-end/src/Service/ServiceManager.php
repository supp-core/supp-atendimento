<?php

namespace App\Service;

use App\Entity\Service;
use App\Entity\Attendant;
use App\Entity\ServiceHistory;
use App\Entity\ServiceAttachment;
use App\Entity\Sector;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;  // Adicione este import
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Twig\Environment;

class ServiceManager
{
    private const VALID_STATUS = ['NEW', 'OPEN', 'IN_PROGRESS', 'RESOLVED', 'CONCLUDED', 'NOVO'];
    private string $uploadDir;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private AttachmentManager $attachmentManager,
        private MailerInterface $mailer,
        string $uploadDir = null
    ) {

        $this->uploadDir = $uploadDir ?? dirname(__DIR__, 2) . '/public/uploads/attachments';
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function findById(int $id): ?Service
    {
        return $this->entityManager->getRepository(Service::class)->find($id);
    }


    public function createService(array $data,  bool $admin = false): Service
    {


        echo '<pre>';
        print_r($data);
        echo '</pre>';
      //  die('parou aqui');
        // Validar dados obrigatórios
        if (empty($data['title']) || empty($data['description']) || empty($data['sector_id']) || empty($data['requester_id'])) {
            throw new BadRequestException('Missing required fields');
        }

        $createdByAdmin = !empty($data['created_by_admin']);
        $requester = null;
   
        if ($createdByAdmin) {
            // Se o ticket está sendo criado por um admin, verificamos o ID do requisitante
            if (empty($data['requester_id'])) {
                throw new BadRequestException('Requester ID is required when creating ticket as admin');
            }

            $requester = $this->entityManager->getRepository(User::class)->find($data['requester_id']);
            if (!$requester) {
                throw new BadRequestException('Invalid requester');
            }
        } else {
            // Caso padrão: ticket criado pelo próprio usuário
            $requester = $data['requester_id'];
        }

        // Buscar entidades relacionadas
        $sector = $this->entityManager->getRepository(Sector::class)->find($data['sector_id']);
        //   $requester = $this->entityManager->getRepository(User::class)->find($data['requester_id']);
        // $requester = $data['requester_id']; 

    
            if (!$sector || !$requester) {
                throw new BadRequestException('Invalid sector or requester');
            }
      

        $service = new Service();
        $service->setTitle($data['title']);
        $service->setDescription($data['description']);
        $service->setSector($sector);
        $service->setRequester($requester);
        $service->setStatus('NOVO');
        $service->setDateCreate(new DateTime());
        $priority = $data['priority'] ?? Service::PRIORITY_NORMAL;
        $service->setPriority($priority);



        // Configurações para tickets criados por admin
        if ($createdByAdmin && !empty($data['created_by_admin_id'])) {
            $adminAttendant = $this->entityManager->getRepository(Attendant::class)->find($data['created_by_admin_id']);

            if ($adminAttendant) {
                $service->setCreatedByAdmin(true);
                $service->setCreatedByAdminAttendant($adminAttendant);
                //$service->setReponsible($adminAttendant);
            }
        }

        // Processar anexos
        if (!empty($data['files'])) {


            foreach ($data['files'] as $uploadedFile) {
                if (!$uploadedFile) {
                    continue;
                }

                if ($uploadedFile instanceof UploadedFile) {
                    try {
                        if ($uploadedFile->isValid()  && $this->attachmentManager->validateFile($uploadedFile)) {


                            if ($this->attachmentManager->validateFile($uploadedFile)) {

                                $extension = $uploadedFile->getClientOriginalExtension();
                                $mimeMap = [
                                    'pdf' => 'application/pdf',
                                    'doc' => 'application/msword',
                                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    'jpg' => 'image/jpeg',
                                    'jpeg' => 'image/jpeg',
                                    'png' => 'image/png',
                                    'gif' => 'image/gif',
                                    // Adicione outros tipos conforme necessário
                                ];

                                $fileSize = $uploadedFile->getSize();
                                // Garantir que o tamanho é um inteiro válido

                                if (is_numeric($fileSize)) {
                                    $fileSize = (int)$fileSize;
                                } else {
                                    $fileSize = 0; // Valor padrão em caso de erro
                                }

                                $filename = $this->attachmentManager->uploadFile($uploadedFile);
                                $extension = $uploadedFile->getClientOriginalExtension();
                                $attachment = new ServiceAttachment();
                                $attachment->setService($service);


                                $attachment->setFilename($filename);

                                $attachment->setOriginalFilename($uploadedFile->getClientOriginalName());
                                $mimeType = $mimeMap[strtolower($extension)] ?? 'application/octet-stream';

                                $attachment->setMimeType($mimeType);

                                $attachment->setFileSize($fileSize);

                                $this->entityManager->persist($attachment);

                                $service->addAttachment($attachment);


                                $attachmentData = [
                                    'filename' => $filename,
                                    'originalFilename' => $uploadedFile->getClientOriginalName(),
                                    'mimeType' => $uploadedFile->getMimeType(),
                                    'fileSize' => $uploadedFile->getSize(),
                                    'uploadedFile' => [
                                        'path' => $uploadedFile->getPathname(),
                                        'exists' => file_exists($uploadedFile->getPathname()),
                                        'readable' => is_readable($uploadedFile->getPathname()),
                                        'error' => $uploadedFile->getError(),
                                        // 'errorMessage' => $this->getUploadErrorMessage($uploadedFile->getError())
                                    ]
                                ];

                                echo  $attachmentData;
                            }
                        } else {
                            error_log('Upload inválido: ' . $uploadedFile->getErrorMessage());
                        }
                    } catch (\Exception $e) {
                        error_log('Erro ao processar arquivo: ' . $e->getMessage());
                        // Continue sem interromper o processo
                    }
                }
            }
        }


        // Persistir o serviço
        $this->entityManager->persist($service);

        // Criar histórico inicial
        $history = new ServiceHistory();
        $history->setService($service);
        $history->setStatusPrev('Nenhum');
        $history->setStatusPost('NEW');
        $history->setDateHistory(new DateTime());

        if ($createdByAdmin && $service->getCreatedByAdminAttendant()) {
            $history->setComment('Ticket criado pelo atendente: ' . $service->getCreatedByAdminAttendant()->getName());
            $history->setResponsible($service->getCreatedByAdminAttendant());
        } else {
            $history->setComment('Ticket criado pelo usuário');
        }

        $this->entityManager->persist($history);
        $this->entityManager->flush();

        return $service;
    }

    public function updateServiceStatus(Service $service, string $newStatus, string $comment, array $files = [], ?Attendant $attendant = null): void
    {

        //   die('parou uuuuuuuuuuuuuuuuu');

        if (!in_array(strtoupper($newStatus), self::VALID_STATUS)) {
            throw new BadRequestException('Invalid status provided');
        }

        $currentStatus = $service->getStatus();

        if (!empty($files)) {
            foreach ($files as $file) {
                if ($this->attachmentManager->validateFile($file)) {
                    $filename = $this->attachmentManager->uploadFile($file);

                    $attachment = new ServiceAttachment();
                    $attachment->setService($service);
                    $attachment->setFilename($filename);
                    $attachment->setOriginalFilename($file->getClientOriginalName());
                    $attachment->setMimeType($file->getMimeType());
                    $attachment->setFileSize($file->getSize());

                    $this->entityManager->persist($attachment);
                    $service->addAttachment($attachment);
                }
            }
        }
        // Criar histórico da alteração

        // $this->createServiceHistory($service, $currentStatus, $newStatus, $comment);
        $this->createServiceHistory($service, $currentStatus, $newStatus, $comment, $attendant);


        if ($attendant && !$service->getReponsible()) {
            $service->setReponsible($attendant);
        }
        // Atualizar o serviço
        $service->setStatus($newStatus);
        $service->setDateUpdate(new DateTime());

        // Se status for CONCLUDED, atualizar data de conclusão e enviar email
        if (strtoupper($newStatus) === 'CONCLUDED') {
            $service->setDateConclusion(new DateTime());
            $this->sendStatusUpdateEmail($service);
        }

        $this->entityManager->flush();
    }

    private function findAdminAttendant(): ?Attendant
    {
        return $this->entityManager->getRepository(Attendant::class)
            ->createQueryBuilder('a')
            ->where('a.function = :function')
            ->andWhere('a.status = :status')
            ->setParameter('function', 'Admin')
            ->setParameter('status', 'ACTIVE')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function createServiceHistory(Service $service, string $prevStatus, string $newStatus, string $comment, ?Attendant $attendant = null): void
    {


        $history = new ServiceHistory();
        $history->setService($service);
        $history->setStatusPrev($prevStatus);
        $history->setStatusPost($newStatus);
        $history->setComment($comment);
        $history->setDateHistory(new DateTime());


        if ($attendant) {

            $history->setResponsible($attendant);

            if (!$service->getReponsible()) {


                $service->setReponsible($attendant);
            }
        }
        //    die('chega de palhaçada');

        $this->entityManager->persist($history);
    }

    private function sendStatusUpdateEmail(Service $service): void
    {
        $requester = $service->getRequester();

        if (!$requester || !$requester->getEmail()) {
            return;
        }

        try {
            // Criamos o conteúdo HTML diretamente
            $htmlContent = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .header { background-color: #4a90e2; color: white; padding: 20px; }
                        .content { padding: 20px; }
                        .ticket-info { background-color: #f9f9f9; padding: 15px; margin: 10px 0; }
                    </style>
                </head>
                <body>
                    <div class='header'>
                        <h1>Chamado Concluído</h1>
                    </div>
                    <div class='content'>
                        <p>Olá {$requester->getName()},</p>
                        
                        <p>Seu chamado foi concluído com sucesso!</p>
                        
                        <div class='ticket-info'>
                            <p><strong>Número do Chamado:</strong> #{$service->getId()}</p>
                            <p><strong>Título:</strong> {$service->getTitle()}</p>
                            <p><strong>Setor:</strong> {$service->getSector()->getName()}</p>
                            <p><strong>Data de Abertura:</strong> {$service->getDateCreate()->format('d/m/Y H:i:s')}</p>
                            <p><strong>Data de Conclusão:</strong> {$service->getDateConclusion()->format('d/m/Y H:i:s')}</p>
                            <p><strong>Atendente Responsável:</strong> " .
                ($service->getReponsible() ? $service->getReponsible()->getName() : 'Não atribuído') .
                "</p>
                        </div>
                        
                        <p>Se você precisar de qualquer assistência adicional, não hesite em abrir um novo chamado.</p>
                        
                        <p>Atenciosamente,<br>Supp - Equipe de Suporte</p>
                    </div>
                </body>
                </html>
            ";

            $email = (new Email())
                ->from('helpdesk@seu-dominio.com')  // Altere para seu email configurado
                ->to($requester->getEmail())
                ->subject('Chamado #' . $service->getId() . ' - Concluído')
                ->html($htmlContent);

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Registra o erro mas não interrompe o fluxo principal
            error_log('Erro ao enviar email de conclusão do chamado #' .
                $service->getId() . ': ' . $e->getMessage());
        }
    }


    // In ServiceManager.php
    public function getServicesBySector(int $sectorId): array
    {
        // Get the repository for Service entity
        $serviceRepository = $this->entityManager->getRepository(Service::class);

        // Create a query builder to fetch services
        $queryBuilder = $serviceRepository->createQueryBuilder('s')
            // Join with sector
            ->join('s.sector', 'sect')
            // Join with requester to get user information
            ->leftJoin('s.requester', 'u')
            // Join with responsible attendant
            ->leftJoin('s.reponsible', 'a')
            // Select necessary fields
            ->select('s', 'sect', 'u', 'a')
            // Filter by sector id
            ->where('sect.id = :sectorId')
            ->setParameter('sectorId', $sectorId)
            // Order by creation date, newest first
            ->orderBy('s.date_create', 'DESC');

        // Execute query and return results
        return $queryBuilder->getQuery()->getResult();
    }

    public function getServicesByAttendant(int $attendantId): array
    {
        $attendant = $this->entityManager->getRepository(Attendant::class)->find($attendantId);

        if (!$attendant) {
            throw new BadRequestException('Attendant not found');
        }

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('s', 'sect', 'u', 'a')
            ->from(Service::class, 's')
            ->leftJoin('s.sector', 'sect')
            ->leftJoin('s.requester', 'u')
            ->leftJoin('s.reponsible', 'a');

        // Se for admin, retorna todos os tickets
        if ($attendant->getFunction() === 'Admin') {

            $queryBuilder->where('s.reponsible IS NULL OR s.reponsible = :attendantId')
                ->setParameter('attendantId', $attendantId);
        } else {

            // Se não for admin, retorna apenas os tickets atribuídos ao atendente
            $queryBuilder->where('s.reponsible = :attendantId')
                ->setParameter('attendantId', $attendantId);
        }

        $queryBuilder->orderBy('CASE s.priority 
        WHEN \'URGENTE\' THEN 0 
        WHEN \'ALTA\' THEN 1 
        WHEN \'NORMAL\' THEN 2 
        WHEN \'BAIXA\' THEN 3 
        ELSE 4 END', 'ASC')
            ->addOrderBy('s.date_create', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }


    // Em ServiceManager.php

    public function transferTicket(Service $service, int $newAttendantId, string $comment): void
    {
        // Buscar novo atendente
        $newAttendant = $this->entityManager->getRepository(Attendant::class)->find($newAttendantId);

        if (!$newAttendant) {
            throw new BadRequestException('New attendant not found');
        }

        // Registrar atendente anterior
        $previousAttendant = $service->getReponsible();

        // Atualizar o atendente responsável
        $service->setReponsible($newAttendant);
        $service->setDateUpdate(new DateTime());

        // Criar histórico da transferência
        $history = new ServiceHistory();
        $history->setService($service);
        $history->setStatusPrev($service->getStatus());
        $history->setStatusPost($service->getStatus());
        $history->setComment($comment);
        $history->setDateHistory(new DateTime());
        $history->setResponsible($previousAttendant);

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }

    public function getAllServices(): array
    {
        $serviceRepository = $this->entityManager->getRepository(Service::class);

        $queryBuilder = $serviceRepository->createQueryBuilder('s')
            // Join with sector
            ->leftJoin('s.sector', 'sect')
            // Join with requester to get user information
            ->leftJoin('s.requester', 'u')
            // Join with responsible attendant
            ->leftJoin('s.reponsible', 'a')
            // Select necessary fields
            ->select('s', 'sect', 'u', 'a')
            // Order by creation date, newest first
            ->orderBy('s.date_create', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }


    public function getServicesByRequester($user, int $page = 1, int $limit = 5, array $filters = []): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('s', 'sect', 'u', 'a', 'h')
            ->from(Service::class, 's')
            ->leftJoin('s.sector', 'sect')
            ->leftJoin('s.requester', 'u')
            ->leftJoin('s.reponsible', 'a')
            ->leftJoin('s.histories', 'h')
            ->where('s.requester = :userId')
            ->setParameter('userId', $user);

        // Aplicar filtros
        if (!empty($filters['title'])) {
            $queryBuilder->andWhere('s.title LIKE :title')
                ->setParameter('title', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['status'])) {
            $queryBuilder->andWhere('s.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $queryBuilder->andWhere('s.priority = :priority')
                ->setParameter('priority', $filters['priority']);
        }

        // Ordenação
        $queryBuilder->orderBy('s.date_create', 'DESC');

        // Calcular total de itens para paginação
        $totalItemsQuery = clone $queryBuilder;
        $totalItems = count($totalItemsQuery->getQuery()->getResult());

        // Aplicar paginação
        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return [
            'items' => $queryBuilder->getQuery()->getResult(),
            'total_items' => $totalItems,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($totalItems / $limit)
        ];
    }


    public function createQueryBuilderForUserTickets($user, array $filters = [])
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('s', 'sect', 'u', 'a')
            ->from(Service::class, 's')
            ->leftJoin('s.sector', 'sect')
            ->leftJoin('s.requester', 'u')
            ->leftJoin('s.reponsible', 'a')
            ->where('s.requester = :user')
            ->setParameter('user', $user);

        // Aplicamos os filtros de forma dinâmica
        if (!empty($filters['title'])) {
            $queryBuilder->andWhere('s.title LIKE :title')
                ->setParameter('title', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['status'])) {
            $queryBuilder->andWhere('s.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $queryBuilder->andWhere('s.priority = :priority')
                ->setParameter('priority', $filters['priority']);
        }

        // Adicionar filtros de data
        if (!empty($filters['start_date'])) {
            $queryBuilder->andWhere('s.date_create >= :start_date')
                ->setParameter('start_date', new \DateTime($filters['start_date'] . ' 00:00:00'));
        }

        if (!empty($filters['end_date'])) {
            $queryBuilder->andWhere('s.date_create <= :end_date')
                ->setParameter('end_date', new \DateTime($filters['end_date'] . ' 23:59:59'));
        }


        // Mantemos a ordenação padrão
        $queryBuilder->orderBy('s.date_create', 'DESC');

        return $queryBuilder;
    }

    // Em ServiceManager.php

    public function getServiceHistory(Service $service): array
    {
        return $this->entityManager->getRepository(ServiceHistory::class)
            ->createQueryBuilder('sh')
            ->where('sh.service = :service')
            ->setParameter('service', $service)
            ->orderBy('sh.date_history', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
