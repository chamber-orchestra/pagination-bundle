<?php

declare(strict_types=1);

namespace Tests\Integrational\View;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory;
use ChamberOrchestra\PaginationBundle\Pagination\Type\PaginationType;
use ChamberOrchestra\PaginationBundle\View\PaginatedView;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PaginatedViewIntegrationTest extends KernelTestCase
{
    public function testPaginatedViewCombinesDataAndMetadata(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $factory = $container->get(PaginationFactory::class);
        $pagination = $factory->create(PaginationType::class, [
            'page' => 1,
            'per_page_limit' => 2,
        ]);

        $view = new PaginatedView([
            ['id' => 1],
        ], $pagination, static fn(array $item) => $item);

        $headers = $view->getHeaders();

        $this->assertArrayHasKey('Pagination-Current', $headers);
        $this->assertSame('1', $headers['Pagination-Current']);
    }
}
