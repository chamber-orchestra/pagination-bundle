<?php

declare(strict_types=1);

namespace Tests\Integrational\Exception;

use ChamberOrchestra\PaginationBundle\Exception\ExceptionInterface;
use ChamberOrchestra\PaginationBundle\Exception\InvalidArgumentException;
use ChamberOrchestra\PaginationBundle\Exception\InvalidOptionsException;
use ChamberOrchestra\PaginationBundle\Exception\LogicException;
use ChamberOrchestra\PaginationBundle\Exception\RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ExceptionIntegrationTest extends KernelTestCase
{
    public function testExceptionsImplementInterfaceInKernelContext(): void
    {
        self::bootKernel();

        $this->assertInstanceOf(ExceptionInterface::class, new InvalidArgumentException('bad'));
        $this->assertInstanceOf(ExceptionInterface::class, new InvalidOptionsException('bad'));
        $this->assertInstanceOf(ExceptionInterface::class, new LogicException('bad'));
        $this->assertInstanceOf(ExceptionInterface::class, new RuntimeException('bad'));
    }
}
