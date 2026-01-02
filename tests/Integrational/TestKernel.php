<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Integrational;

use ChamberOrchestra\PaginationBundle\ChamberOrchestraPaginationBundle;
use ChamberOrchestra\ViewBundle\ChamberOrchestraViewBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new ChamberOrchestraViewBundle(),
            new ChamberOrchestraPaginationBundle(),
//            new DoctrineBundle(),
        ];
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'test_secret',
            'test' => true,
            'form' => true,
            'serializer' => ['enabled' => true],
        ]);
        $container->extension('chamber_orchestra_pagination', []);
        $container->extension('chamber_orchestra_view', []);
//        $container->extension('doctrine', [
//            'orm' => [
//                'enable_native_lazy_objects' => true,
//            ],
//        ]);
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__, 2);
    }
}
