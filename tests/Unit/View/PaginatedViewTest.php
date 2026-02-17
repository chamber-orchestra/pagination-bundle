<?php

declare(strict_types=1);

namespace Tests\Unit\View;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use ChamberOrchestra\PaginationBundle\View\PaginatedView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PaginatedViewTest extends TestCase
{
    public function testBuildsMetadataFromPaginationView(): void
    {
        $paginationView = new PaginationView();
        $paginationView->vars = [
            'current' => 2,
            'limit' => 10,
        ];

        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('createView')->willReturn($paginationView);

        $view = new PaginatedView([
            ['id' => 1],
            ['id' => 2],
        ], $pagination, static fn (array $item) => $item);

        $this->assertSame($paginationView->vars, $view->metadata);
    }

    public function testHeadersIncludePaginationMetadata(): void
    {
        $paginationView = new PaginationView();
        $paginationView->vars = [
            'current' => 2,
            'limit' => 10,
            'empty' => null,
        ];

        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('createView')->willReturn($paginationView);

        $view = new PaginatedView([], $pagination, static fn (array $item) => $item);

        $headers = $view->getHeaders();

        $this->assertSame('2', $headers['Pagination-Current']);
        $this->assertSame('10', $headers['Pagination-Limit']);
        $this->assertArrayNotHasKey('Pagination-Empty', $headers);
        $this->assertArrayHasKey('X-Pagination-Current', $headers);
    }

    public function testNormalizeUsesDataAndMetadata(): void
    {
        $paginationView = new PaginationView();
        $paginationView->vars = ['current' => 1];

        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('createView')->willReturn($paginationView);

        $view = new PaginatedView([['id' => 1]], $pagination, static fn (array $item) => $item);

        $normalizer = new class implements NormalizerInterface {
            public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
            {
                return $data;
            }

            public function supportsNormalization($data, ?string $format = null, array $context = []): bool
            {
                return true;
            }

            public function getSupportedTypes(?string $format): array
            {
                return ['array' => true];
            }
        };

        $normalized = $view->normalize($normalizer);

        $this->assertSame([['id' => 1]], $normalized['data']);
        $this->assertSame(['current' => 1], $normalized['metadata']);
    }

    public function testHeadersHandleCursorPaginationValues(): void
    {
        $paginationView = new PaginationView();
        $paginationView->vars = [
            'after' => '01JABC',
            'before' => null,
            'limit' => 20,
            'next_cursor' => '01JNEXT',
            'previous_cursor' => '01JPREV',
            'has_next_page' => true,
            'has_previous_page' => false,
        ];

        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('createView')->willReturn($paginationView);

        $view = new PaginatedView([], $pagination, static fn (array $item) => $item);

        $headers = $view->getHeaders();

        $this->assertSame('01JABC', $headers['Pagination-After']);
        $this->assertSame('20', $headers['Pagination-Limit']);
        $this->assertSame('01JNEXT', $headers['Pagination-Next_cursor']);
        $this->assertSame('01JPREV', $headers['Pagination-Previous_cursor']);
        $this->assertSame('1', $headers['Pagination-Has_next_page']);
        $this->assertSame('0', $headers['Pagination-Has_previous_page']);
        $this->assertArrayNotHasKey('Pagination-Before', $headers);
    }
}
