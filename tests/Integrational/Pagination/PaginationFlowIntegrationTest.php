<?php

declare(strict_types=1);

namespace Tests\Integrational\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilder;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationRegistry;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationUtil;
use ChamberOrchestra\PaginationBundle\Pagination\Type\PaginationType;
use ChamberOrchestra\PaginationBundle\Pagination\Type\RangeType;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PaginationFlowIntegrationTest extends KernelTestCase
{
    public function testPaginationTypeCreatesView(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $factory = $container->get(PaginationFactory::class);
        $pagination = $factory->create(PaginationType::class, [
            'page' => 3,
            'limit' => 10,
        ]);

        $this->assertInstanceOf(PaginationInterface::class, $pagination);
        $view = $pagination->createView();
        $this->assertInstanceOf(\ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView::class, $view);

        $this->assertSame(3, $view->vars['current']);
        $this->assertSame(2, $view->vars['previous']);
        $this->assertSame(4, $view->vars['next']);
        $this->assertSame(10, $view->vars['limit']);

        $registry = $container->get(PaginationRegistry::class);
        $resolvedType = $registry->getType(PaginationType::class);
        $this->assertInstanceOf(ResolvedPaginationType::class, $resolvedType);
        $builder = $resolvedType->createBuilder(PaginationType::class, ['page' => 1]);

        $this->assertInstanceOf(PaginationConfigBuilder::class, $builder);
        $this->assertInstanceOf(PaginationConfigBuilder::class, $builder->getPaginationConfig());
    }

    public function testRangeTypeProvidesPagesCount(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $factory = $container->get(PaginationFactory::class);
        $pagination = $factory->create(RangeType::class, [
            'page' => 2,
            'limit' => 2,
            'page_range' => 3,
        ]);

        $this->assertInstanceOf(ExtendedPaginationInterface::class, $pagination);
        $pagination->setElementsCount(9);

        $view = $pagination->createView();

        $this->assertSame(5, PaginationUtil::getPagesCount($pagination));
        $this->assertSame(2, $view->vars['current']);
        $this->assertSame(5, $view->vars['pagesCount']);
        $this->assertSame(9, $view->vars['elementsCount']);
    }
}
