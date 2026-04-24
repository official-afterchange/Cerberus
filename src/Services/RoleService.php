<?php

declare(strict_types=1);

namespace Afterchange\Template\Services;

use Afterchange\Template\Repositories\RoleRepository;

class RoleService extends Service
{
    /** @var RoleRepository */
    protected \Afterchange\Template\Repositories\Repository $repository;

    public function __construct(RoleRepository $repository)
    {
        parent::__construct($repository);
    }
}
