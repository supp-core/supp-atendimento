<?php

namespace App\Repository;

use App\Entity\Project;

interface ProjectRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?Project;
    public function findByStatus(string $status): array;
    public function save(Project $project): void;
    public function remove(Project $project): void;
}
