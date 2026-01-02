<?php

declare(strict_types=1);

namespace Tests\Integrational\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilderInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationRegistry;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationUtil;
use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationTypeInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\PaginationType;
use ChamberOrchestra\PaginationBundle\Pagination\Type\RangeType;
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
            'per_page_limit' => 10,
        ]);

        $this->assertInstanceOf(PaginationInterface::class, $pagination);
        $view = $pagination->createView();
        $this->assertInstanceOf(\ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView::class, $view);

        $this->assertSame(3, $view->vars['current']);
        $this->assertSame(2, $view->vars['previous']);
        $this->assertSame(4, $view->vars['next']);
        $this->assertSame(10, $view->vars['per_page_limit']);

        $registry = $container->get(PaginationRegistry::class);
        $resolvedType = $registry->getType(PaginationType::class);
        $this->assertInstanceOf(ResolvedPaginationTypeInterface::class, $resolvedType);
        $builder = $resolvedType->createBuilder(PaginationType::class, ['page' => 1]);

        $this->assertInstanceOf(PaginationConfigBuilderInterface::class, $builder);
        $this->assertInstanceOf(PaginationConfigInterface::class, $builder->getPaginationConfig());
    }

    public function testRangeTypeProvidesPagesCount(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $factory = $container->get(PaginationFactory::class);
        $pagination = $factory->create(RangeType::class, [
            'page' => 2,
            'per_page_limit' => 2,
            'page_range' => 3,
        ]);

        $this->assertInstanceOf(ExtendedPaginationInterface::class, $pagination);
        $pagination->setElementsCount(9);

        $view = $pagination->createView();

        $this->assertSame(5, PaginationUtil::getPagesCount($pagination));
        $this->assertSame(2, $view->vars['current']);
        $this->assertSame(5, $view->vars['pages_count']);
        $this->assertSame(9, $view->vars['elements_count']);
    }
}
