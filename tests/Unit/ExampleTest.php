<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_basic_assertion(): void
    {
        $array = ['foo', 'bar'];

        $this->assertCount(2, $array);
        $this->assertContains('foo', $array);
    }
}
