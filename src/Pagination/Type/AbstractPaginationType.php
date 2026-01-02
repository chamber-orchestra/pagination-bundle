<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination\Type;

use ChamberOrchestra\PaginationBundle\Exception\InvalidOptionsException;
use ChamberOrchestra\PaginationBundle\Exception\RuntimeException;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilderInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractPaginationType implements PaginationTypeInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'page_parameter' => 'page',
            'per_page_limit' => 12, // per page limit of entries
            'page' => null, //current page, if null request parameter will be used
            'extended' => false, // does pagination need to know the entries amount
        ]);

        $resolver->setAllowedTypes('extended', 'bool');
        $resolver->setAllowedTypes('page_parameter', 'string');
        $resolver->setAllowedTypes('per_page_limit', 'int');
        $resolver->setNormalizer('per_page_limit', function (Options $options, $value) {
            $limit = \abs((int)$value);
            if (!$limit) {
                throw new InvalidOptionsException("Parameter 'limit' must be a positive int.");
            }

            return $limit;
        });

        $resolver->setAllowedTypes('page', ['int', 'null']);
        $requestStack = $this->requestStack;
        $resolver->setNormalizer('page', function (Options $options, int|null $value) use ($requestStack): int {
            if (null !== $value) {
                return $value;
            }

            $request = $requestStack->getMainRequest();
            if (null === $request) {
                throw new RuntimeException('No master request.');
            }

            return \abs($request->query->getInt($options['page_parameter'], 1));
        });
    }

    public function buildPagination(PaginationConfigBuilderInterface $builder, array $options): void
    {
        $builder
            ->setPage($options['page'])
            ->setPerPageLimit($options['per_page_limit'])
            ->setExtended($options['extended']);
    }

    public function buildView(PaginationView $view, PaginationInterface $pagination, array $options): void
    {
    }

    public function finishView(PaginationView $view, PaginationInterface $pagination, array $options): void
    {
        $view->vars = \array_replace_recursive($view->vars, [
            'page_parameter' => $options['page_parameter'],
            'per_page_limit' => $options['per_page_limit'],
        ]);
    }
}
