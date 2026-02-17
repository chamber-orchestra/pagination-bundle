<?php

declare(strict_types=1);

namespace Tests\Integrational\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\CursorPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\CursorType;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CursorPaginationFlowIntegrationTest extends KernelTestCase
{
    public function testFactoryCreatesCursorPagination(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $factory = $container->get(PaginationFactory::class);
        $pagination = $factory->create(CursorType::class, [
            'cursor' => '01JABC',
            'limit' => 20,
        ]);

        $this->assertInstanceOf(PaginationInterface::class, $pagination);
        $this->assertInstanceOf(CursorPaginationInterface::class, $pagination);
        $this->assertSame('01JABC', $pagination->getPosition());
        $this->assertSame(20, $pagination->getLimit());
    }

    public function testCursorPaginationCreatesView(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $factory = $container->get(PaginationFactory::class);
        $pagination = $factory->create(CursorType::class, [
            'cursor' => '01JABC',
            'limit' => 15,
        ]);

        $pagination->setNextCursor('01JNEXT');
        $pagination->setPreviousCursor('01JPREV');

        $view = $pagination->createView();

        $this->assertInstanceOf(PaginationView::class, $view);
        $this->assertSame('01JABC', $view->vars['cursor']);
        $this->assertSame(15, $view->vars['limit']);
        $this->assertSame('01JNEXT', $view->vars['next']);
        $this->assertSame('01JPREV', $view->vars['previous']);
    }

    public function testCursorPaginationWithNoOptions(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $factory = $container->get(PaginationFactory::class);
        $pagination = $factory->create(CursorType::class, []);

        $this->assertInstanceOf(CursorPaginationInterface::class, $pagination);
        $this->assertNull($pagination->getPosition());
        $this->assertSame(12, $pagination->getLimit());
    }
}
