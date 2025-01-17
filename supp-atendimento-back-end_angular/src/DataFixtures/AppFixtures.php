<?php

namespace App\DataFixtures;

use App\Entity\Attendant;
use App\Entity\Sector;
use App\Entity\Service;
use App\Entity\ServiceHistory;
use App\Entity\User;
use DateTime;
use DateInterval;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    // Definição dos status possíveis do sistema
    private const SERVICE_STATUSES = ['NEW', 'OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'];
    private const ATTENDANT_STATUSES = ['AVAILABLE', 'BUSY', 'OFFLINE'];
    
    // Categorias de problemas de infraestrutura com títulos concisos
    private const INFRASTRUCTURE_ISSUES = [
        'Rede' => [
            'Rede fora do ar',
            'Internet lenta',
            'VPN com falha',
            'WiFi instável',
            'Impressora off'
        ],
        'Hardware' => [
            'PC não liga',
            'Monitor com defeito',
            'Teclado com erro',
            'Mouse travado',
            'Scanner com erro'
        ],
        'Servidor' => [
            'Servidor lento',
            'Backup falhou',
            'Espaço baixo',
            'CPU alta',
            'Email fora'
        ]
    ];
    
    // Categorias de problemas de desenvolvimento
    private const DEVELOPMENT_ISSUES = [
        'Bugs' => [
            'Erro no login',
            'Form não salva',
            'Relatório lento',
            'Bug no mobile',
            'Sessão expira'
        ],
        'Features' => [
            'Exportar PDF',
            'Modo escuro',
            'Edição em lote',
            'Notificações',
            'Login SSO'
        ],
        'Performance' => [
            'Query lenta',
            'Memory leak',
            'API lenta',
            'Cache erro',
            'Página lenta'
        ]
    ];

    public function load(ObjectManager $manager): void
    {
        // Carrega os dados básicos do sistema
        $sectors = $this->loadSectors($manager);
        $users = $this->loadUsers($manager);
        $attendants = $this->loadAttendants($manager, $sectors);
        
        // Carrega os chamados e seus históricos
        $services = $this->createServices($manager, $sectors, $users, $attendants);
        $this->loadServiceHistory($manager, $services, $attendants);
        
        // Persiste todas as alterações no banco
        $manager->flush();
    }

    private function loadSectors(ObjectManager $manager): array
    {
        $sectors = [];
        foreach (['Infra', 'Dev'] as $name) {
            $sector = new Sector();
            $sector->setName($name);
            $manager->persist($sector);
            $sectors[] = $sector;
        }
        return $sectors;
    }

    private function loadUsers(ObjectManager $manager): array
    {
        $users = [];
        $userData = [
            ['João Silva', 'joao@teste.com', ['ROLE_USER']],
            ['Maria Santos', 'maria@teste.com', ['ROLE_USER']],
            ['Pedro Lima', 'pedro@teste.com', ['ROLE_USER']],
            ['Ana Costa', 'ana@teste.com', ['ROLE_USER']],
            ['Carlos Dias', 'carlos@teste.com', ['ROLE_ADMIN']],
            ['Lucia Ramos', 'lucia@teste.com', ['ROLE_USER']],
            ['Paulo Melo', 'paulo@teste.com', ['ROLE_USER']],
            ['Rita Gomes', 'rita@teste.com', ['ROLE_ADMIN']]
        ];

        foreach ($userData as [$name, $email,$roles]) {
            $user = new User();
            $user->setName($name)
                 ->setEmail($email)
                 ->setRoles($roles)
                 ->setPassword('senha123');
                 $hashedPassword = $this->passwordHasher->hashPassword(
                    $user,
                    'senha123'
                );
                $user->setPassword($hashedPassword);
                
                $manager->persist($user);
                $users[] = $user;
        }

        return $users;
    }

    private function loadAttendants(ObjectManager $manager, array $sectors): array
    {
        $attendants = [];
        $attendantData = [
            ['Alex Silva', 'Suporte N1', 0, 'alex.silva@supp.com'],
            ['Bia Tech', 'Admin', 0, 'bia.tech@supp.com'],
            ['Carlos TI', 'Suporte N2', 0, 'carlos.ti@supp.com'],
            ['Diana Inf', 'Redes', 0, 'diana.inf@supp.com'],
            ['Edu Dev', 'Frontend', 1, 'edu.dev@supp.com'],
            ['Fabi Sys', 'Backend', 1, 'fabi.sys@supp.com'],
            ['Gil Pro', 'FullStack', 1, 'gil.pro@supp.com'],
            ['Hugo Dev', 'DevOps', 1, 'hugo.dev@supp.com']
        ];

        foreach ($attendantData as [$name, $function, $sectorIndex, $email]) {
            $attendant = new Attendant();
            $attendant->setName($name)
                ->setFunction($function)
                ->setStatus(self::ATTENDANT_STATUSES[array_rand(self::ATTENDANT_STATUSES)])
                ->setSector($sectors[$sectorIndex])
                ->setEmail($email)
                ->setPassword($this->passwordHasher->hashPassword($attendant, '123456'))
                ->setRoles(['ROLE_ATTENDANT']);

            $manager->persist($attendant);
            $attendants[] = $attendant;
        }

        return $attendants;
    }

    
    private function createServices(
        ObjectManager $manager,
        array $sectors,
        array $users,
        array $attendants
    ): array {
        $services = [];
        $baseDate = new DateTime('2024-01-01');
        
        // Cria chamados para o setor de infraestrutura
        foreach (self::INFRASTRUCTURE_ISSUES as $category => $issues) {
            foreach ($issues as $issue) {
                $service = $this->createServiceTicket(
                    $sectors[0], // Setor de Infra
                    $issue,
                    $this->generateDescription($issue),
                    $users,
                    array_slice($attendants, 0, 4),
                    $baseDate,
                    $manager
                );
                $services[] = $service;
            }
        }
        
        // Cria chamados para o setor de desenvolvimento
        foreach (self::DEVELOPMENT_ISSUES as $category => $issues) {
            foreach ($issues as $issue) {
                $service = $this->createServiceTicket(
                    $sectors[1], // Setor de Dev
                    $issue,
                    $this->generateDescription($issue),
                    $users,
                    array_slice($attendants, 4, 4),
                    $baseDate,
                    $manager
                );
                $services[] = $service;
            }
        }

        return $services;
    }

    private function createServiceTicket(
        Sector $sector,
        string $title,
        string $description,
        array $users,
        array $sectorAttendants,
        DateTime $baseDate,
        ObjectManager $manager
    ): Service {
        $service = new Service();
        $status = self::SERVICE_STATUSES[array_rand(self::SERVICE_STATUSES)];
        $dateCreate = clone $baseDate;
        $dateCreate->add(new DateInterval('P' . rand(0, 30) . 'D'));
        
        $service->setTitle($title)
               ->setDescription($description)
               ->setSector($sector)
               ->setStatus($status)
               ->setRequester($users[array_rand($users)])
               ->setDateCreate($dateCreate)
               ->setDateUpdate($dateCreate);

        // Atribui um atendente se o status não for NEW
        if ($status !== 'NEW') {
            $service->setReponsible($sectorAttendants[array_rand($sectorAttendants)]);
            $service->setDateUpdate(clone $dateCreate->add(new DateInterval('P' . rand(1, 5) . 'D')));
        }

        // Define data de conclusão para chamados resolvidos ou fechados
        if (in_array($status, ['RESOLVED', 'CLOSED'])) {
            $dateConclusion = clone $service->getDateUpdate();
            $service->setDateConclusion($dateConclusion->add(new DateInterval('P' . rand(1, 3) . 'D')));
        }

        $manager->persist($service);
        return $service;
    }

    private function generateDescription(string $issue): string
    {
        $priority = ['Alta', 'Média', 'Baixa'][array_rand([0, 1, 2])];
        return sprintf(
            'Prioridade %s: %s. %s',
            $priority,
            $issue,
            $this->getImpactDescription($priority)
        );
    }

    private function getImpactDescription(string $priority): string
    {
        return match($priority) {
            'Alta' => 'Afeta múltiplos usuários.',
            'Média' => 'Tem solução temporária.',
            'Baixa' => 'Não é crítico.',
        };
    }

    private function loadServiceHistory(
        ObjectManager $manager,
        array $services,
        array $attendants
    ): void {
        $statusComments = [
            'OPEN' => 'Em análise',
            'IN_PROGRESS' => 'Em andamento',
            'RESOLVED' => 'Solução aplicada',
            'CLOSED' => 'Validado'
        ];
    
        foreach ($services as $service) {
            if ($service->getStatus() !== 'NEW') {
                $history = new ServiceHistory();
                $history->setStatusPrev('NEW')
                       ->setStatusPost($service->getStatus())
                       ->setDateHistory($service->getDateUpdate())
                       ->setComment($statusComments[$service->getStatus()] ?? 'Status atualizado')
                       ->setResponsible($service->getReponsible())
                       ->setService($service);  // Definindo a relação correta
                
                $manager->persist($history);
            }
        }
    }
}