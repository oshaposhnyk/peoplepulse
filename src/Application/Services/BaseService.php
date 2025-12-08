<?php

declare(strict_types=1);

namespace Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Base application service class
 * 
 * Provides common functionality for all application services.
 */
abstract class BaseService
{
    /**
     * Execute operation within database transaction
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     * @throws \Throwable
     */
    protected function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    /**
     * Log info message
     */
    protected function logInfo(string $message, array $context = []): void
    {
        Log::info($message, array_merge($context, [
            'service' => static::class,
        ]));
    }

    /**
     * Log error message
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error($message, array_merge($context, [
            'service' => static::class,
        ]));
    }

    /**
     * Log warning message
     */
    protected function logWarning(string $message, array $context = []): void
    {
        Log::warning($message, array_merge($context, [
            'service' => static::class,
        ]));
    }

    /**
     * Handle service exception
     */
    protected function handleException(\Throwable $exception, string $operation): void
    {
        $this->logError("Failed to {$operation}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        throw $exception;
    }
}

