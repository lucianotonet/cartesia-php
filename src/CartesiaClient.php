<?php

namespace LucianoTonet\CartesiaPHP;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CartesiaClient
 * 
 * This class provides robust methods to interact with the Cartesia API, allowing operations such as cloning, deleting, and updating voices.
 * 
 * @package LucianoTonet\CartesiaPHP
 */
class CartesiaClient
{
    private string $apiKey;
    private string $baseUrl;
    private Client $client;

    // Constants for valid experimental parameter values
    public const EMOTION_HAPPY = 'happy';
    public const EMOTION_SAD = 'sad';
    public const CURIOSITY_LOW = 'low';
    public const CURIOSITY_HIGH = 'high';
    public const SPEED_SLOWEST = 'slowest';
    public const SPEED_SLOW = 'slow';
    public const SPEED_NORMAL = 'normal';
    public const SPEED_FAST = 'fast';
    public const SPEED_FASTEST = 'fastest';

    /**
     * CartesiaClient constructor.
     *
     * @param string|null $apiKey API key for authentication.
     * @param array $options Additional options for client configuration.
     * @throws CartesiaClientException If the API key is not set or options are invalid.
     */
    public function __construct(?string $apiKey = null, array $options = [])
    {
        $this->apiKey = $apiKey ?? $_ENV['CARTESIA_API_KEY'];

        if (is_null($this->apiKey)) {
            throw new CartesiaClientException("The API key is not set.");
        }

        $this->baseUrl = $options['baseUrl'] ?? $_ENV['CARTESIA_API_BASE'] ?? 'https://api.cartesia.ai';

        // Validate base URL
        if (!filter_var($this->baseUrl, FILTER_VALIDATE_URL)) {
            throw new CartesiaClientException("The base URL is invalid.");
        }

        $this->client = new Client(['base_uri' => $this->baseUrl]);
    }

    /**
     * Clones a voice clip.
     *
     * @param array $body Data of the voice clip to be cloned.
     * @return ResponseInterface     
     */
    public function cloneVoiceClip(array $body): ResponseInterface
    {
        $this->validateCloneBody($body);
        return $this->makeRequest('POST', '/voices/clone/clip', ['multipart' => [['name' => 'clip', 'contents' => $body['clip']]]]);
    }

    /**
     * Deletes a voice by ID.
     *
     * @param string $voiceId ID of the voice to be deleted.
     * @return ResponseInterface
     */
    public function deleteVoice(string $voiceId): ResponseInterface
    {
        return $this->makeRequest('DELETE', "/voices/{$voiceId}", []);
    }

    /**
     * Updates a voice by ID.
     *
     * @param string $voiceId ID of the voice to be updated.
     * @param array $body Data to update the voice. Possible values include:
     *                    - 'id' (string): ID of the voice to be updated. This field is required.
     *                    - 'name' (string): New name of the voice. This field is required.
     *                    - 'description' (string): New description of the voice. This field is required.
     *                    - 'is_public' (bool): Defines whether the voice is public or not. (optional, default is false)
     *                    - 'language' (string): Language code of the voice (e.g., 'pt', 'en'). (optional)
     * @return ResponseInterface
     * @throws \InvalidArgumentException If the body parameters are invalid.
     */
    public function updateVoice(string $voiceId, array $body): ResponseInterface
    {        
        $this->validateUpdateBody($body);
        return $this->makeRequest('PATCH', "/voices/{$voiceId}", ['json' => $body]);
    }

    /**
     * Creates a new voice.
     *
     * @param array $body Data to create the new voice. Possible values include:
     *                    - 'name' (string): Name of the new voice. This field is required.
     *                    - 'description' (string): Description of the new voice. This field is required.
     *                    - 'embedding' (array): Array of numbers for the embedding. This field is required.
     * @return ResponseInterface
     * @throws \InvalidArgumentException If the body parameters are invalid.
     */
    public function createVoice(array $body): ResponseInterface
    {
        $this->validateCreateBody($body);
        return $this->makeRequest('POST', '/voices', ['json' => $body]);
    }

