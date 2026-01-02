<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Paginator;

use ArrayObject;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationUtil;

class ArrayPaginator extends AbstractPaginator
{
    /**
     * @param iterable $target
     */
    public function paginate($target, PaginationInterface $pagination, array $options = []): iterable
    {
        if ($target instanceof ArrayObject) {
            return new ArrayObject(
                \array_slice(
                    $target->getArrayCopy(),
                    PaginationUtil::getOffset($pagination),
                    $pagination->getPerPageLimit()
                )
            );
        }

        /** @var $target array */
        return \array_slice(
            $target,
            PaginationUtil::getOffset($pagination),
            $pagination->getPerPageLimit()
        );
    }

    public function count($target, array $options = []): int
    {
        return \count($target);
    }

    public function supports($target): bool
    {
        return \is_array($target) || $target instanceof ArrayObject;
    }
}
