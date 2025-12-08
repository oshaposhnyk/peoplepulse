<?php

declare(strict_types=1);

namespace Infrastructure\Persistence;

use Domain\Shared\Interfaces\AggregateRoot;
use Domain\Shared\Interfaces\Repository;
use Illuminate\Database\Eloquent\Model;

/**
 * Base repository implementation
 * 
 * Provides common functionality for all repositories.
 */
abstract class BaseRepository implements Repository
{
    /**
     * Get the Eloquent model class
     */
    abstract protected function model(): string;

    /**
     * Convert Eloquent model to domain aggregate
     */
    abstract protected function toDomain(Model $model): AggregateRoot;

    /**
     * Convert domain aggregate to Eloquent model
     */
    abstract protected function toModel(AggregateRoot $aggregate): Model;

    /**
     * Generate next identity
     */
    abstract public function nextIdentity(): string;

    /**
     * Save aggregate
     */
    public function save(AggregateRoot $aggregate): void
    {
        $model = $this->toModel($aggregate);
        $model->save();

        // Dispatch domain events
        $this->dispatchEvents($aggregate);
    }

    /**
     * Find aggregate by ID
     */
    public function findById(string $id): ?AggregateRoot
    {
        $modelClass = $this->model();
        $model = $modelClass::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    /**
     * Delete aggregate
     */
    public function delete(AggregateRoot $aggregate): void
    {
        $model = $this->toModel($aggregate);
        $model->delete();
    }

    /**
     * Dispatch domain events from aggregate
     */
    protected function dispatchEvents(AggregateRoot $aggregate): void
    {
        $events = $aggregate->releaseEvents();

        foreach ($events as $event) {
            event($event);
        }
    }

    /**
     * Get model instance
     */
    protected function getModelInstance(): Model
    {
        $modelClass = $this->model();
        return new $modelClass();
    }
}

