<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination;

interface PaginationConfigBuilderInterface extends PaginationConfigInterface
{
    public function setPage(int $page): PaginationConfigBuilderInterface;

    public function setPerPageLimit(int $limit): PaginationConfigBuilderInterface;

    public function setExtended(bool $extended): PaginationConfigBuilderInterface;

    public function getPagination(): PaginationInterface;

    public function getPaginationConfig(): PaginationConfigInterface;
}