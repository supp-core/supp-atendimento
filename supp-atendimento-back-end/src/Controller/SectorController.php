<?php

namespace App\Controller;

use App\Entity\Sector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/sectors')]
class SectorController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        try {
            $sectors = $this->entityManager->getRepository(Sector::class)->findAll();
            
            return new JsonResponse([
                'success' => true,
                'data' => array_map(function($sector) {
                    return [
                        'id' => $sector->getId(),
                        'name' => $sector->getName()
                    ];
                }, $sectors)
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error fetching sectors: ' . $e->getMessage()
            ], 500);
        }
    }
}