<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination\Type;

use ChamberOrchestra\PaginationBundle\Exception\RuntimeException;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilder;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\AbstractPaginationType;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AbstractPaginationTypeTest extends TestCase
{
    public function testConfigureOptionsUsesRequestParameter(): void
    {
        $stack = new RequestStack();
        $stack->push(new Request(['page' => 4]));

        $type = new class($stack) extends AbstractPaginationType {
        };

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);

        $options = $resolver->resolve([]);

        $this->assertSame(4, $options['page']);
        $this->assertSame(12, $options['limit']);
    }

    public function testConfigureOptionsThrowsWithoutRequest(): void
    {
        $type = new class(new RequestStack()) extends AbstractPaginationType {
        };

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);

        $this->expectException(RuntimeException::class);
        $resolver->resolve(['page' => null]);
    }

    public function testBuildPaginationSetsBuilderValues(): void
    {
        $type = new class(new RequestStack()) extends AbstractPaginationType {
            public function buildView(PaginationView $view, PaginationInterface $pagination, array $options): void
            {
            }
        };

        $builder = $this->createMock(PaginationConfigBuilder::class);
        $builder->expects($this->once())->method('setPosition')->with(2)->willReturnSelf();
        $builder->expects($this->once())->method('setLimit')->with(5)->willReturnSelf();
        $builder->expects($this->once())->method('setExtended')->with(false)->willReturnSelf();

        $type->buildPagination($builder, [
            'page' => 2,
            'limit' => 5,
            'extended' => false,
        ]);
    }

    public function testConfigureOptionsRejectsInvalidLimit(): void
    {
        $stack = new RequestStack();
        $stack->push(new Request(['page' => 1]));

        $type = new class($stack) extends AbstractPaginationType {
        };

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);

        $this->expectException(\ChamberOrchestra\PaginationBundle\Exception\InvalidOptionsException::class);
        $resolver->resolve(['limit' => 0]);
    }
}
