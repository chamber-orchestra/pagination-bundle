<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination;

class ExtendedPagination extends Pagination implements ExtendedPaginationInterface
{
    private int|null $elementsCount = null;

    public function getElementsCount(): int
    {
        return $this->elementsCount;
    }

    public function setElementsCount(int $elementsCount): void
    {
        $this->elementsCount = $elementsCount;
    }
}
