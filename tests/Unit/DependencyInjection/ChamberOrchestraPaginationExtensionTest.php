<?php

declare(strict_types=1);

namespace Tests\Unit\DependencyInjection;

use ChamberOrchestra\PaginationBundle\DependencyInjection\ChamberOrchestraPaginationExtension;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationRegistry;
use ChamberOrchestra\PaginationBundle\Paginator\PaginatorRegistry;
use ChamberOrchestra\PaginationBundle\Paging;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ChamberOrchestraPaginationExtensionTest extends TestCase
{
    public function testLoadsServiceDefinitions(): void
    {
        $container = new ContainerBuilder();
        $extension = new ChamberOrchestraPaginationExtension();

        $extension->load([], $container);

        $this->assertTrue($container->hasDefinition(Paging::class));
        $this->assertTrue($container->hasDefinition(PaginationFactory::class));
        $this->assertTrue($container->hasDefinition(PaginatorRegistry::class));
        $this->assertTrue($container->hasDefinition(PaginationRegistry::class));
    }
}
