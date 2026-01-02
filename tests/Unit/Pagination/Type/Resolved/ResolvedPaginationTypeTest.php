<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination\Type\Resolved;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\PaginationTypeInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationType;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ResolvedPaginationTypeTest extends TestCase
{
    public function testOptionsResolverIsConfiguredOnce(): void
    {
        $inner = $this->createMock(PaginationTypeInterface::class);
        $inner->expects($this->once())
            ->method('configureOptions')
            ->with($this->isInstanceOf(OptionsResolver::class));

        $resolved = new ResolvedPaginationType($inner);

        $resolver1 = $resolved->getOptionsResolver();
        $resolver2 = $resolved->getOptionsResolver();

        $this->assertSame($resolver1, $resolver2);
    }

    public function testBuildViewCallsInnerTypeMethods(): void
    {
        $inner = $this->createMock(PaginationTypeInterface::class);
        $inner->expects($this->once())->method('buildView');
        $inner->expects($this->once())->method('finishView');

        $resolved = new ResolvedPaginationType($inner);

        $view = new PaginationView();
        $pagination = $this->createStub(PaginationInterface::class);

        $resolved->buildView($view, $pagination, []);
    }

    public function testCreateBuilderResolvesOptions(): void
    {
        $inner = $this->createMock(PaginationTypeInterface::class);
        $inner->expects($this->once())
            ->method('configureOptions')
            ->willReturnCallback(function (OptionsResolver $resolver): void {
                $resolver->setDefaults(['page' => 1]);
            });

        $resolved = new ResolvedPaginationType($inner);
        $builder = $resolved->createBuilder('default', ['page' => 2]);

        $this->assertSame('default', $builder->getName());
        $this->assertSame(2, $builder->getOptions()['page']);
    }
}
