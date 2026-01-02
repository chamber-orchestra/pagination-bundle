<?php

declare(strict_types=1);

namespace Tests\Integrational\Filter;

use ChamberOrchestra\PaginationBundle\Filter\FilterBuilder;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class FilterBuilderIntegrationTest extends KernelTestCase
{
    public function testBuildsCriteriaWithOperators(): void
    {
        self::bootKernel();

        $builder = new FilterBuilder();
        $criteria = $builder->createWithArray([
            'name' => '^Jo',
            'tags' => ['a', 'b'],
        ]);

        $this->assertInstanceOf(CompositeExpression::class, $criteria->getWhereExpression());
    }
}
