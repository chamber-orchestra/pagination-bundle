<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autoconfigure()
        ->autowire()
        ->private();

    $services->instanceof(ChamberOrchestra\PaginationBundle\Paginator\PaginatorInterface::class)
        ->tag('pagination.paginator');

    $services->instanceof(ChamberOrchestra\PaginationBundle\Pagination\Type\PaginationTypeInterface::class)
        ->tag('pagination.type');

    $services->load('ChamberOrchestra\\PaginationBundle\\', __DIR__.'/../../')
        ->exclude(__DIR__.'/../../{DependencyInjection,Resources,ExceptionInterface,Events,Doctrine,Paginator/CursorQueryPaginator.php}');

    $services->set(ChamberOrchestra\PaginationBundle\Paging::class)
        ->lazy();

    $services->set(ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory::class)
        ->public();

    $services->set(ChamberOrchestra\PaginationBundle\Paginator\PaginatorRegistry::class)
        ->arg('$paginators', tagged_iterator('pagination.paginator'));

    $services->set(ChamberOrchestra\PaginationBundle\Pagination\PaginationRegistry::class)
        ->arg('$types', tagged_locator('pagination.type'));

    if (\class_exists(Doctrine\ORM\QueryBuilder::class)) {
        $services->set(ChamberOrchestra\PaginationBundle\Paginator\CursorQueryPaginator::class)
            ->tag('pagination.paginator', ['priority' => 10]);

        $services->set(ChamberOrchestra\PaginationBundle\Doctrine\Cursor\CursorFieldResolver::class);

        $services->set(ChamberOrchestra\PaginationBundle\Doctrine\Cursor\CursorFieldPaging::class)
            ->decorate(ChamberOrchestra\PaginationBundle\Paging::class)
            ->arg('$inner', service('.inner'));
    }
};
