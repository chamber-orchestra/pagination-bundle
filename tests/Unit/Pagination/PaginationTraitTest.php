<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\PaginationTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class PaginationTraitTest extends TestCase
{
    public function testCreatePaginationUsesFactory(): void
    {
        $factory = $this->createMock(PaginationFactory::class);
        $pagination = $this->createStub(PaginationInterface::class);

        $factory->expects($this->once())
            ->method('create')
            ->with('range', ['page' => 2])
            ->willReturn($pagination);

        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')->with(PaginationFactory::class)->willReturn($factory);

        $object = new class($container) {
            use PaginationTrait;

            public function __construct(public ContainerInterface $container)
            {
            }

            public function make(string $type, array $options = []): PaginationInterface
            {
                return $this->createPagination($type, $options);
            }
        };

        $result = $object->make('range', ['page' => 2]);

        $this->assertSame($pagination, $result);
    }
}
