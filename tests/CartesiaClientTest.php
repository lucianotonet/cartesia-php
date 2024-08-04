<?php

namespace LucianoTonet\CartesiaPHP\Tests;

use LucianoTonet\CartesiaPHP\CartesiaClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Exception\ClientException;

class CartesiaClientTest extends TestCase
{
    public function testClientInitialization()
    {
        $client = new CartesiaClient('your_api_key');
        $this->assertInstanceOf(CartesiaClient::class, $client);
    }

    public function testStreamSpeechBytes()
    {
        $client = new CartesiaClient('your_api_key');
        $response = $client->streamSpeechBytes([
            'model_id' => 'test_model',
            'transcript' => 'Hello, world!',
            'voice' => [
                'mode' => 'id',
                'id' => 'test_voice'
            ],
            'output_format' => [
                'container' => 'raw',
                'encoding' => 'pcm_s16le',
                'sample_rate' => 16000
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testListVoices()
    {
        $client = new CartesiaClient('your_api_key');
        $response = $client->listVoices();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetVoice()
    {
        $client = new CartesiaClient('your_api_key');
        $response = $client->getVoice('test_voice_id');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdateVoice()
    {
        $client = new CartesiaClient('your_api_key');
        $response = $client->updateVoice('test_voice_id', [
            'name' => 'Updated Voice Name'
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteVoice()
    {
        $client = new CartesiaClient('your_api_key');
        $response = $client->deleteVoice('test_voice_id');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testApiStatus()
    {
        $client = new CartesiaClient('your_api_key');
        $response = $client->apiStatus();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testStreamSpeechWebSocket()
    {
        $client = new CartesiaClient('your_api_key');
        $response = $client->streamSpeechWebSocket([
            'model_id' => 'sonic-english',
            'transcript' => 'Hello, world!',
            'voice' => [
                'mode' => 'id',
                'id' => 'a0e99841-438c-4a64-b679-ae501e7d6091'
            ],
            'output_format' => [
                'container' => 'raw',
                'encoding' => 'pcm_f32le',
                'sample_rate' => 44100
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testStreamSpeechBytesWithInvalidData()
    {
        $this->expectException(ClientException::class);

        $client = new CartesiaClient('your_api_key');
        $client->streamSpeechBytes([
            'model_id' => '',
            'transcript' => '',
        ]);
    }

    public function testStreamSpeechBytesWithError()
    {
        $this->expectException(ClientException::class);

        $client = new CartesiaClient('invalid_api_key');
        $client->streamSpeechBytes([
            'model_id' => 'test_model',
            'transcript' => 'Hello, world!',
            'voice' => [
                'mode' => 'id',
                'id' => 'test_voice'
            ],
            'output_format' => [
                'container' => 'raw',
                'encoding' => 'pcm_s16le',
                'sample_rate' => 16000
            ]
        ]);
    }
}

public function testCloneVoiceClip()
{
    $client = new CartesiaClient('your_api_key');
    $response = $client->cloneVoiceClip([
        'source_voice_id' => 'source_voice_id',
        'target_voice_id' => 'target_voice_id'
    ]);
    $this->assertEquals(200, $response->getStatusCode());
}

public function testDeleteVoice()
{
    $client = new CartesiaClient('your_api_key');
    $response = $client->deleteVoice('voice_id');
    $this->assertEquals(200, $response->getStatusCode());
}

public function testUpdateVoice()
{
    $client = new CartesiaClient('your_api_key');
    $response = $client->updateVoice('voice_id', [
        'name' => 'New Voice Name',
        'description' => 'Updated description'
    ]);
    $this->assertEquals(200, $response->getStatusCode());
}
