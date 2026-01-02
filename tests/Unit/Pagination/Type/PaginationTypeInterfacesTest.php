<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination\Type;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilderInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\PaginationTypeInterface;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PaginationTypeInterfacesTest extends TestCase
{
    public function testInterfaceCanBeImplemented(): void
    {
        $type = new class() implements PaginationTypeInterface {
            public function configureOptions(OptionsResolver $resolver): void
            {
            }

            public function buildPagination(PaginationConfigBuilderInterface $builder, array $options): void
            {
            }

            public function buildView(PaginationView $view, PaginationInterface $pagination, array $options): void
            {
            }

            public function finishView(PaginationView $view, PaginationInterface $pagination, array $options): void
            {
            }
        };

        $this->assertInstanceOf(PaginationTypeInterface::class, $type);
    }
}
