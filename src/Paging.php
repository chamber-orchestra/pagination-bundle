<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle;

use ChamberOrchestra\PaginationBundle\Exception\LogicException;
use ChamberOrchestra\PaginationBundle\Exception\RuntimeException;
use ChamberOrchestra\PaginationBundle\Pagination\CursorPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Paginator\PaginatorRegistry;

readonly class Paging implements PagingInterface
{
    public function __construct(
        private PaginatorRegistry $registry,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return iterable<mixed>
     */
    public function paginate(mixed $target, PaginationInterface $pagination, array $options = []): iterable
    {
        if ($pagination->getLimit() < 1) {
            throw new LogicException('Invalid per page limit, it must be a positive number');
        }

        $paginator = $this->registry->getSupportedPaginator($target, $pagination);
        if (null === $paginator) {
            throw new RuntimeException(\sprintf('No supported paginator for target "%s".', \get_debug_type($target)));
        }

        if ($pagination instanceof ExtendedPaginationInterface && !$pagination instanceof CursorPaginationInterface) {
            $pagination->setElementsCount($paginator->count($target, $options));
        }

        return $paginator->paginate($target, $pagination, $options);
    }
}
