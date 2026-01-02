<?php

declare(strict_types=1);

namespace Tests\Integrational;

use ChamberOrchestra\PaginationBundle\ChamberOrchestraPaginationBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class BundleIntegrationTest extends KernelTestCase
{
    public function testBundleIsLoadable(): void
    {
        self::bootKernel();

        $bundle = new ChamberOrchestraPaginationBundle();

        $this->assertInstanceOf(Bundle::class, $bundle);
    }
}
