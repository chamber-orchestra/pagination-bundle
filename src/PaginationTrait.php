<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @property ContainerInterface $container
 */
trait PaginationTrait
{
    /**
     * @param array<string, mixed> $options
     */
    protected function createPagination(string $type, array $options = []): PaginationInterface
    {
        $factory = $this->container->get(PaginationFactory::class);
        \assert($factory instanceof PaginationFactory);

        return $factory->create($type, $options);
    }
}
