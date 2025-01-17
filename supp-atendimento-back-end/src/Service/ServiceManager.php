<?php

namespace App\Service;

use App\Entity\Service;
use App\Entity\Attendant;
use App\Entity\ServiceHistory;
use App\Entity\Sector;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;  // Adicione este import

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Twig\Environment;

class ServiceManager
{
    private const VALID_STATUS = ['NEW', 'OPEN', 'IN_PROGRESS', 'RESOLVED', 'CONCLUDED'];


    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
    ) {}

    public function findById(int $id): ?Service
    {
        return $this->entityManager->getRepository(Service::class)->find($id);
    }


    public function createService(array $data): Service
    {
        // Validar dados obrigatórios
        if (empty($data['title']) || empty($data['description']) || empty($data['sector_id']) || empty($data['requester_id'])) {
            throw new BadRequestException('Missing required fields');
        }

        // Buscar entidades relacionadas
        $sector = $this->entityManager->getRepository(Sector::class)->find($data['sector_id']);
        $requester = $this->entityManager->getRepository(User::class)->find($data['requester_id']);

        if (!$sector || !$requester) {
            throw new BadRequestException('Invalid sector or requester');
        }

        // Criar novo serviço
        $service = new Service();
        $service->setTitle($data['title']);
        $service->setDescription($data['description']);
        $service->setSector($sector);
        $service->setRequester($requester);
        $service->setStatus('new');
        $service->setDateCreate(new DateTime());

        // Persistir o serviço
        $this->entityManager->persist($service);

        // Criar histórico inicial
        $history = new ServiceHistory();
        $history->setService($service);
        $history->setStatusPrev('none');
        $history->setStatusPost('new');
        $history->setDateHistory(new DateTime());
        $history->setComment('Ticket created');

        $this->entityManager->persist($history);
        $this->entityManager->flush();

        return $service;
    }

    public function updateServiceStatus(Service $service, string $newStatus, string $comment): void
    {
        if (!in_array(strtoupper($newStatus), self::VALID_STATUS)) {
            throw new BadRequestException('Invalid status provided');
        }

        $currentStatus = $service->getStatus();

        // Criar histórico da alteração
        $this->createServiceHistory($service, $currentStatus, $newStatus, $comment);

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

    private function createServiceHistory(Service $service, string $prevStatus, string $newStatus, string $comment): void
    {
        $history = new ServiceHistory();
        $history->setService($service);
        $history->setStatusPrev($prevStatus);
        $history->setStatusPost($newStatus);
        $history->setComment($comment);
        $history->setDateHistory(new DateTime());

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
        $serviceRepository = $this->entityManager->getRepository(Service::class);

        $queryBuilder = $serviceRepository->createQueryBuilder('s')
            ->join('s.reponsible', 'a')
            ->leftJoin('s.requester', 'u')
            ->leftJoin('s.sector', 'sect')
            ->select('s', 'sect', 'u', 'a')
            ->where('a.id = :attendantId')
            ->setParameter('attendantId', $attendantId)
            ->orderBy('s.date_create', 'DESC');

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
}
