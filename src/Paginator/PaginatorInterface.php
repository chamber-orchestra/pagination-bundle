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

interface PaginatorInterface
{
    public function supports(mixed $target, ?PaginationInterface $pagination = null): bool;

    /**
     * @param array<string, mixed> $options
     */
    public function count(mixed $target, array $options = []): int;

    /**
     * @param array<string, mixed> $options
     *
     * @return iterable<mixed>
     */
    public function paginate(mixed $target, PaginationInterface $pagination, array $options = []): iterable;
}
