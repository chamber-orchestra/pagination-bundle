<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\CursorPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\ExtendedCursorPagination;
use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilder;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationType;
use PHPUnit\Framework\TestCase;

final class ExtendedCursorPaginationTest extends TestCase
{
    private function createPagination(array $options = []): ExtendedCursorPagination
    {
        $type = $this->createStub(ResolvedPaginationType::class);

        $config = new PaginationConfigBuilder($type, 'extended_cursor', $options);
        $config->setPosition(1)->setLimit(12)->setCursor(true)->setExtended(true);

        return new ExtendedCursorPagination($config);
    }

    public function testImplementsInterfaces(): void
    {
        $pagination = $this->createPagination();

        $this->assertInstanceOf(CursorPaginationInterface::class, $pagination);
        $this->assertInstanceOf(ExtendedPaginationInterface::class, $pagination);
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

    public function testElementsCountGetterSetter(): void
    {
        $pagination = $this->createPagination();

        $pagination->setElementsCount(150);
        $this->assertSame(150, $pagination->getElementsCount());
    }

    public function testElementsCountThrowsWhenNotSet(): void
    {
        $pagination = $this->createPagination();

        $this->expectException(\LogicException::class);
        $pagination->getElementsCount();
    }

    public function testNextCursorGetterSetter(): void
    {
        $pagination = $this->createPagination();

        $this->assertNull($pagination->getNextCursor());

        $pagination->setNextCursor('01JNEXT');
        $this->assertSame('01JNEXT', $pagination->getNextCursor());
    }

    public function testPreviousCursorGetterSetter(): void
    {
        $pagination = $this->createPagination();

        $this->assertNull($pagination->getPreviousCursor());

        $pagination->setPreviousCursor('01JPREV');
        $this->assertSame('01JPREV', $pagination->getPreviousCursor());
    }

    public function testDelegatesLimitAndNameToConfig(): void
    {
        $pagination = $this->createPagination();

        $this->assertSame(12, $pagination->getLimit());
        $this->assertSame('extended_cursor', $pagination->getName());
    }
}
