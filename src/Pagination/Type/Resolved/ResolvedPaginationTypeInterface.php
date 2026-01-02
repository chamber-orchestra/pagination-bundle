<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilderInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;

interface ResolvedPaginationTypeInterface
{
    public function createBuilder(string $name, array $options): PaginationConfigBuilderInterface;

    public function buildPagination(PaginationConfigBuilderInterface $builder, array $options): void;

    public function createView(PaginationInterface $pagination): PaginationView;

    public function buildView(PaginationView $view, PaginationInterface $pagination, array $options): void;
}
