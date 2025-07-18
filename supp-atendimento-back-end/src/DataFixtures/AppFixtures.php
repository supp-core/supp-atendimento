<?php

namespace App\DataFixtures;

use App\Entity\Attendant;
use App\Entity\Category;
use App\Entity\Sector;
use App\Entity\Service;
use App\Entity\ServiceHistory;
use App\Entity\ServiceType;
use App\Entity\User;
use DateTime;
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

    // Categorias de problemas vazias, mantendo apenas a estrutura
    private const INFRASTRUCTURE_ISSUES = [];
    private const DEVELOPMENT_ISSUES = [];
    private const ADMIN_ISSUES = [];
    private const DEVOPS_ISSUES = [];

    public function load(ObjectManager $manager): void
    {
        // Carrega os dados básicos do sistema
        $sectors = $this->loadSectors($manager);
        $this->loadCategories($manager);
        $this->loadServiceTypes($manager);
        
        // Criar e persistir o usuário para Rafael
        $userRafael = new User();
        $userRafael->setName('Rafael Assumpcao de Oliveira')
            ->setEmail('rafael.assumpcao@pbh.gov.br')
            ->setRoles(['ROLE_USER', 'ROLE_ATTENDANT', 'ROLE_ADMIN'])
            ->setIsAttendant(true);
        
        $hashedPasswordRafael = $this->passwordHasher->hashPassword(
            $userRafael,
            'teste123'
        );
        $userRafael->setPassword($hashedPasswordRafael);
        $manager->persist($userRafael);
        
        // Criar e persistir o usuário comum João
        $userJoao = new User();
        $userJoao->setName('João')
            ->setEmail('joao@gmail.com')
            ->setRoles(['ROLE_USER'])
            ->setIsAttendant(false);
        
        $hashedPasswordJoao = $this->passwordHasher->hashPassword(
            $userJoao,
            'teste123'
        );
        $userJoao->setPassword($hashedPasswordJoao);
        $manager->persist($userJoao);
        
        // Criar e persistir o atendente Rafael
        $attendant = new Attendant();
        $attendant->setName('Rafael Assumpcao de Oliveira')
            ->setFunction('Admin')
            ->setStatus('AVAILABLE')
            ->setSector($sectors[2]) // Setor Diretoria
            ->setUser($userRafael);
            
        $manager->persist($attendant);

        // Criar novos usuários atendentes
        $newUsers = [
            [
                'name' => 'Marco Ribeiro',
                'email' => 'marco.a.ribeiro@pbh.gov.br',
                'sector' => $sectors[0], // Infra
                'function' => 'Atendente'
            ],
            [
                'name' => 'Danilo Rodrigues César',
                'email' => 'danilo.cesar@pbh.gov.br',
                'sector' => $sectors[0], // Infra
                'function' => 'Atendente'
            ],
            [
                'name' => 'Danilo de Souza Lima',
                'email' => 'danilo.dlima@pbh.gov.br',
                'sector' => $sectors[1], // Dev
                'function' => 'Atendente'
            ],
            [
                'name' => 'Isaac Emanuel Santos Martins',
                'email' => 'isaac.martins@pbh.gov.br',
                'sector' => $sectors[1], // Dev
                'function' => 'Atendente'
            ],
            [
                'name' => 'Cristhian Ramos',
                'email' => 'cristhian.r@pbh.gov.br',
                'sector' => $sectors[3], // Suporte
                'function' => 'Atendente'
            ],
            [
                'name' => 'Paulo Henrique Meireles Araujo',
                'email' => 'paulohenr.araujo@edu.pbh.gov.br',
                'sector' => $sectors[3], // Suporte
                'function' => 'Atendente'
            ],
            [
                'name' => 'Larissa Evelyn',
                'email' => 'larissa.evelyn@pbh.gov.br',
                'sector' => $sectors[3], // Suporte
                'function' => 'Atendente'
            ]
        ];

        foreach ($newUsers as $userData) {
            // Criar usuário
            $user = new User();
            $user->setName($userData['name'])
                ->setEmail($userData['email'])
                ->setRoles(['ROLE_USER', 'ROLE_ATTENDANT'])
                ->setIsAttendant(true);
            
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                'teste123'
            );
            $user->setPassword($hashedPassword);
            $manager->persist($user);

            // Criar atendente
            $attendant = new Attendant();
            $attendant->setName($userData['name'])
                ->setFunction($userData['function'])
                ->setStatus('AVAILABLE')
                ->setSector($userData['sector'])
                ->setUser($user);
                
            $manager->persist($attendant);
        }
        
        // Persiste todas as alterações no banco
        $manager->flush();
    }

    private function loadSectors(ObjectManager $manager): array
    {
        $sectors = [];
        foreach (['Infra', 'Dev', 'Diretoria', 'Suporte'] as $name) {
            $sector = new Sector();
            $sector->setName($name);
            $manager->persist($sector);
            $sectors[] = $sector;
        }
        return $sectors;
    }

    private function loadCategories(ObjectManager $manager): void
    {
        $categories = ['SUPP', 'Suporte', 'Planilha', 'Solicitação de E-mail'];
        
        foreach ($categories as $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);
        }
    }

    private function loadServiceTypes(ObjectManager $manager): void
    {
        $serviceTypes = ['Triagem', 'Bug', 'Implantação', 'Solicitação', 'Manutenção', 'Instalação'];
        
        foreach ($serviceTypes as $typeName) {
            $serviceType = new ServiceType();
            $serviceType->setName($typeName);
            $manager->persist($serviceType);
        }
    }
}