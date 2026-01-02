<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use Doctrine\Common\Collections\Collection;

interface PagingInterface
{
    /**
     * @param mixed $target - anything that needs to be paginated
     *
     * @return array|Collection current page of elements
     */
    public function paginate($target, PaginationInterface $pagination, array $options = []): iterable;
}
