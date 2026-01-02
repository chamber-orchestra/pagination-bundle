<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle;

use Symfony\Contracts\Service\Attribute\Required;

trait PaginationAwareTrait
{
    protected PagingInterface|null $paging = null;

    #[Required]
    public function withPaging(PagingInterface $paging): void
    {
        $this->paging = $paging;
    }
}