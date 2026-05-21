<?php

declare(strict_types=1);

namespace Afterchange\Template\Services;

use Afterchange\Template\Repositories\Repository;

/**
 * Base service providing access to a repository for domain operations.
 */
abstract class Service
{
    protected Repository $repository;

    /**
     * Initializes the service with its primary repository.
     *
     * @param Repository $repository The repository used for data persistence.
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }
}