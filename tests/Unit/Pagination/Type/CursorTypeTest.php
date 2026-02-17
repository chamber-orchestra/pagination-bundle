<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination\Type;

use ChamberOrchestra\PaginationBundle\Pagination\CursorPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilder;
use ChamberOrchestra\PaginationBundle\Pagination\Type\CursorType;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CursorTypeTest extends TestCase
{
    private function createType(?Request $request = null): CursorType
    {
        $requestStack = new RequestStack();
        if (null !== $request) {
            $requestStack->push($request);
        }

        return new CursorType($requestStack);
    }

    private function resolveOptions(CursorType $type, array $options = []): array
    {
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);

        return $resolver->resolve($options);
    }

    public function testDefaultOptions(): void
    {
        $type = $this->createType(new Request());
        $options = $this->resolveOptions($type);

        $this->assertNull($options['cursor']);
        $this->assertSame(12, $options['limit']);
    }

    public function testExplicitCursor(): void
    {
        $type = $this->createType(new Request());
        $options = $this->resolveOptions($type, [
            'cursor' => '01JABC',
        ]);

        $this->assertSame('01JABC', $options['cursor']);
    }

    public function testReadsCursorFromRequest(): void
    {
        $request = new Request(['cursor' => '01JREQ']);
        $type = $this->createType($request);
        $options = $this->resolveOptions($type);

        $this->assertSame('01JREQ', $options['cursor']);
    }

    public function testExplicitValueOverridesRequest(): void
    {
        $request = new Request(['cursor' => '01JREQ']);
        $type = $this->createType($request);
        $options = $this->resolveOptions($type, [
            'cursor' => '01JEXPLICIT',
        ]);

        $this->assertSame('01JEXPLICIT', $options['cursor']);
    }

    public function testNoRequestReturnsNull(): void
    {
        $type = $this->createType();
        $options = $this->resolveOptions($type);

        $this->assertNull($options['cursor']);
    }

    public function testBuildPaginationSetsBuilderValues(): void
    {
        $type = $this->createType(new Request());

        $builder = $this->createMock(PaginationConfigBuilder::class);
        $builder->expects($this->once())->method('setLimit')->with(20)->willReturnSelf();
        $builder->expects($this->once())->method('setExtended')->with(false)->willReturnSelf();
        $builder->expects($this->once())->method('setCursor')->with(true)->willReturnSelf();
        $builder->expects($this->once())->method('setPosition')->with(0)->willReturnSelf();

        $type->buildPagination($builder, ['limit' => 20]);
    }

    public function testBuildViewWithNullCursors(): void
    {
        $type = $this->createType(new Request());
        $view = new PaginationView();

        $pagination = $this->createStub(CursorPaginationInterface::class);
        $pagination->method('getNextCursor')->willReturn(null);
        $pagination->method('getPreviousCursor')->willReturn(null);

        $type->buildView($view, $pagination, [
            'parameter' => 'cursor',
            'cursor' => '01JABC',
            'limit' => 20,
        ]);

        $this->assertSame('cursor', $view->vars['parameter']);
        $this->assertSame('01JABC', $view->vars['cursor']);
        $this->assertSame(20, $view->vars['limit']);
        $this->assertNull($view->vars['next']);
        $this->assertNull($view->vars['previous']);
    }

    public function testBuildViewWithCursors(): void
    {
        $type = $this->createType(new Request());
        $view = new PaginationView();

        $pagination = $this->createStub(CursorPaginationInterface::class);
        $pagination->method('getNextCursor')->willReturn('01JNEXT');
        $pagination->method('getPreviousCursor')->willReturn(null);

        $type->buildView($view, $pagination, [
            'parameter' => 'cursor',
            'cursor' => '01JABC',
            'limit' => 10,
        ]);

        $this->assertSame('01JNEXT', $view->vars['next']);
        $this->assertNull($view->vars['previous']);
    }

    public function testInvalidLimitThrows(): void
    {
        $type = $this->createType(new Request());

        $this->expectException(\ChamberOrchestra\PaginationBundle\Exception\InvalidOptionsException::class);
        $this->resolveOptions($type, ['limit' => 0]);
    }
}
