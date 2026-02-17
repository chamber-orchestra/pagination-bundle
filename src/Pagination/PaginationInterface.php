<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;

interface PaginationInterface
{
    /**
     * Returns the pagination type name used to create this pagination.
     */
    public function getName(): string;

    /**
     * Returns the current position in the dataset (page number or cursor value).
     */
    public function getPosition(): int|string|null;

    /**
     * Returns the maximum number of items displayed per page.
     */
    public function getLimit(): int;

    /**
     * Builds and returns the view representation of this pagination.
     */
    public function createView(): PaginationView;
}
