<?php

namespace App\Controller;

use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use App\Entity\User;
use App\Entity\Attendant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/users')]
class UserController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(
        private UserManager $userManager,
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }


    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $user = $this->userManager->createUser($data);

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail()
                ]
            ], 201);
        } catch (BadRequestException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }


    // No UserController.php
    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        try {
            // Verificar se o usuário está autenticado
            $currentUser = $this->getUser();
            if (!$currentUser) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Verificar se é um atendente com função admin
         /*  $attendant = $this->entityManager->getRepository(Attendant::class)
                ->findOneBy(['user' => $currentUser]);

            if (!$attendant || $attendant->getFunction() !== 'Admin') {

            
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Apenas administradores podem listar usuários'
                ], Response::HTTP_FORBIDDEN);
            }*/
            // Buscar todos os usuários (excluindo atendentes)
            $users = $this->entityManager->getRepository(User::class)
                ->createQueryBuilder('u')
                ->where('u.isAttendant = :isAttendant')
                ->setParameter('isAttendant', false)
                ->orderBy('u.name', 'ASC')
                ->getQuery()
                ->getResult();

            return new JsonResponse([
                'success' => true,
                'data' => array_map(function ($user) {
                    return [
                        'id' => $user->getId(),
                        'name' => $user->getName(),
                        'email' => $user->getEmail()
                    ];
                }, $users)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erro ao buscar usuários: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
