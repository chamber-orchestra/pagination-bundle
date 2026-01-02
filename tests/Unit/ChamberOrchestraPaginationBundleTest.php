<?php

declare(strict_types=1);

namespace Tests\Unit;

use ChamberOrchestra\PaginationBundle\ChamberOrchestraPaginationBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class ChamberOrchestraPaginationBundleTest extends TestCase
{
    public function testBundleExtendsSymfonyBundle(): void
    {
        $bundle = new ChamberOrchestraPaginationBundle();

        $this->assertInstanceOf(Bundle::class, $bundle);
    }
}
