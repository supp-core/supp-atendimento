<?php

// src/Controller/AttendantAuthController.php

namespace App\Controller;

use App\Service\AttendantAuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AttendantAuthController extends AbstractController
{
    public function __construct(
        private AttendantAuthService $authService,
        private JWTTokenManagerInterface $jwtManager
    ) {}

    #[Route('/api/attendant/login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['email']) || !isset($data['password'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Email e senha são obrigatórios'
                ], 400);
            }

            // Autentica o atendente
            $attendant = $this->authService->authenticate(
                $data['email'],
                $data['password']
            );

            // Gera o token JWT usando o User associado
            $token = $this->jwtManager->create($attendant->getUser());

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'attendant' => [
                        'id' => $attendant->getId(),
                        'name' => $attendant->getName(),
                        'function' => $attendant->getFunction(),
                        'sector' => [
                            'id' => $attendant->getSector()?->getId(),
                            'name' => $attendant->getSector()?->getName()
                        ]
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }
}