    /**
     * Makes a request to the API.
     *
     * @param string $method HTTP method to be used.
     * @param string $uri URI of the request.
     * @param array $options Additional options for the request.
     * @return ResponseInterface
     * @throws GuzzleException If an error occurs during the request.
     * @throws CartesiaClientException If the response indicates an error.
     */
    public function makeRequest(string $method, string $uri, array $options = []): ResponseInterface
    {
        $defaultHeaders = [
            'X-API-Key' => $this->apiKey,
            'Accept' => 'application/json',
            'Cartesia-Version' => '2024-06-10', // Ensure the version is correct
            'Sec-WebSocket-Key' => base64_encode(random_bytes(16)), // Adds the Sec-WebSocket-Key header
        ];

        $options['headers'] = array_merge($defaultHeaders, $options['headers'] ?? []);

        try {
            $response = $this->client->request($method, $uri, $options);
            return $response;
        } catch (GuzzleException $e) {
            throw new CartesiaClientException("The request failed: " . $e->getMessage(), $e->getCode(), $e);
        } catch (\InvalidArgumentException $e) {
            throw new CartesiaClientException("Invalid arguments: " . $e->getMessage(), $e->getCode(), $e);
        } catch (\Exception $e) {
            throw new CartesiaClientException("Unknown error: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Validates the parameters of the request body for voice cloning.
     *
     * @param array $body
     * @throws \InvalidArgumentException If the body parameters are invalid.
     */
    private function validateCloneBody(array $body): void
    {
        if (empty($body['clip'])) {
            throw new \InvalidArgumentException("The 'clip' field is required.");
        }

        if (!isset($body['enhance'])) {
            $body['enhance'] = true; // Set default value to true
        }
    }

    /**
     * Validates the parameters of the request body for creating a voice.
     *
     * @param array $body
     * @throws \InvalidArgumentException If the body parameters are invalid.
     */
    private function validateCreateBody(array $body): void
    {
        if (empty($body['name'])) {
            throw new \InvalidArgumentException("The name is required.");
        }

        if (empty($body['description'])) {
            throw new \InvalidArgumentException("The description is required.");
        }

        if (empty($body['embedding']) || !is_array($body['embedding'])) {
            throw new \InvalidArgumentException("The embedding is required and must be an array.");
        }
    }

    /**
     * Validates the parameters of the request body for the Stream Speech (Bytes) endpoint.
     *
     * @param array $body
     * @throws \InvalidArgumentException If the body parameters are invalid.
     */
    private function validateStreamSpeechBytesBody(array $body): void
    {
        $requiredFields = [
            'model_id' => "The model ID is required.",
            'transcript' => "The transcript text is required.",
            'voice' => "The voice is required.",
            'output_format' => "The output format is required.",
            'language' => "The language is required. Must be one of the following: en, es, fr, de, pt, zh, ja.",
        ];

        foreach ($requiredFields as $field => $errorMessage) {
            if (!isset($body[$field])) {
                throw new \InvalidArgumentException($errorMessage);
            }
        }

        if (isset($body['duration']) && !is_int($body['duration'])) {
            throw new \InvalidArgumentException("The duration must be an integer.");
        }
    }

    /**
     * Validates the parameters of the request body for the Stream Speech (SSE) endpoint.
     *
     * @param array $body
     * @throws \InvalidArgumentException If the body parameters are invalid.
     */
    private function validateStreamSpeechSSEBody(array $body): void
    {
        $requiredFields = [
            'model_id' => "The model ID is required.",
            'transcript' => "The transcript text is required.",
            'voice' => "The voice is required.",
            'output_format' => "The output format is required.",
            'language' => "The language is required. Must be one of the following: en, es, fr, de, pt, zh, ja.",
        ];

        foreach ($requiredFields as $field => $errorMessage) {
            if (!isset($body[$field])) {
                throw new \InvalidArgumentException($errorMessage);
            }
        }

        // Check if output_format.sample_rate is an integer or convert to integer
        if (isset($body['output_format']['sample_rate']) && !is_int($body['output_format']['sample_rate'])) {
            $body['output_format']['sample_rate'] = (int)$body['output_format']['sample_rate'];
        }

        // output_format.container must be 'raw' for this endpoint
        if (isset($body['output_format']['container']) && $body['output_format']['container'] !== 'raw') {
            throw new \InvalidArgumentException("Only the 'raw' format is supported for this endpoint.");
        }

        // output_format.encoding must be present and be a string
        if (!isset($body['output_format']['encoding']) || !is_string($body['output_format']['encoding'])) {
            throw new \InvalidArgumentException("The 'encoding' field is required and must be a string.");
        }
        

        // Remove 'add_timestamps' if the language is 'pt'
        if (isset($body['language']) && $body['language'] === 'pt') {
            unset($body['add_timestamps']);
        }

        if (isset($body['duration']) && !is_int($body['duration'])) {
            throw new \InvalidArgumentException("The duration must be an integer.");
        }
    }

    /**
     * Validates the parameters of the request body for voice updates.
     *
     * @param array $body
     * @throws \InvalidArgumentException If the body parameters are invalid.
     */
    private function validateUpdateBody(array $body): void
    {
        if (empty($body['name'])) {
            throw new \InvalidArgumentException("The name is required.");
        }

        if (empty($body['description'])) {
            throw new \InvalidArgumentException("The description is required.");
        }        
    }

    public function listVoices(): ResponseInterface
    {
        return $this->makeRequest('GET', '/voices', []);
    }

    public function getVoice(string $voiceId): ResponseInterface
    {
        return $this->makeRequest('GET', "/voices/{$voiceId}", []);
    }

    public function apiStatus(): ResponseInterface
    {
        return $this->makeRequest('GET', '/', []);
    }

    public function streamSpeechBytes(array $body): ResponseInterface
    {
        $this->validateStreamSpeechBytesBody($body);
        return $this->makeRequest('POST', '/tts/bytes', ['json' => $body]);
    }

    public function streamSpeechSSE(array $body): ResponseInterface
    {
        $this->validateStreamSpeechSSEBody($body);

        // Check if the output format is 'raw'
        if (isset($body['output_format']['container']) && $body['output_format']['container'] !== 'raw') {
            throw new \InvalidArgumentException("Only the 'raw' format is supported for this endpoint.");
        }

        return $this->makeRequest('POST', '/tts/sse', ['json' => $body]);
    }

    public function streamSpeechWebSocket(array $body): ResponseInterface
    {
        // Validate body parameters
        $requiredFields = [
            'context_id' => "The 'context_id' field is required.",
            'model_id' => "The 'model_id' field is required.",
            'transcript' => "The 'transcript' field is required.",
            'voice' => "The 'voice' field is required.",
            'output_format' => "The 'output_format' field is required.",
            'language' => "The 'language' field is required.",
        ];

        foreach ($requiredFields as $field => $errorMessage) {
            if (empty($body[$field])) {
                throw new \InvalidArgumentException($errorMessage);
            }
        }

        // Add necessary connection headers for WebSocket
        $body['headers'] = array_merge($body['headers'] ?? [], [
            'Connection' => 'Upgrade',
            'Upgrade' => 'websocket',
            'Sec-WebSocket-Version' => '13',
            'Sec-WebSocket-Key' => base64_encode(random_bytes(16)),
        ]);

        // Validate the 'continue' field
        if (!isset($body['continue'])) {
            $body['continue'] = false; // Set default to false
        }

        // If add_timestamps is set to true, implement logic to handle timestamps
        if (!empty($body['add_timestamps'])) {
            // TODO: Implement logic to handle response with timestamps
        }

        return $this->makeRequest('GET', '/tts/websocket', ['headers' => $body['headers'], 'json' => $body]);
    }
}
