<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher

    ) {

    }

    public function createUser(array $data): User
    {
        // Validar dados obrigatórios
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            throw new BadRequestException('Missing required fields');
        }

        // Verificar se já existe um usuário com o mesmo email
        $existingUser = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $data['email']]);

        if ($existingUser) {
            throw new BadRequestException('Email already registered');
        }

        // Criar novo usuário
        $user = new User();
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        
        // Em um cenário real, você deve usar password_hash() ou um serviço específico 
        // do Symfony para hash de senha
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $data['password']
        );
        $user->setPassword($hashedPassword);

        // Persistir o usuário
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}