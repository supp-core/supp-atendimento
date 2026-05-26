<?php

namespace App\Controller;

use App\Entity\Attendant;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/api')]
class AuthController extends AbstractController
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['email']) || !isset($data['password'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Usuário e senha são obrigatórios'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(['email' => $data['email']]);

            if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Credenciais inválidas'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $token = $this->jwtManager->create($user);

            $attendant = $user->getAttendant()
                ?? $this->entityManager->getRepository(Attendant::class)
                    ->findOneBy(['user' => $user]);

            if ($attendant) {
                return new JsonResponse([
                    'success' => true,
                    'data' => [
                        'token' => $token,
                        'type' => 'attendant',
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
            }

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'type' => 'user',
                    'user' => [
                        'id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'name' => $user->getName(),
                        'roles' => $user->getRoles()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erro interno do servidor: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
