<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination;

class CursorPagination extends Pagination implements CursorPaginationInterface
{
    private ?string $nextCursor = null;
    private ?string $previousCursor = null;

    public function getPosition(): int|string|null
    {
        $cursor = $this->config->getOptions()['cursor'] ?? null;

        return \is_string($cursor) ? $cursor : null;
    }

    public function setNextCursor(?string $cursor): void
    {
        $this->nextCursor = $cursor;
    }

    public function getNextCursor(): ?string
    {
        return $this->nextCursor;
    }

    public function setPreviousCursor(?string $cursor): void
    {
        $this->previousCursor = $cursor;
    }

    public function getPreviousCursor(): ?string
    {
        return $this->previousCursor;
    }
}
