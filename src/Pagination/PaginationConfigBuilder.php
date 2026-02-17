<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationType;

class PaginationConfigBuilder
{
    /**
     * Current position (page number).
     */
    private ?int $position = null;
    /**
     * Maximum number of items per page.
     */
    private ?int $limit = null;
    /**
     * Aware of items in the collection.
     */
    private bool $extended = true;
    /**
     * Cursor-based pagination.
     */
    private bool $cursor = false;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private readonly ResolvedPaginationType $type,
        private readonly string $name,
        private readonly array $options = []
    ) {
    }

    public function getPagination(): PaginationInterface
    {
        $config = $this->getPaginationConfig();

        if ($config->isCursor()) {
            return $config->isExtended() ? new ExtendedCursorPagination($config) : new CursorPagination($config);
        }

        return $config->isExtended() ? new ExtendedPagination($config) : new Pagination($config);
    }

    public function getPaginationConfig(): self
    {
        return clone $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPosition(): int
    {
        return $this->position ?? throw new \LogicException('Position has not been set on PaginationConfigBuilder.');
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit ?? throw new \LogicException('Limit has not been set on PaginationConfigBuilder.');
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function isExtended(): bool
    {
        return $this->extended;
    }

    public function setExtended(bool $extended): self
    {
        $this->extended = $extended;

        return $this;
    }

    public function isCursor(): bool
    {
        return $this->cursor;
    }

    public function setCursor(bool $cursor): self
    {
        $this->cursor = $cursor;

        return $this;
    }

    public function getType(): ResolvedPaginationType
    {
        return $this->type;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
