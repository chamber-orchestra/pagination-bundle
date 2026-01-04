<?php

declare(strict_types=1);

namespace Tests\Unit\Filter;

use ChamberOrchestra\PaginationBundle\Filter\FilterBuilder;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;

final class FilterBuilderTest extends TestCase
{
    public function testCreateWithFormReturnsEmptyCriteriaWhenInvalid(): void
    {
        $form = $this->createStub(FormInterface::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(false);

        $builder = new FilterBuilder();

        $criteria = $builder->createWithForm($form);

        $this->assertInstanceOf(Criteria::class, $criteria);
        $this->assertNull($criteria->getWhereExpression());
    }

    public function testCreateWithFormUsesDataWhenValid(): void
    {
        $form = $this->createStub(FormInterface::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn(['name' => 'john']);

        $builder = new FilterBuilder();

        $criteria = $builder->createWithForm($form);

        $expression = $criteria->getWhereExpression();
        $this->assertNotNull($expression);
        $this->assertInstanceOf(Comparison::class, $expression);
    }

    public function testBuildSupportsMappingsAndOperators(): void
    {
        $builder = new FilterBuilder();

        $criteria = $builder->createWithArray([
            'name' => '^Jo',
            'status' => 'active$',
            'roles' => ['admin', 'user'],
            'enabled' => true,
        ], [
            'enabled' => function (Criteria $criteria, $value): void {
                $criteria->andWhere(Criteria::expr()->eq('flagged', $value));
            },
        ]);

        $expression = $criteria->getWhereExpression();
        $this->assertNotNull($expression);
        $this->assertInstanceOf(CompositeExpression::class, $expression);

        $operators = [];
        $stack = $expression->getExpressionList();
        while ($stack !== []) {
            $expr = array_pop($stack);
            if ($expr instanceof Comparison) {
                $operators[] = $expr->getOperator();
                continue;
            }

            if ($expr instanceof CompositeExpression) {
                $stack = array_merge($stack, $expr->getExpressionList());
            }
        }
        sort($operators);

        $expected = [Comparison::ENDS_WITH, Comparison::EQ, Comparison::IN, Comparison::STARTS_WITH];
        sort($expected);

        $this->assertSame($expected, $operators);
    }

    public function testBuildRejectsInvalidMapping(): void
    {
        $builder = new FilterBuilder();

        $this->expectException(\TypeError::class);
        $builder->createWithArray(['status' => 'active'], ['status' => 'nope']);
    }

    public function testCreateWithArrayUsesMappingCallback(): void
    {
        $builder = new FilterBuilder();

        $criteria = $builder->createWithArray([
            'enabled' => true,
            'name' => 'john',
        ], [
            'enabled' => function (Criteria $criteria, $value, array $data): void {
                $criteria->andWhere(Criteria::expr()->eq('flagged', $value));
                $criteria->andWhere(Criteria::expr()->eq('name', $data['name']));
            },
        ]);

        $expression = $criteria->getWhereExpression();
        $this->assertNotNull($expression);

        $comparisons = $this->collectComparisons($expression);

        $fields = [];
        foreach ($comparisons as $comparison) {
            $fields[$comparison->getField()][] = $comparison->getValue()->getValue();
        }

        $this->assertSame([true], $fields['flagged']);
    }

    public function testCreateWithArrayHandlesMixedOperators(): void
    {
        $builder = new FilterBuilder();

        $criteria = $builder->createWithArray([
            'name' => '^Jo',
            'status' => 'active$',
            'title' => 'middle',
            'roles' => new \ArrayObject(['admin', 'user']),
        ]);

        $expression = $criteria->getWhereExpression();
        $this->assertNotNull($expression);

        $operators = [];
        foreach ($this->collectComparisons($expression) as $comparison) {
            $operators[] = $comparison->getOperator();
        }
        sort($operators);

        $expected = [Comparison::CONTAINS, Comparison::ENDS_WITH, Comparison::IN, Comparison::STARTS_WITH];
        sort($expected);

        $this->assertSame($expected, $operators);
    }

    /**
     * @return Comparison[]
     */
    private function collectComparisons(mixed $expression): array
    {
        $comparisons = [];
        $stack = [$expression];

        while ($stack !== []) {
            $expr = array_pop($stack);
            if ($expr instanceof Comparison) {
                $comparisons[] = $expr;
                continue;
            }

            if ($expr instanceof CompositeExpression) {
                foreach ($expr->getExpressionList() as $child) {
                    $stack[] = $child;
                }
            }
        }

        return $comparisons;
    }
}
