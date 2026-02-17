<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationRegistry;
use ChamberOrchestra\PaginationBundle\Pagination\Type\PaginationTypeInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class PaginationRegistryTest extends TestCase
{
    public function testResolvesAndCachesTypes(): void
    {
        $innerType = $this->createStub(PaginationTypeInterface::class);

        $locator = new ServiceLocator([
            'default' => static fn () => $innerType,
        ]);

        $registry = new PaginationRegistry($locator);

        $first = $registry->getType('default');
        $second = $registry->getType('default');

        $this->assertInstanceOf(ResolvedPaginationType::class, $first);
        $this->assertSame($first, $second);
    }
}
