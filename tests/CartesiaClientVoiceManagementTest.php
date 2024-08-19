<?php

namespace LucianoTonet\CartesiaPHP\Tests;

use LucianoTonet\CartesiaPHP\CartesiaClient;
use LucianoTonet\CartesiaPHP\CartesiaClientException;
use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;

class CartesiaClientVoiceManagementTest extends TestCase
{
    protected function setUp(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . "\..", '.env');
        $dotenv->load();
    }

    public function testListVoices()
    {
        $useMock = true; // Altere para true para usar a versão mockada

        if ($useMock) {
            // Carregar resposta mockada do arquivo JSON
            $mockResponse = json_decode(file_get_contents(__DIR__ . '/mock/CartesiaClientVoiceManagementMock-ListVoices.json'), true);
            $response = new \GuzzleHttp\Psr7\Response(200, [], json_encode($mockResponse));
        } else {
            $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
            $response = $client->listVoices();
            // Salvar a resposta em um arquivo JSON para uso futuro
            $responseData = [
                'status_code' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => json_decode($response->getBody()->getContents(), true)
            ];
            file_put_contents(__DIR__ . '/mock/CartesiaClientVoiceManagementMock-ListVoices.json', json_encode($responseData, JSON_PRETTY_PRINT));
        }

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetVoice()
    {
        $useMock = true; // Altere para true para usar a versão mockada

        if ($useMock) {
            // Carregar resposta mockada do arquivo JSON
            $mockResponse = json_decode(file_get_contents(__DIR__ . '/mock/CartesiaClientVoiceManagementMock-GetVoice.json'), true);
            $response = new \GuzzleHttp\Psr7\Response(200, [], json_encode($mockResponse));
        } else {
            $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
            $response = $client->getVoice('a0e99841-438c-4a64-b679-ae501e7d6091');
            // Salvar a resposta em um arquivo JSON para uso futuro
            $responseData = [
                'status_code' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => json_decode($response->getBody()->getContents(), true)
            ];
            file_put_contents(__DIR__ . '/mock/CartesiaClientVoiceManagementMock-GetVoice.json', json_encode($responseData, JSON_PRETTY_PRINT));
        }

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Clone Voice (Clip)
     * Clones a voice from an audio clip uploaded as a file. The clip is uploaded using multipart/form-data with a clip field containing the audio file.
     */
    public function testCloneVoiceClip()
    {
        $useMock = false; // Altere para true para usar a versão mockada

        if ($useMock) {
            // Carregar resposta mockada do arquivo JSON
            $mockResponse = json_decode(file_get_contents(__DIR__ . '/mock/CartesiaClientVoiceManagementMock-CloneVoiceClip.json'), true);
            $response = new \GuzzleHttp\Psr7\Response(200, [], json_encode($mockResponse));
        } else {
            $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
            // Clones a voice from an audio clip uploaded as a file. O clip é carregado usando multipart/form-data com um campo clip contendo o arquivo de áudio.
            $response = $client->cloneVoiceClip([
                'clip' => fopen(__DIR__ . '/audio.ogg', 'r'),
                'enhance' => true, // Se não especificado, o padrão é true            
            ]);
            // Salvar a resposta em um arquivo JSON para uso futuro
            $responseData = [
                'status_code' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => json_decode($response->getBody()->getContents(), true)
            ];
            file_put_contents(__DIR__ . '/mock/CartesiaClientVoiceManagementMock-CloneVoiceClip.json', json_encode($responseData, JSON_PRETTY_PRINT));
        }

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Create Voice 
     * Create a new voice with a given name, description, and embedding.
     */
    public function testCreateVoice()
    {
        $useMock = true; // Altere para true para usar a versão mockada

        if ($useMock) {
            // Carregar resposta mockada do arquivo JSON
            $mockResponse = json_decode(file_get_contents(__DIR__ . '/mock/CartesiaClientVoiceManagementMock-CreateVoice.json'), true);
            $response = new \GuzzleHttp\Psr7\Response(201, [], json_encode($mockResponse));
        } else {
            $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
            // Criar uma nova voz com um nome, descrição e embedding
            $response = $client->createVoice([
                'name' => 'Voz teste',
                'description' => 'Descrição da nova voz.',
                'embedding' => array_fill(0, 192, 0.0) // Simulando um array de embedding com 192 dimensões preenchido com zeros
            ]);
            // Salvar a resposta em um arquivo JSON para uso futuro
            $responseData = [
                'status_code' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => json_decode($response->getBody()->getContents(), true)
            ];
            file_put_contents(__DIR__ . '/mock/CartesiaClientVoiceManagementMock-CreateVoice.json', json_encode($responseData, JSON_PRETTY_PRINT));
        }

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testUpdateVoice()
    {
        $useMock = true; // Altere para true para usar a versão mockada

        if ($useMock) {
            // Carregar resposta mockada do arquivo JSON
            $mockResponse = json_decode(file_get_contents(__DIR__ . '/mock/CartesiaClientVoiceManagementMock-UpdateVoice.json'), true);
            $response = new \GuzzleHttp\Psr7\Response(200, [], json_encode($mockResponse));
        } else {
            $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);

            // Primeiro, buscar as vozes clonadas
            $clonedVoicesResponse = $client->listVoices();
            $clonedVoices = json_decode($clonedVoicesResponse->getBody()->getContents(), true);

            // Procurar por uma voz que não seja pública
            $clonedVoiceId = null;
            foreach ($clonedVoices as $voice) {
                if (isset($voice['is_public']) && !$voice['is_public'] && isset($voice['name']) && $voice['name'] === 'Voz teste') {
                    $clonedVoiceId = $voice['id'];
                    break; // Encontrou a voz não pública com o nome "Voz teste", sai do loop
                }
            }

            if ($clonedVoiceId) {
                // Tentativa de atualizar a voz clonada com um ID válido
                $response = $client->updateVoice($clonedVoiceId, [
                    'name' => 'Voz teste atualizada',
                    'description' => 'Voice updated just for test.'
                ]);

                // Salvar a resposta em um arquivo JSON para uso futuro
                $responseData = [
                    'status_code' => $response->getStatusCode(),
                    'headers' => $response->getHeaders(),
                    'body' => json_decode($response->getBody()->getContents(), true)
                ];
                file_put_contents(__DIR__ . '/mock/CartesiaClientVoiceManagementMock-UpdateVoice.json', json_encode($responseData, JSON_PRETTY_PRINT));
            } else {
                $this->fail("Nenhuma voz clonada encontrada para atualizar.");
            }
        }

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteVoice()
    {
        $useMock = true; // Altere para true para usar a versão mockada

        if ($useMock) {
            // Carregar resposta mockada do arquivo JSON
            $mockResponse = json_decode(file_get_contents(__DIR__ . '/mock/CartesiaClientVoiceManagementMock-DeleteVoice.json'), true);
            $response = new \GuzzleHttp\Psr7\Response(204, [], json_encode($mockResponse));
        } else {
            $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);

            // Primeiro, buscar as vozes clonadas
            $clonedVoicesResponse = $client->listVoices();
            $clonedVoices = json_decode($clonedVoicesResponse->getBody()->getContents(), true);

            // Procurar por uma voz que não seja pública
            $clonedVoiceId = null;
            foreach ($clonedVoices as $voice) {
                if (isset($voice['is_public']) && !$voice['is_public'] && isset($voice['name']) && $voice['name'] === 'Voz teste atualizada') {
                    $clonedVoiceId = $voice['id'];
                    break; // Encontrou a voz não pública com o nome "Voz teste atualizada", sai do loop
                }
            }

            if ($clonedVoiceId) {
                // Tentativa de deletar a voz clonada com um ID válido
                $response = $client->deleteVoice($clonedVoiceId);

                // Salvar a resposta em um arquivo JSON para uso futuro
                $responseData = [
                    'status_code' => $response->getStatusCode(),
                    'headers' => $response->getHeaders(),
                    'body' => json_decode($response->getBody()->getContents(), true)
                ];
                file_put_contents(__DIR__ . '/mock/CartesiaClientVoiceManagementMock-DeleteVoice.json', json_encode($responseData, JSON_PRETTY_PRINT));
            } else {
                $this->fail("Nenhuma voz clonada encontrada para deletar.");
            }
        }

        $this->assertEquals(204, $response->getStatusCode());
    }
}