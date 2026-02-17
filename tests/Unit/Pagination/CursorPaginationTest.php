<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\CursorPagination;
use ChamberOrchestra\PaginationBundle\Pagination\CursorPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilder;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationType;
use PHPUnit\Framework\TestCase;

final class CursorPaginationTest extends TestCase
{
    private function createPagination(array $options = []): CursorPagination
    {
        $type = $this->createStub(ResolvedPaginationType::class);

        $config = new PaginationConfigBuilder($type, 'cursor', $options);
        $config->setPosition(1)->setLimit(12)->setCursor(true);

        return new CursorPagination($config);
    }

    public function testImplementsInterfaces(): void
    {
        $pagination = $this->createPagination();

        $this->assertInstanceOf(CursorPaginationInterface::class, $pagination);
        $this->assertInstanceOf(PaginationInterface::class, $pagination);
    }

    public function testGetPositionFromOptions(): void
    {
        $pagination = $this->createPagination(['cursor' => '01JABC']);

        $this->assertSame('01JABC', $pagination->getPosition());
    }

    public function testPositionDefaultsToNull(): void
    {
        $pagination = $this->createPagination();

        $this->assertNull($pagination->getPosition());
    }

    public function testNextCursorGetterSetter(): void
    {
        $pagination = $this->createPagination();

        $this->assertNull($pagination->getNextCursor());

        $pagination->setNextCursor('01JNEXT');
        $this->assertSame('01JNEXT', $pagination->getNextCursor());

        $pagination->setNextCursor(null);
        $this->assertNull($pagination->getNextCursor());
    }

    public function testPreviousCursorGetterSetter(): void
    {
        $pagination = $this->createPagination();

        $this->assertNull($pagination->getPreviousCursor());

        $pagination->setPreviousCursor('01JPREV');
        $this->assertSame('01JPREV', $pagination->getPreviousCursor());

        $pagination->setPreviousCursor(null);
        $this->assertNull($pagination->getPreviousCursor());
    }

    public function testDelegatesPerPageLimitAndPageToConfig(): void
    {
        $pagination = $this->createPagination();

        $this->assertSame(12, $pagination->getLimit());
        $this->assertSame('cursor', $pagination->getName());
    }
}
