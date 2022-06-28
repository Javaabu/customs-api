<?php

namespace Javaabu\Customs\Tests;

use Orchestra\Testbench\TestCase;
use Javaabu\Customs\CustomsServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [CustomsServiceProvider::class];
    }

    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
