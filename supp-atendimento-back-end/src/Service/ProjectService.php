<?php

namespace App\Service;

use App\Entity\Attendant;
use App\Entity\Project;
use App\Entity\Service;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProjectService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProjectRepository $projectRepository
    ) {}

    public function createProject(array $data, Attendant $createdBy): Project
    {
        if (empty($data['name']) || empty($data['acronym']) || empty($data['date_start'])) {
            throw new \InvalidArgumentException('Campos obrigatórios: name, acronym, date_start.');
        }

        $this->validateAcronymUnique(strtoupper($data['acronym']));
        $this->validateDates($data['date_start'], $data['date_end'] ?? null);

        $project = new Project();
        $project->setName($data['name']);
        $project->setAcronym(strtoupper($data['acronym']));
        $project->setDescription($data['description'] ?? null);
        $project->setStatus($data['status'] ?? Project::STATUS_ATIVO);
        $project->setDateStart(new \DateTime($data['date_start']));
        $project->setDateEnd(!empty($data['date_end']) ? new \DateTime($data['date_end']) : null);
        $project->setCreatedAt(new \DateTime());
        $project->setCreatedBy($createdBy);

        $this->projectRepository->save($project);
        return $project;
    }

    public function updateProject(Project $project, array $data): Project
    {
        if (isset($data['name'])) {
            $project->setName($data['name']);
        }
        if (isset($data['description'])) {
            $project->setDescription($data['description']);
        }
        if (isset($data['acronym'])) {
            $newAcronym = strtoupper($data['acronym']);
            if ($newAcronym !== $project->getAcronym()) {
                $this->validateAcronymUnique($newAcronym);
            }
            $project->setAcronym($newAcronym);
        }
        if (isset($data['status'])) {
            $project->setStatus($data['status']);
        }
        if (isset($data['date_start'])) {
            $project->setDateStart(new \DateTime($data['date_start']));
        }
        if (array_key_exists('date_end', $data)) {
            $project->setDateEnd($data['date_end'] ? new \DateTime($data['date_end']) : null);
        }

        $this->validateDates(
            $project->getDateStart()?->format('Y-m-d'),
            $project->getDateEnd()?->format('Y-m-d')
        );

        $this->projectRepository->save($project);
        return $project;
    }

    public function canDelete(Project $project): bool
    {
        return $this->entityManager->getRepository(Service::class)
            ->count(['project' => $project]) === 0;
    }

    public function serialize(Project $project): array
    {
        return [
            'id' => $project->getId(),
            'name' => $project->getName(),
            'acronym' => $project->getAcronym(),
            'description' => $project->getDescription(),
            'status' => $project->getStatus(),
            'date_start' => $project->getDateStart()?->format('Y-m-d'),
            'date_end' => $project->getDateEnd()?->format('Y-m-d'),
            'created_at' => $project->getCreatedAt()?->format('Y-m-d\TH:i:s'),
            'created_by' => [
                'id' => $project->getCreatedBy()?->getId(),
                'name' => $project->getCreatedBy()?->getName(),
            ],
        ];
    }

    private function validateAcronymUnique(string $acronym): void
    {
        $existing = $this->projectRepository->findOneBy(['acronym' => $acronym]);
        if ($existing) {
            throw new \InvalidArgumentException("Sigla '{$acronym}' já está em uso por outro projeto.");
        }
    }

    private function validateDates(?string $dateStart, ?string $dateEnd): void
    {
        if ($dateStart && $dateEnd) {
            if (new \DateTime($dateEnd) < new \DateTime($dateStart)) {
                throw new \InvalidArgumentException('A data de término deve ser maior ou igual à data de início.');
            }
        }
    }
}
