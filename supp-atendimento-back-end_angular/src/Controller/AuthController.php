<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api')]
class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
        private TokenStorageInterface $tokenStorage
    ) {}

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate required fields
        if (!isset($data['email'], $data['password'], $data['name'])) {
            return new JsonResponse(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse(['message' => 'User already exists'], Response::HTTP_CONFLICT);
        }

        // Create new user
        $user = new User();
        $user->setEmail($data['email']);
        $user->setName($data['name']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'User registered successfully',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName()
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
                  // Buscar o usuário pelo email
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);            
            if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Credenciais inválidas'
                ], Response::HTTP_UNAUTHORIZED);
            }
    
            // Gerar o token JWT
          //  $token = $this->jwtManager->create($user);
    
            // Estrutura correta da resposta
            return new JsonResponse([
                'success' => true,
                'data' => [
                    'token' => $this->jwtManager->create($user),
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

    #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        // Invalidate the token
        $this->tokenStorage->setToken(null);

        return new JsonResponse(['message' => 'Logged out successfully']);
    }
}
