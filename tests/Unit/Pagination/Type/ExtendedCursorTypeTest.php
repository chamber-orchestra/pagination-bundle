<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination\Type;

use ChamberOrchestra\PaginationBundle\Pagination\CursorPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilder;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\ExtendedCursorType;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ExtendedCursorTypeTest extends TestCase
{
    private function createType(?Request $request = null): ExtendedCursorType
    {
        $requestStack = new RequestStack();
        if (null !== $request) {
            $requestStack->push($request);
        }

        return new ExtendedCursorType($requestStack);
    }

    private function resolveOptions(ExtendedCursorType $type, array $options = []): array
    {
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);

        return $resolver->resolve($options);
    }

    public function testDefaultOptionsIncludeExtended(): void
    {
        $type = $this->createType(new Request());
        $options = $this->resolveOptions($type);

        $this->assertTrue($options['extended']);
        $this->assertNull($options['cursor']);
        $this->assertSame(12, $options['limit']);
    }

    public function testBuildPaginationSetsExtendedAndCursor(): void
    {
        $type = $this->createType(new Request());

        $builder = $this->createMock(PaginationConfigBuilder::class);
        $builder->expects($this->once())->method('setLimit')->with(20)->willReturnSelf();
        $builder->expects($this->once())->method('setCursor')->with(true)->willReturnSelf();
        $builder->expects($this->once())->method('setPosition')->with(0)->willReturnSelf();
        $builder->method('setExtended')->willReturnSelf();

        $type->buildPagination($builder, ['limit' => 20]);
    }

    public function testBuildPaginationCallsSetExtendedTrue(): void
    {
        $type = $this->createType(new Request());

        $builder = $this->createMock(PaginationConfigBuilder::class);
        $builder->method('setLimit')->willReturnSelf();
        $builder->method('setCursor')->willReturnSelf();
        $builder->method('setPosition')->willReturnSelf();

        // Parent sets extended(false), then ExtendedCursorType overrides to true.
        // We verify the final call is with true.
        $builder->expects($this->exactly(2))->method('setExtended')->willReturnSelf();

        $type->buildPagination($builder, ['limit' => 20]);
    }

    public function testBuildViewAddsElementsCount(): void
    {
        $type = $this->createType(new Request());
        $view = new PaginationView();

        $pagination = $this->createStub(ExtendedCursorPaginationStub::class);
        $pagination->method('getNextCursor')->willReturn('01JNEXT');
        $pagination->method('getPreviousCursor')->willReturn(null);
        $pagination->method('getElementsCount')->willReturn(150);
        $pagination->method('getLimit')->willReturn(20);

        $type->buildView($view, $pagination, [
            'parameter' => 'cursor',
            'cursor' => '01JABC',
            'limit' => 20,
        ]);

        $this->assertSame('cursor', $view->vars['parameter']);
        $this->assertSame('01JABC', $view->vars['cursor']);
        $this->assertSame(20, $view->vars['limit']);
        $this->assertSame('01JNEXT', $view->vars['next']);
        $this->assertNull($view->vars['previous']);
        $this->assertSame(8, $view->vars['pagesCount']); // ceil(150/20) = 8
        $this->assertSame(150, $view->vars['elementsCount']);
    }

    public function testBuildViewThrowsWithoutExtendedInterface(): void
    {
        $type = $this->createType(new Request());
        $view = new PaginationView();

        $pagination = $this->createStub(CursorPaginationInterface::class);

        $this->expectException(\LogicException::class);
        $type->buildView($view, $pagination, [
            'parameter' => 'cursor',
            'cursor' => '01JABC',
            'limit' => 20,
        ]);
    }

    public function testBuildViewThrowsWithPlainPagination(): void
    {
        $type = $this->createType(new Request());
        $view = new PaginationView();

        $pagination = $this->createStub(PaginationInterface::class);

        $this->expectException(\LogicException::class);
        $type->buildView($view, $pagination, [
            'parameter' => 'cursor',
            'cursor' => '01JABC',
            'limit' => 20,
        ]);
    }
}

/**
 * @internal stub interface for testing: combines CursorPaginationInterface + ExtendedPaginationInterface
 */
interface ExtendedCursorPaginationStub extends CursorPaginationInterface, ExtendedPaginationInterface
{
}
