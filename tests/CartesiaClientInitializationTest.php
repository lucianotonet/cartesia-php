<?php

namespace LucianoTonet\CartesiaPHP\Tests;

use LucianoTonet\CartesiaPHP\CartesiaClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Exception\ClientException;
use Dotenv\Dotenv;

class CartesiaClientInitializationTest extends TestCase
{
    protected function setUp(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . "\..", '.env');
        $dotenv->load();
    }

    public function testClientInitialization()
    {
        $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
        $this->assertInstanceOf(CartesiaClient::class, $client);
    }
}
