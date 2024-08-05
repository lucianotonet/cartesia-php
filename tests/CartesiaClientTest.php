<?php

namespace LucianoTonet\CartesiaPHP\Tests;

use LucianoTonet\CartesiaPHP\CartesiaClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Exception\ClientException;
use Dotenv\Dotenv;

/**
 * Class CartesiaClientTest
 * 
 * This class contains unit tests for the CartesiaClient, ensuring that all methods
 * function as expected and handle various scenarios correctly.
 */
class CartesiaClientTest extends TestCase
{
    protected function setUp(): void
    {
        // Load environment variables from the .env file for API key and other configurations
        $dotenv = Dotenv::createImmutable(__DIR__ . "\..", '.env');
        $dotenv->load();
    }

    /**
     * Test the initialization of the CartesiaClient.
     * 
     * This test verifies that the CartesiaClient can be instantiated correctly
     * with a valid API key.
     */
    public function testClientInitialization()
    {
        $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
        $this->assertInstanceOf(CartesiaClient::class, $client);
    }

    /**
     * Test streaming speech bytes with valid data.
     * 
     * This test checks that the streamSpeechBytes method returns a 200 status code
     * when provided with valid input data.
     */
    public function testStreamSpeechBytes()
    {
        $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
        $response = $client->streamSpeechBytes([
            'model_id' => 'sonic-english',
            'transcript' => "Hello, world! I'm generating audio on Cartesia.",
            'duration' => 123,
            'voice' => [
                'mode' => 'id',
                'id' => 'a0e99841-438c-4a64-b679-ae501e7d6091',
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
            'language' => 'en'
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test listing all available voices.
     * 
     * This test verifies that the listVoices method returns a 200 status code,
     * indicating successful retrieval of voices.
     */
    public function testListVoices()
    {
        $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
        $response = $client->listVoices();
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test retrieving a specific voice by its ID.
     * 
     * This test checks that the getVoice method returns a 200 status code
     * when a valid voice ID is provided.
     */
    public function testGetVoice()
    {
        $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
        $response = $client->getVoice('a0e99841-438c-4a64-b679-ae501e7d6091');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test updating a voice by its ID.
     * 
     * This test verifies that the updateVoice method returns a 200 status code
     * when a valid voice ID and update data are provided.
     */
    public function testUpdateVoice()
    {
        $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
        $response = $client->updateVoice('a0e99841-438c-4a64-b679-ae501e7d6091', [
            'name' => 'Updated Voice Name'
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test deleting a voice by its ID.
     * 
     * This test checks that the deleteVoice method returns a 200 status code
     * when a valid voice ID is provided for deletion.
     */
    public function testDeleteVoice()
    {
        $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
        $response = $client->deleteVoice('a0e99841-438c-4a64-b679-ae501e7d6091');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test checking the API status.
     * 
     * This test verifies that the apiStatus method returns a 200 status code,
     * indicating that the API is operational.
     */
    public function testApiStatus()
    {
        $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
        $response = $client->apiStatus();
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test streaming speech via WebSocket with valid data.
     * 
     * This test checks that the streamSpeechWebSocket method returns a 200 status code
     * when provided with valid input data.
     */
    public function testStreamSpeechWebSocket()
    {
        $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
        $response = $client->streamSpeechWebSocket([
            'context_id' => 'happy-monkeys-fly',
            'model_id' => 'sonic-english',
            'transcript' => 'Olá, mundo! Esta é uma frase mais longa que demonstra a capacidade de gerar um texto mais elaborado para o teste.',
            'voice' => [
                'mode' => 'id',
                'id' => 'a0e99841-438c-4a64-b679-ae501e7d6091',
                "__experimental_controls" => [
                    "speed" => "normal",
                    "emotion" => [
                        "positivity:high",
                        "curiosity"
                    ]
                ]
            ],
            'output_format' => [
                'container' => 'raw',
                'encoding' => 'pcm_f32le',
                'sample_rate' => 44100
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test streaming speech bytes with invalid data.
     * 
     * This test expects a ClientException to be thrown when the transcript is empty,
     * which is an invalid input scenario.
     */
    public function testStreamSpeechBytesWithInvalidData()
    {
        $this->expectException(ClientException::class);

        $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
        $client->streamSpeechBytes([
            'model_id' => 'sonic-english', // Ensure this is a valid model ID
            'transcript' => '', // Invalid input: empty transcript
        ]);
    }

    /**
     * Test streaming speech bytes with an invalid API key.
     * 
     * This test expects a ClientException to be thrown when an invalid API key is used.
     */
    public function testStreamSpeechBytesWithInvalidApiKey()
    {
        $this->expectException(ClientException::class);

        $client = new CartesiaClient('invalid_api_key');
        $client->streamSpeechBytes([
            'model_id' => 'sonic-english', // Ensure this is a valid model ID
            'transcript' => 'Hello, world!',
            'voice' => [
                'mode' => 'id',
                'id' => 'a0e99841-438c-4a64-b679-ae501e7d6091'
            ],
            'output_format' => [
                'container' => 'raw',
                'encoding' => 'pcm_s16le',
                'sample_rate' => 16000
            ]
        ]);
    }

    /**
     * Test streaming speech bytes with a non-existent API key.
     * 
     * This test expects a ClientException to be thrown when a non-existent API key is used.
     */
    public function testStreamSpeechBytesWithNonExistentApiKey()
    {
        $this->expectException(ClientException::class);

        $client = new CartesiaClient($_ENV['NON_EXISTENT_API_KEY']);
        $client->streamSpeechBytes([
            'model_id' => 'sonic-english', // Ensure this is a valid model ID
            'transcript' => 'Hello, world!',
            'voice' => [
                'mode' => 'id',
                'id' => 'a0e99841-438c-4a64-b679-ae501e7d6091'
            ],
            'output_format' => [
                'container' => 'raw',
                'encoding' => 'pcm_s16le',
                'sample_rate' => 16000
            ]
        ]);
    }

    /**
     * Test cloning a voice clip.
     * 
     * This test verifies that the cloneVoiceClip method returns a 200 status code
     * when valid source and target voice IDs are provided.
     */
    public function testCloneVoiceClip()
    {
        $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
        $response = $client->cloneVoiceClip([
            'source_voice_id' => 'source_voice_id',
            'target_voice_id' => 'target_voice_id'
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test deleting a voice with a duplicate ID.
     * 
     * This test checks that the deleteVoice method returns a 200 status code
     * when a valid voice ID is provided for deletion, even if it is a duplicate.
     */
    public function testDeleteVoiceDuplicate()
    {
        $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
        $response = $client->deleteVoice('voice_id');
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test updating a voice with a duplicate ID.
     * 
     * This test verifies that the updateVoice method returns a 200 status code
     * when a valid voice ID and update data are provided, even if it is a duplicate.
     */
    public function testUpdateVoiceDuplicate()
    {
        $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
        $response = $client->updateVoice('voice_id', [
            'name' => 'New Voice Name',
            'description' => 'Updated description'
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
