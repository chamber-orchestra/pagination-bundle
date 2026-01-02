<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use ChamberOrchestra\PaginationBundle\Exception\ExceptionInterface;
use ChamberOrchestra\PaginationBundle\Exception\InvalidArgumentException;
use ChamberOrchestra\PaginationBundle\Exception\InvalidOptionsException;
use ChamberOrchestra\PaginationBundle\Exception\LogicException;
use ChamberOrchestra\PaginationBundle\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

final class ExceptionTest extends TestCase
{
    public function testExceptionsImplementInterface(): void
    {
        $this->assertInstanceOf(ExceptionInterface::class, new InvalidArgumentException('bad'));
        $this->assertInstanceOf(ExceptionInterface::class, new InvalidOptionsException('bad'));
        $this->assertInstanceOf(ExceptionInterface::class, new LogicException('bad'));
        $this->assertInstanceOf(ExceptionInterface::class, new RuntimeException('bad'));
    }

    public function testExceptionsExtendSplExceptions(): void
    {
        $this->assertInstanceOf(\InvalidArgumentException::class, new InvalidArgumentException('bad'));
        $this->assertInstanceOf(\LogicException::class, new LogicException('bad'));
        $this->assertInstanceOf(\RuntimeException::class, new RuntimeException('bad'));
    }
}
