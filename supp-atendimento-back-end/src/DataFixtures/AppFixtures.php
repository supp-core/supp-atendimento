<?php

namespace App\DataFixtures;

use App\Entity\Attendant;
use App\Entity\Sector;
use App\Entity\Service;
use App\Entity\ServiceHistory;
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
        
        // Criar e persistir o usuário para Rafael
        $user = new User();
        $user->setName('Rafael Assumpcao de Oliveira')
            ->setEmail('rafael.assumpcao@pbh.gov.br')
            ->setRoles(['ROLE_USER', 'ROLE_ATTENDANT', 'ROLE_ADMIN'])
            ->setIsAttendant(true);
        
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'teste123'
        );
        $user->setPassword($hashedPassword);
        $manager->persist($user);
        
        // Criar e persistir o atendente Rafael
        $attendant = new Attendant();
        $attendant->setName('Rafael Assumpcao de Oliveira')
            ->setFunction('Admin')
            ->setStatus('AVAILABLE')
            ->setSector($sectors[2]) // Setor Admin
            ->setUser($user);
            
        $manager->persist($attendant);
        
        // Persiste todas as alterações no banco
        $manager->flush();
    }

    private function loadSectors(ObjectManager $manager): array
    {
        $sectors = [];
        foreach (['Infra', 'Dev', 'Admin', 'DevOps'] as $name) {
            $sector = new Sector();
            $sector->setName($name);
            $manager->persist($sector);
            $sectors[] = $sector;
        }
        return $sectors;
    }
}