<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\Type\PaginationTypeInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationType;
use Symfony\Component\DependencyInjection\ServiceLocator;

class PaginationRegistry
{
    /**
     * @var array<string, ResolvedPaginationType>
     */
    private array $resolved = [];

    /**
     * @param ServiceLocator<PaginationTypeInterface> $types
     */
    public function __construct(
        private readonly ServiceLocator $types,
    ) {
    }

    public function getType(string $name): ResolvedPaginationType
    {
        if (!isset($this->resolved[$name])) {
            $type = $this->types->get($name);
            \assert($type instanceof PaginationTypeInterface);
            $this->resolved[$name] = new ResolvedPaginationType($type);
        }

        return $this->resolved[$name];
    }
}
