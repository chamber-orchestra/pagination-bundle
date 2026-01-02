<?php

declare(strict_types=1);

namespace Tests\Integrational;

use ChamberOrchestra\PaginationBundle\Paging;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory;
use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationUtil;
use ChamberOrchestra\PaginationBundle\Pagination\Type\PaginationType;
use ChamberOrchestra\PaginationBundle\Pagination\Type\RangeType;
use ChamberOrchestra\PaginationBundle\Paginator\ArrayPaginator;
use ChamberOrchestra\PaginationBundle\Paginator\PaginatorRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PagingIntegrationTest extends KernelTestCase
{
    public function testPagingPaginatesArray(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $paging = new Paging(new PaginatorRegistry([new ArrayPaginator()]));
        $factory = $container->get(PaginationFactory::class);

        $pagination = $factory->create(PaginationType::class, [
            'page' => 2,
            'per_page_limit' => 2,
        ]);

        $result = $paging->paginate([1, 2, 3, 4], $pagination);

        $this->assertSame([3, 4], $result);
    }

    public function testPaginationUtilWorksWithExtendedPagination(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $factory = $container->get(PaginationFactory::class);
        $pagination = $factory->create(RangeType::class, [
            'page' => 1,
            'per_page_limit' => 2,
        ]);

        $this->assertInstanceOf(ExtendedPaginationInterface::class, $pagination);

        $pagination->setElementsCount(5);

        $this->assertSame(3, PaginationUtil::getPagesCount($pagination));
    }
}
