<?php

// src/Controller/AttendantAuthController.php

namespace App\Controller;

use App\Entity\Attendant;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;


#[Route('/api/attendant')]
class AttendantAuthController extends AbstractController
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private TokenStorageInterface $tokenStorage,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager
    ) {}

 #[Route('/login', name: 'api_attendant_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            // Obtém os dados da requisição
            $data = json_decode($request->getContent(), true);
            
            // Verifica se email e senha foram fornecidos
            if (!isset($data['email']) || !isset($data['password'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Email e senha são obrigatórios'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Busca o atendente pelo email
            $attendant = $this->entityManager->getRepository(Attendant::class)
                ->findOneBy(['email' => $data['email']]);

            // Verifica se o atendente existe
            if (!$attendant) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Credenciais inválidas'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Verifica se a senha está correta
            if (!$this->passwordHasher->isPasswordValid($attendant, $data['password'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Credenciais inválidas'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Gera o token JWT
            $token = $this->jwtManager->create($attendant);

            // Monta a resposta com os dados do atendente e o token
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
                    'token' => $token
                ]
            ]);

        } catch (\Exception $e) {
            // Log do erro para debug
            error_log('Erro no login do atendente: ' . $e->getMessage());
            
            return new JsonResponse([
                'success' => false,
                'message' => 'Erro ao processar login'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
