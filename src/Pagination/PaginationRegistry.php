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
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationTypeFactoryInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationTypeInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class PaginationRegistry
{
    /**
     * @var ResolvedPaginationTypeInterface[]
     */
    private array $resolved = [];

    public function __construct(
        private readonly ResolvedPaginationTypeFactoryInterface $factory,
        private readonly ServiceLocator $types,
    ) {
    }

    public function getType(string $name): ResolvedPaginationTypeInterface
    {
        // create one pagination per request
        if (!isset($this->resolved[$name])) {
            $this->resolved[$name] = $this->resolve($this->types->get($name));
        }

        return $this->resolved[$name];
    }

    private function resolve(PaginationTypeInterface $type): ResolvedPaginationTypeInterface
    {
        return $this->factory->createResolvedPaginationType($type);
    }
}