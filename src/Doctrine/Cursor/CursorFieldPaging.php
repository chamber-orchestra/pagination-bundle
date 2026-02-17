<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Doctrine\Cursor;

use ChamberOrchestra\PaginationBundle\Pagination\CursorPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\PagingInterface;
use Doctrine\ORM\QueryBuilder;

readonly class CursorFieldPaging implements PagingInterface
{
    public function __construct(
        private PagingInterface $inner,
        private CursorFieldResolver $resolver,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return iterable<mixed>
     */
    public function paginate(mixed $target, PaginationInterface $pagination, array $options = []): iterable
    {
        if (
            $pagination instanceof CursorPaginationInterface
            && $target instanceof QueryBuilder
            && !isset($options['cursor_field'])
        ) {
            $options = \array_merge($options, $this->resolver->resolve($target));
        }

        return $this->inner->paginate($target, $pagination, $options);
    }
}
