<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Attendant;
use App\Entity\Sector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;



#[Route('/api/attendants')]
class AttendantController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {}


    #[Route('', methods: ['GET'])]
public function index(): JsonResponse
{
    try {

    //    die('parou aqui');
        // Busca todos os atendentes com seus respectivos setores e usuários
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('a', 's', 'u')
            ->from(Attendant::class, 'a')
            ->leftJoin('a.sector', 's')
            ->leftJoin('a.user', 'u')
            ->where('u.isAttendant = :isAttendant')
            ->setParameter('isAttendant', true)
            ->orderBy('a.name', 'ASC');

        $attendants = $queryBuilder->getQuery()->getResult();

        return new JsonResponse([
            'success' => true,
            'data' => array_map(function($attendant) {
                return [
                    'id' => $attendant->getId(),
                    'name' => $attendant->getName(),
                    'email' => $attendant->getUser()->getEmail(),
                    'function' => $attendant->getFunction(),
                    'status' => $attendant->getStatus(),
                    'sector' => [
                        'id' => $attendant->getSector()?->getId(),
                        'name' => $attendant->getSector()?->getName()
                    ]
                ];
            }, $attendants)
        ]);
    } catch (\Exception $e) {
        return new JsonResponse([
            'success' => false,
            'message' => 'Erro ao buscar atendentes: ' . $e->getMessage()
        ], 500);
    }
}


    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Criar novo usuário
            $user = new User();
            $user->setEmail($data['email']);
            $user->setName($data['name']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
            $user->setRoles(['ROLE_USER', 'ROLE_ATTENDANT']);

            // Criar novo atendente
            $attendant = new Attendant();
            $attendant->setName($data['name']);
            $attendant->setFunction($data['function']);
            $attendant->setStatus('ACTIVE');
            
            // Buscar e definir o setor
            if (isset($data['sector_id'])) {
                $sector = $this->entityManager->getRepository(Sector::class)->find($data['sector_id']);
                if ($sector) {
                    $attendant->setSector($sector);
                }
            }

            // Vincular usuário ao atendente
            $attendant->setUser($user);

            // Persistir as entidades
            $this->entityManager->persist($user);
            $this->entityManager->persist($attendant);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'id' => $attendant->getId(),
                    'name' => $attendant->getName(),
                    'function' => $attendant->getFunction(),
                    'sector' => [
                        'id' => $attendant->getSector()?->getId(),
                        'name' => $attendant->getSector()?->getName()
                    ]
                ]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erro ao criar atendente: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {


 

            $attendant = $this->entityManager->getRepository(Attendant::class)
                ->createQueryBuilder('a')
                ->leftJoin('a.sector', 's')
                ->select('a', 's')
                ->where('a.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult();

            if (!$attendant) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Atendente não encontrado'
                ], 404);
            }

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'id' => $attendant->getId(),
                    'name' => $attendant->getName(),
                    //'email' => $attendant->getEmail(),
                    'function' => $attendant->getFunction(),
                    'status' => $attendant->getStatus(),
                    'sector' => [
                        'id' => $attendant->getSector()?->getId(),
                        'name' => $attendant->getSector()?->getName()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erro ao buscar atendente: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/sector/{sectorId}', methods: ['GET'])]
    public function listBySector(int $sectorId): JsonResponse
    {
        try {
            $attendants = $this->entityManager->getRepository(Attendant::class)
                ->createQueryBuilder('a')
                ->leftJoin('a.sector', 's')
                ->select('a', 's')
                ->where('s.id = :sectorId')
                ->andWhere('a.status = :status')
                ->setParameter('sectorId', $sectorId)
                ->setParameter('status', 'ACTIVE')
                ->orderBy('a.name', 'ASC')
                ->getQuery()
                ->getResult();

            return new JsonResponse([
                'success' => true,
                'data' => array_map(function($attendant) {
                    return [
                        'id' => $attendant->getId(),
                        'name' => $attendant->getName(),
                       // 'email' => $attendant->getEmail(),
                        'function' => $attendant->getFunction(),
                        'sector' => [
                            'id' => $attendant->getSector()?->getId(),
                            'name' => $attendant->getSector()?->getName()
                        ]
                    ];
                }, $attendants)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erro ao buscar atendentes do setor: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/status', methods: ['PUT'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['status'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Status é obrigatório'
                ], 400);
            }

            $attendant = $this->entityManager->getRepository(Attendant::class)->find($id);

            if (!$attendant) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Atendente não encontrado'
                ], 404);
            }

            $attendant->setStatus($data['status']);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Status atualizado com sucesso',
                'data' => [
                    'id' => $attendant->getId(),
                    'name' => $attendant->getName(),
                    'status' => $attendant->getStatus()
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erro ao atualizar status: ' . $e->getMessage()
            ], 500);
        }
    }
}