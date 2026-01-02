<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Paginator;

readonly class PaginatorRegistry
{
    public function __construct(private iterable $paginators)
    {
    }

    public function getSupportedPaginator($target): PaginatorInterface|null
    {
        foreach ($this->paginators as $pager) {
            if ($pager->supports($target)) {
                return $pager;
            }
        }

        return null;
    }
}