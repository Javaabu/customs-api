<?php

namespace Javaabu\Customs\Tests;


use Illuminate\Support\Facades\App;
use Javaabu\Customs\Customs;

class CustomsTest extends TestCase
{

    public function test_it_can_make_a_customs_instance()
    {
        $api = App::make('customs');

        $this->assertInstanceOf(Customs::class, $api);
    }
}
