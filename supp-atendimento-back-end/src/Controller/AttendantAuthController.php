<?php

// src/Controller/AttendantAuthController.php

namespace App\Controller;

use App\Entity\Attendant; // Adicione este import
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        /** @var Attendant $attendant */
        $attendant = $this->getUser();

        if (!$attendant) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'success' => true,
            'data' => [
                'attendant' => [
                    'id' => $attendant->getId(),
                    'name' => $attendant->getName(),
                    'email' => $attendant->getEmail(),
                    'function' => $attendant->getFunction(),
                    'sector' => [
                        'id' => $attendant->getSector()?->getId(),
                        'name' => $attendant->getSector()?->getName()
                    ],
                ],
                'token' => $this->jwtManager->create($attendant)
            ]
        ]);
    }

    #[Route('/logout', name: 'api_attendant_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        $this->tokenStorage->setToken(null);
        
        return new JsonResponse([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}