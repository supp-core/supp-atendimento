<?php
// src/Service/AttendantAuthService.php

namespace App\Service;

use App\Entity\Attendant;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AttendantAuthService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function authenticate(string $email, string $password): Attendant
    {
        // Primeiro busca o usuário pelo email
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if (!$user) {
            throw new AuthenticationException('Email não encontrado.');
        }

        // Verifica se a senha está correta
        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new AuthenticationException('Senha inválida.');
        }

        // Busca o atendente associado ao usuário
        $attendant = $this->entityManager->getRepository(Attendant::class)
            ->findOneBy(['user' => $user]);

        if (!$attendant) {
            throw new AuthenticationException('Este usuário não é um atendente.');
        }

        return $attendant;
    }
}