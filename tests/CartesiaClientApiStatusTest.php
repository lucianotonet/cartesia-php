<?php

namespace LucianoTonet\CartesiaPHP\Tests;

use Dotenv\Dotenv;
use LucianoTonet\CartesiaPHP\CartesiaClient;
use PHPUnit\Framework\TestCase;

class CartesiaClientApiStatusTest extends TestCase
{
    protected function setUp(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . "\..", '.env');
        $dotenv->load();
    }

    public function testApiStatus()
    {
        $useMock = false; // Altere para true para usar a versão mockada

        if ($useMock) {
            // Carregar resposta mockada do arquivo JSON
            $mockResponse = json_decode(file_get_contents(__DIR__ . '/mock/CartesiaClientApiStatusMock.json'), true);
            $response = new \GuzzleHttp\Psr7\Response(200, [], json_encode($mockResponse));
        } else {
            // Usar a API real
            $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
            $response = $client->apiStatus();
            // Salvar a resposta em um arquivo JSON para uso futuro
            $responseBody = json_decode($response->getBody()->getContents(), true);
            file_put_contents(__DIR__ . '/mock/CartesiaClientApiStatusMock.json', json_encode($responseBody, JSON_PRETTY_PRINT));
        }

        $this->assertEquals(200, $response->getStatusCode());
    }
}