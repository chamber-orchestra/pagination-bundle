<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination\Type;

use ChamberOrchestra\PaginationBundle\Pagination\CursorPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilder;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CursorType implements PaginationTypeInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'parameter' => 'cursor',
            'cursor' => null,
            'limit' => 12,
        ]);

        $resolver->setAllowedTypes('parameter', 'string');
        $resolver->setAllowedTypes('cursor', ['string', 'null']);
        $resolver->setAllowedTypes('limit', 'int');
        $resolver->setNormalizer('limit', Normalizer\LimitNormalizer::normalize(...));

        $requestStack = $this->requestStack;

        $resolver->setNormalizer('cursor', function (Options $options, ?string $value) use ($requestStack): ?string {
            if (null !== $value) {
                return $value;
            }

            $request = $requestStack->getMainRequest();

            /** @var string $parameter */
            $parameter = $options['parameter'];

            return $request?->query->get($parameter);
        });
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildPagination(PaginationConfigBuilder $builder, array $options): void
    {
        /** @var int $limit */
        $limit = $options['limit'];
        $builder
            ->setLimit($limit)
            ->setExtended(false)
            ->setCursor(true)
            ->setPosition(0); // unused by CursorPagination (reads cursor from options instead)
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildView(PaginationView $view, PaginationInterface $pagination, array $options): void
    {
        if (!$pagination instanceof CursorPaginationInterface) {
            throw new \LogicException('CursorType requires a CursorPaginationInterface instance.');
        }

        $view->vars = [
            'parameter' => $options['parameter'],
            'cursor' => $options['cursor'],
            'limit' => $options['limit'],
            'next' => $pagination->getNextCursor(),
            'previous' => $pagination->getPreviousCursor(),
        ];
    }
}
