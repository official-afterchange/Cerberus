<?php

declare(strict_types=1);

namespace Afterchange\Template\Services;

use Afterchange\Template\Repositories\PermissionRepository;

class PermissionService extends Service
{
    /** @var PermissionRepository */
    protected \Afterchange\Template\Repositories\Repository $repository;

    public function __construct(PermissionRepository $repository)
    {
        parent::__construct($repository);
    }
}
