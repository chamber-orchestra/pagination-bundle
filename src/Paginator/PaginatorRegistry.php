<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Paginator;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;

readonly class PaginatorRegistry
{
    /**
     * @param iterable<PaginatorInterface> $paginators
     */
    public function __construct(private iterable $paginators)
    {
    }

    public function getSupportedPaginator(mixed $target, PaginationInterface $pagination): ?PaginatorInterface
    {
        foreach ($this->paginators as $pager) {
            if ($pager->supports($target, $pagination)) {
                return $pager;
            }
        }

        return null;
    }
}
