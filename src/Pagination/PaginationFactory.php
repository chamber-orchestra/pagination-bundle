<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination;

readonly class PaginationFactory
{
    public function __construct(private PaginationRegistry $registry)
    {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function create(string $name, array $options = []): PaginationInterface
    {
        $type = $this->registry->getType($name);

        $builder = $type->createBuilder($name, $options);

        $type->buildPagination($builder, $builder->getOptions());

        return $builder->getPagination();
    }
}
