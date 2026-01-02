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
    public function supports($target): bool;

    public function count($target, array $options = []): int;

    public function paginate($target, PaginationInterface $pagination, array $options = []): iterable;
}
