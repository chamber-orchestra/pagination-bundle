<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination;

interface CursorPaginationInterface extends PaginationInterface
{
    /**
     * Sets the cursor for fetching the next page. Null means no next page exists.
     */
    public function setNextCursor(?string $cursor): void;

    /**
     * Returns the cursor for fetching the next page, or null if no next page exists.
     */
    public function getNextCursor(): ?string;

    /**
     * Sets the cursor for fetching the previous page. Null means no previous page exists.
     */
    public function setPreviousCursor(?string $cursor): void;

    /**
     * Returns the cursor for fetching the previous page, or null if no previous page exists.
     */
    public function getPreviousCursor(): ?string;
}
