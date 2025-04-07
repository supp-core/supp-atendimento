<?php

namespace App\Controller;

use App\Entity\ServiceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/service-types')]
class ServiceTypeController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        try {
            $serviceTypes = $this->entityManager->getRepository(ServiceType::class)->findAll();
            
            return new JsonResponse([
                'success' => true,
                'data' => array_map(function($type) {
                    return [
                        'id' => $type->getId(),
                        'name' => $type->getName(),
                        'description' => $type->getDescription(),
                    ];
                }, $serviceTypes)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error fetching service types: ' . $e->getMessage()
            ], 500);
        }
    }
}