<?php

declare(strict_types=1);

namespace Tests\Integrational\DependencyInjection;

use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory;
use ChamberOrchestra\PaginationBundle\Pagination\Type\PaginationType;
use ChamberOrchestra\PaginationBundle\Pagination\Type\RangeType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ContainerWiringTest extends KernelTestCase
{
    public function testPaginationFactoryBuildsConfiguredTypes(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $factory = $container->get(PaginationFactory::class);
        $this->assertInstanceOf(PaginationFactory::class, $factory);

        $pagination = $factory->create(PaginationType::class, [
            'page' => 2,
            'limit' => 5,
        ]);

        $view = $pagination->createView();
        $this->assertSame(2, $view->vars['current']);
        $this->assertSame(5, $view->vars['limit']);

        $rangePagination = $factory->create(RangeType::class, [
            'page' => 1,
            'limit' => 2,
            'page_range' => 3,
        ]);
        $this->assertInstanceOf(ExtendedPaginationInterface::class, $rangePagination);

        $rangePagination->setElementsCount(6);
        $rangeView = $rangePagination->createView();
        $this->assertSame(3, $rangeView->vars['pagesCount']);
    }
}
