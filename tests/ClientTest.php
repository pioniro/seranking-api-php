<?php

use PHPUnit\Framework\TestCase;
use Pioniro\Seranking\Client;

class ClientTest extends TestCase
{

    public function test__construct()
    {
        $client = new Client([
                'login' => 'some_login',
                'password' => 'some password'
            ]
        );
        $this->assertEquals(true, true);
    }
}
