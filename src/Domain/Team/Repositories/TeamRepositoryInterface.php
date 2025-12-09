<?php

declare(strict_types=1);

namespace Domain\Team\Repositories;

use Domain\Shared\Interfaces\Repository;
use Domain\Team\Aggregates\Team;
use Domain\Team\ValueObjects\TeamId;

/**
 * Team repository interface
 */
interface TeamRepositoryInterface extends Repository
{
    /**
     * Generate next team ID
     */
    public function nextIdentity(): string;

    /**
     * Find team by ID
     * 
     * @return Team|null
     */
    public function findById(string $id): mixed;

    /**
     * Find all active teams
     * 
     * @return array<Team>
     */
    public function findActive(): array;

    /**
     * Find teams by type
     * 
     * @return array<Team>
     */
    public function findByType(string $type): array;

    /**
     * Find child teams of a parent
     * 
     * @return array<Team>
     */
    public function findByParent(TeamId $parentId): array;

}

