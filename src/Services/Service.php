<?php

declare(strict_types=1);

namespace Afterchange\Template\Services;

use Afterchange\Template\Repositories\Repository;

abstract class Service
{
    protected Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }
}
