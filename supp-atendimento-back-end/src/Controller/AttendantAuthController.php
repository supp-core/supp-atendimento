<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/attendant')]
class AttendantAuthController extends AbstractController
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private TokenStorageInterface $tokenStorage
    ) {}

    #[Route('/login', name: 'api_attendant_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Esta função pode ficar vazia porque o processo de autenticação 
        // e formatação da resposta é tratado pelo sistema de autenticação 
        // do Symfony e nosso Event Listener
        return new JsonResponse([
            'message' => 'Use JSON credentials to login'
        ], Response::HTTP_UNAUTHORIZED);
    }

    #[Route('/logout', name: 'api_attendant_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        $this->tokenStorage->setToken(null);
        
        return new JsonResponse([
            'success' => true,
            'message' => 'Logout realizado com sucesso'
        ]);
    }
}