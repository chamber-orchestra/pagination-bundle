<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination;

use ChamberOrchestra\PaginationBundle\Exception\LogicException;

class PaginationUtil
{
    private function __construct()
    {
    }

    public static function getOffset(PaginationInterface $pagination): int
    {
        if ($pagination instanceof CursorPaginationInterface) {
            throw new LogicException('getOffset() is not applicable to cursor-based pagination.');
        }

        $position = $pagination->getPosition();
        if (!\is_int($position)) {
            throw new LogicException(\sprintf('getOffset() requires an integer position, got %s from pagination "%s".', \get_debug_type($position), $pagination->getName()));
        }

        return \abs($position - 1) * $pagination->getLimit();
    }

    public static function getPagesCount(PaginationInterface $pagination): int
    {
        if (!$pagination instanceof ExtendedPaginationInterface) {
            throw new LogicException(\sprintf('Pagination of type %s should be marked as "extended" to use %s.', $pagination->getName(), __METHOD__));
        }

        return (int) \ceil($pagination->getElementsCount() / $pagination->getLimit());
    }
}
