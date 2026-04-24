<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class MathTest extends TestCase
{
    public function testOnePlusOne(): void
    {
        $this->assertSame(2, 1 + 1);
    }
}