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
use ChamberOrchestra\PaginationBundle\Pagination\PaginationUtil;

class ArrayPaginator extends AbstractPaginator
{
    /**
     * @param array<string, mixed> $options
     *
     * @return array<mixed>|\ArrayObject<int, mixed>
     */
    public function paginate(mixed $target, PaginationInterface $pagination, array $options = []): iterable
    {
        if ($target instanceof \ArrayObject) {
            return new \ArrayObject(
                \array_slice(
                    $target->getArrayCopy(),
                    PaginationUtil::getOffset($pagination),
                    $pagination->getLimit()
                )
            );
        }

        \assert(\is_array($target));

        return \array_slice(
            $target,
            PaginationUtil::getOffset($pagination),
            $pagination->getLimit()
        );
    }

    /**
     * @param array<string, mixed> $options
     */
    public function count(mixed $target, array $options = []): int
    {
        \assert(\is_array($target) || $target instanceof \ArrayObject);

        return \count($target);
    }

    public function supports(mixed $target, ?PaginationInterface $pagination = null): bool
    {
        return \is_array($target) || $target instanceof \ArrayObject;
    }
}
