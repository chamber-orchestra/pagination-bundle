<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationTypeInterface;

class PaginationConfigBuilder implements PaginationConfigBuilderInterface
{
    /**
     * Current page
     */
    private int|null $page = null;
    /**
     * Per page limit of entries
     */
    private int|null $perPageLimit = null;
    /**
     * Aware of items in the collection
     */
    private bool $extended = true;

    public function __construct(
        private readonly ResolvedPaginationTypeInterface $type,
        private readonly string $name,
        private readonly array $options = []
    ) {
    }

    public function getPagination(): PaginationInterface
    {
        $config = $this->getPaginationConfig();

        return $config->isExtended() ? new ExtendedPagination($config) : new Pagination($config);
    }

    public function getPaginationConfig(): PaginationConfigInterface
    {
        return clone $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): PaginationConfigBuilderInterface
    {
        $this->page = $page;

        return $this;
    }

    public function getPerPageLimit(): int
    {
        return $this->perPageLimit;
    }

    public function setPerPageLimit(int $limit): PaginationConfigBuilderInterface
    {
        $this->perPageLimit = $limit;

        return $this;
    }

    public function isExtended(): bool
    {
        return $this->extended;
    }

    public function setExtended(bool $extended): PaginationConfigBuilderInterface
    {
        $this->extended = $extended;

        return $this;
    }

    public function getType(): ResolvedPaginationTypeInterface
    {
        return $this->type;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}