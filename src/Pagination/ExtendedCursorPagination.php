<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination;

class ExtendedCursorPagination extends CursorPagination implements ExtendedPaginationInterface
{
    private ?int $elementsCount = null;

    public function getElementsCount(): int
    {
        return $this->elementsCount ?? throw new \LogicException('Elements count has not been set. Ensure the paginator calls setElementsCount() before accessing this value.');
    }

    public function setElementsCount(int $elementsCount): void
    {
        $this->elementsCount = $elementsCount;
    }
}
