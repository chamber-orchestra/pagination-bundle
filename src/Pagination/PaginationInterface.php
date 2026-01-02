<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;

interface PaginationInterface
{
    public function getName(): string;

    public function getPage(): int|null;

    public function getPerPageLimit(): int;

    public function createView(): PaginationView;
}
