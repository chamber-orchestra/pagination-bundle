<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination\Type;

use ChamberOrchestra\PaginationBundle\Exception\RuntimeException;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilder;
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
            'parameter' => 'page',
            'limit' => 12, // maximum number of items per page
            'page' => null, // current page, if null request parameter will be used
            'extended' => false, // does pagination need to know the entries amount
        ]);

        $resolver->setAllowedTypes('extended', 'bool');
        $resolver->setAllowedTypes('parameter', 'string');
        $resolver->setAllowedTypes('limit', 'int');
        $resolver->setNormalizer('limit', Normalizer\LimitNormalizer::normalize(...));

        $resolver->setAllowedTypes('page', ['int', 'null']);
        $requestStack = $this->requestStack;
        $resolver->setNormalizer('page', function (Options $options, ?int $value) use ($requestStack): int {
            if (null !== $value) {
                return $value;
            }

            $request = $requestStack->getMainRequest();
            if (null === $request) {
                throw new RuntimeException('No main request available.');
            }

            /** @var string $parameter */
            $parameter = $options['parameter'];

            return \abs($request->query->getInt($parameter, 1));
        });
    }

    public function buildPagination(PaginationConfigBuilder $builder, array $options): void
    {
        /** @var int $page */
        $page = $options['page'];
        /** @var int $limit */
        $limit = $options['limit'];
        /** @var bool $extended */
        $extended = $options['extended'];

        $builder
            ->setPosition($page)
            ->setLimit($limit)
            ->setExtended($extended);
    }

    public function buildView(PaginationView $view, PaginationInterface $pagination, array $options): void
    {
        /** @var string $parameter */
        $parameter = $options['parameter'];
        /** @var int $limit */
        $limit = $options['limit'];

        /** @var array<string, mixed> $merged */
        $merged = \array_replace_recursive($view->vars, [
            'parameter' => $parameter,
            'limit' => $limit,
        ]);
        $view->vars = $merged;
    }
}
