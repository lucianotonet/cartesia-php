<?php

namespace LucianoTonet\CartesiaPHP;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CartesiaClient
 * 
 * Esta classe fornece métodos robustos para interagir com a API Cartesia, permitindo operações como clonagem, exclusão e atualização de vozes.
 * 
 * @package LucianoTonet\CartesiaPHP
 */
class CartesiaClient
{
    private string $apiKey;
    private string $baseUrl;
    private array $options;
    private Client $client;

    // Constantes para valores válidos de parâmetros experimentais
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
     * Construtor do CartesiaClient.
     *
     * @param string|null $apiKey Chave da API para autenticação.
     * @param array $options Opções adicionais para configuração do cliente.
     * @throws CartesiaClientException Se a chave da API não estiver definida ou as opções forem inválidas.
     */
    public function __construct(?string $apiKey = null, array $options = [])
    {
        $this->apiKey = $apiKey ?? $_ENV['CARTESIA_API_KEY'];

        if (is_null($this->apiKey)) {
            throw new CartesiaClientException("A chave da API não está definida.");
        }

        $this->options = $options;
        $this->baseUrl = $options['baseUrl'] ?? $_ENV['CARTESIA_API_BASE'] ?? 'https://api.cartesia.ai';

        // Validar opções
        if (!filter_var($this->baseUrl, FILTER_VALIDATE_URL)) {
            throw new CartesiaClientException("A URL base é inválida.");
        }

        $this->client = new Client(['base_uri' => $this->baseUrl]);
    }

    /**
     * Clona um clipe de voz.
     *
     * @param array $body Dados do clipe de voz a ser clonado.
     * @return ResponseInterface
     * @throws \InvalidArgumentException Se os parâmetros do corpo forem inválidos.
     */
    public function cloneVoiceClip(array $body): ResponseInterface
    {
        $this->validateBody($body);
        return $this->makeRequest('POST', '/voice-clone', ['json' => $body]);
    }

    /**
     * Deleta uma voz pelo ID.
     *
     * @param string $voiceId ID da voz a ser deletada.
     * @return ResponseInterface
     */
    public function deleteVoice(string $voiceId): ResponseInterface
    {
        return $this->makeRequest('DELETE', "/voices/{$voiceId}", []);
    }

    /**
     * Atualiza uma voz pelo ID.
     *
     * @param string $voiceId ID da voz a ser atualizada.
     * @param array $body Dados para atualizar a voz.
     * @return ResponseInterface
     * @throws \InvalidArgumentException Se os parâmetros do corpo forem inválidos.
     */
    public function updateVoice(string $voiceId, array $body): ResponseInterface
    {
        $this->validateBody($body);
        return $this->makeRequest('PATCH', "/voices/{$voiceId}", ['json' => $body]);
    }

    /**
     * Faz uma requisição para a API.
     *
     * @param string $method Método HTTP a ser utilizado.
     * @param string $uri URI da requisição.
     * @param array $options Opções adicionais para a requisição.
     * @return ResponseInterface
     * @throws GuzzleException Se ocorrer um erro durante a requisição.
     * @throws CartesiaClientException Se a resposta indicar um erro.
     */
    public function makeRequest(string $method, string $uri, array $options = []): ResponseInterface
    {
        $options['headers'] = array_merge([
            'X-API-Key' => $this->apiKey,
            'Accept' => 'application/json',
            'Cartesia-Version' => '2024-06-10', // Garantir que a versão esteja correta
            'Sec-WebSocket-Key' => base64_encode(random_bytes(16)), // Adiciona o cabeçalho Sec-WebSocket-Key
        ], $options['headers'] ?? []);

        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (GuzzleException $e) {
            throw new CartesiaClientException("A requisição falhou: " . $e->getMessage());
        }

        // Verifica se o status da resposta é 401 e lança uma exceção específica
        if ($response->getStatusCode() === 401) {
            throw new CartesiaClientException("Erro 401: Acesso não autorizado. Verifique sua chave da API.");
        }

        $this->handleErrors($response);

        return $response;
    }

    /**
     * Valida os parâmetros do corpo da requisição.
     *
     * @param array $body
     * @throws \InvalidArgumentException Se os parâmetros do corpo forem inválidos.
     */
    private function validateBody(array $body): void
    {
        $requiredFields = [
            'context_id' => "O ID do contexto é obrigatório.",
            'voice.id' => "O ID da voz é obrigatório.",
            'model_id' => "O ID do modelo é obrigatório.",
            'transcript' => "O texto do transcript é obrigatório.",
            'duration' => "A duração é obrigatória.",
            'output_format.container' => "O formato de saída é inválido. Deve ser 'raw' ou 'mp3'.",
            'output_format.encoding' => "A codificação de saída é inválida. Deve ser 'pcm_s16le', 'mp3' ou 'pcm_f32le'.",
            'output_format.sample_rate' => "A taxa de amostragem deve ser 8000, 16000 ou 44100.",
            'language' => "O idioma é obrigatório. Deve ser um dos seguintes: en, es, fr, de, pt, zh, ja.",
        ];

        foreach ($requiredFields as $field => $errorMessage) {
            $keys = explode('.', $field);
            $value = $body;

            foreach ($keys as $key) {
                if (isset($value[$key])) {
                    $value = $value[$key];
                } else {
                    throw new \InvalidArgumentException($errorMessage);
                }
            }

            if (in_array($field, ['output_format.container', 'output_format.encoding'])) {
                if (!in_array($value, ['raw', 'mp3', 'pcm_s16le', 'mp3', 'pcm_f32le'], true)) {
                    throw new \InvalidArgumentException($errorMessage);
                }
            }

            if ($field === 'output_format.sample_rate' && !in_array($value, [8000, 16000, 44100], true)) {
                throw new \InvalidArgumentException($errorMessage);
            }
        }

        // Adiciona o cabeçalho de conexão necessário para WebSocket
        if (!isset($body['headers'])) {
            $body['headers'] = [];
        }
        
        $body['headers']['Connection'] = 'Upgrade';
        $body['headers']['Upgrade'] = 'websocket';
    }

    /**
     * Trata erros da resposta da API.
     *
     * @param ResponseInterface $response
     * @throws CartesiaClientException Se a resposta indicar um erro.
     */
    private function handleErrors(ResponseInterface $response): void
    {
        if ($response->getStatusCode() >= 400) {
            $body = json_decode((string) $response->getBody(), true);
            throw new CartesiaClientException($body['message'] ?? 'Erro desconhecido');
        }
    }

    // Métodos adicionais implementados conforme os testes
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
        $this->validateBody($body);
        return $this->makeRequest('POST', '/tts/bytes', ['json' => $body]);
    }

    public function streamSpeechSSE(array $body): ResponseInterface
    {
        $this->validateBody($body);
        return $this->makeRequest('POST', '/tts/sse', ['json' => $body]);
    }

    public function streamSpeechWebSocket(array $body): ResponseInterface
    {
        $this->validateBody($body);

        // Adiciona o cabeçalho de conexão necessário para WebSocket
        if (!isset($body['headers'])) {
            $body['headers'] = [];
        }
        
        $body['headers']['Connection'] = 'Upgrade';
        $body['headers']['Upgrade'] = 'websocket';
        $body['headers']['Sec-WebSocket-Version'] = '13'; // Adiciona o cabeçalho Sec-WebSocket-Version
        $body['headers']['Sec-WebSocket-Key'] = base64_encode(random_bytes(16)); // Adiciona o cabeçalho Sec-WebSocket-Key

        return $this->makeRequest('GET', '/tts/websocket', ['headers' => $body['headers'], 'form_params' => $body]);
    }
}
