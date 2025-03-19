<?php


namespace App\Controller;

use App\Entity\User;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;


#[Route('/api/users')]
class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private UserManager $userManager;


    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserManager $userManager
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->userManager = $userManager;
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


    // Adicione este método ao UserController.php

#[Route('/{id}', methods: ['PUT'])]
public function update(int $id, Request $request): JsonResponse
{
    try {
        $data = json_decode($request->getContent(), true);
        
        // Buscar o usuário existente
        $user = $this->entityManager->getRepository(User::class)->find($id);
        
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Usuário não encontrado'
            ], 404);
        }
        
        // Atualizar os dados do usuário
        if (isset($data['name'])) {
            $user->setName($data['name']);
        }
        
        if (isset($data['email'])) {
            // Verificar se o email já está em uso por outro usuário
            $existingUser = $this->entityManager->getRepository(User::class)
                ->findOneBy(['email' => $data['email']]);
            
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Email já está em uso por outro usuário'
                ], 400);
            }
            
            $user->setEmail($data['email']);
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        }
        
        // Persistir as alterações
        $this->entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => 'Usuário atualizado com sucesso',
            'data' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail()
            ]
        ]);
    } catch (\Exception $e) {
        return new JsonResponse([
            'success' => false,
            'message' => 'Erro ao atualizar usuário: ' . $e->getMessage()
        ], 500);
    }
}
}
