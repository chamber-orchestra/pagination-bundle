<?php

declare(strict_types=1);

namespace Tests\Integrational\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\Type\PaginationType;
use ChamberOrchestra\PaginationBundle\PaginationAwareTrait;
use ChamberOrchestra\PaginationBundle\PaginationTrait;
use ChamberOrchestra\PaginationBundle\Paginator\ArrayPaginator;
use ChamberOrchestra\PaginationBundle\Paginator\PaginatorRegistry;
use ChamberOrchestra\PaginationBundle\Paging;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TraitsIntegrationTest extends KernelTestCase
{
    public function testPaginationTraitUsesContainerFactory(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $object = new class($container) {
            use PaginationTrait;

            public function __construct(public \Symfony\Component\DependencyInjection\ContainerInterface $container)
            {
            }

            public function buildPagination(): \ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface
            {
                return $this->createPagination(PaginationType::class, ['page' => 1, 'limit' => 2]);
            }
        };

        $pagination = $object->buildPagination();
        $this->assertSame(1, $pagination->getPosition());
    }

    public function testPaginationAwareTraitStoresPaging(): void
    {
        self::bootKernel();
        $object = new class {
            use PaginationAwareTrait;

            public function getPaging(): ?Paging
            {
                return $this->paging;
            }
        };

        $paging = new Paging(new PaginatorRegistry([new ArrayPaginator()]));
        $object->withPaging($paging);

        $this->assertSame($paging, $object->getPaging());
    }
}
