<?php

namespace LucianoTonet\CartesiaPHP\Tests;

use LucianoTonet\CartesiaPHP\CartesiaClient;
use LucianoTonet\CartesiaPHP\CartesiaClientException;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Exception\ClientException;
use Dotenv\Dotenv;

class CartesiaClientStreamSpeechSSETest extends TestCase
{
    protected function setUp(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . "\..", '.env');
        $dotenv->load();
    }

    public function testStreamSpeechSSE()
    {
        $useMock = true; // Altere para true para usar a versão mockada

        if ($useMock) {
            // Carregar resposta mockada do arquivo JSON
            $mockResponse = json_decode(file_get_contents(__DIR__ . '/mock/CartesiaClientStreamSpeechSSEMock-StreamSpeechSSE.json'), true);
            $response = new \GuzzleHttp\Psr7\Response(200, [], json_encode($mockResponse));
        } else {
            // Usar a API real
            $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
            $response = $client->streamSpeechSSE([
                'context_id' => 'happy-monkeys-fly',
                'model_id' => 'sonic-multilingual',
                'transcript' => "Olá, mundo! Estou gerando áudio na Cartesia.",
                'duration' => 123,
                'voice' => [
                    'mode' => 'id',
                    'id' => '2b568345-1d48-4047-b25f-7baccf842eb0',
                    '__experimental_controls' => [
                        'speed' => 'normal',
                        'emotion' => [
                            'positivity:high',
                            'curiosity'
                        ]
                    ]
                ],
                'output_format' => [
                    'container' => 'raw',
                    'encoding' => 'pcm_s16le',
                    'sample_rate' => 8000
                ],
                'language' => 'pt' // Alterado para português
            ]);
            
            // Salvar a resposta completa em um arquivo JSON para uso futuro
            $responseData = [
                'status_code' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => json_decode($response->getBody()->getContents(), true)
            ];
            file_put_contents(__DIR__ . '/mock/CartesiaClientStreamSpeechSSEMock-StreamSpeechSSE.json', json_encode($responseData, JSON_PRETTY_PRINT));
        }

        // Verificar se a resposta é um evento SSE
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/event-stream', $response->getHeaderLine('Content-Type'));
    }
}