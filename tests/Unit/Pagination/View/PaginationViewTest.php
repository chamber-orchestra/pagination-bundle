<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination\View;

use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use PHPUnit\Framework\TestCase;

final class PaginationViewTest extends TestCase
{
    public function testVarsDefaultToEmptyArray(): void
    {
        $view = new PaginationView();

        $this->assertSame([], $view->vars);
    }
}
