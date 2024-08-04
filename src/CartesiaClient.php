<?php

namespace LucianoTonet\CartesiaPHP;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CartesiaClient
 * 
 * Esta classe fornece métodos para interagir com a API Cartesia.
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

    public function __construct(?string $apiKey = null, array $options = [])
    {
        $this->apiKey = $apiKey ?? $_ENV['CARTESIA_API_KEY'] ?? null;

        if (!$this->apiKey) {
            throw new \Exception("Chave da API não definida.");
        }
        
        $this->options = $options;
        $this->baseUrl = $options['baseUrl'] ?? $_ENV['CARTESIA_API_BASE'] ?? 'https://api.cartesia.ai/v1';
        $this->client = new Client(['base_uri' => $this->baseUrl]);
    }

    /**
     * Clona um clipe de voz.
     *
     * @param array $body Dados do clipe de voz a ser clonado.
     * @return ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function cloneVoiceClip(array $body): ResponseInterface
    {
        // Validação dos parâmetros do corpo
        $this->validateBody($body);
        return $this->makeRequest('POST', '/voice-clone', [
            'json' => $body
        ]);
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
     */
    public function updateVoice(string $voiceId, array $body): ResponseInterface
    {
        $this->validateBody($body); // Adicionada validação antes da atualização
        return $this->makeRequest('PATCH', "/voices/{$voiceId}", [
            'json' => $body
        ]);
    }

    /**
     * Define os controles experimentais para velocidade e emoção.
     *
     * @param array $controls Controles experimentais que incluem 'speed' e 'emotion'.
     * @return static
     * @throws \InvalidArgumentException
     */
    public function setExperimentalControls(array $controls): self
    {
        if (isset($controls['speed']) && !in_array($controls['speed'], [
            self::SPEED_SLOWEST, 
            self::SPEED_SLOW, 
            self::SPEED_NORMAL, 
            self::SPEED_FAST, 
            self::SPEED_FASTEST
        ], true)) {
            throw new \InvalidArgumentException("Velocidade inválida.");
        }

        if (isset($controls['emotion'])) {
            foreach ($controls['emotion'] as $emotion) {
                if (!preg_match('/^(anger|positivity|surprise|sadness|curiosity)(:[a-z]+)?$/', $emotion)) {
                    throw new \InvalidArgumentException("Emoção inválida: {$emotion}.");
                }
            }
        }

        $this->options['__experimental_controls'] = $controls;
        return $this;
    }

    /**
     * Faz uma requisição para a API.
     *
     * @param string $method Método HTTP a ser utilizado.
     * @param string $uri URI da requisição.
     * @param array $options Opções adicionais para a requisição.
     * @return ResponseInterface
     */
    public function makeRequest(string $method, string $uri, array $options = []): ResponseInterface
    {
        $options['headers'] = array_merge([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
            'Cartesia-Version' => '2024-06-10'
        ], $options['headers'] ?? []);

        $response = $this->client->request($method, $uri, $options);

        $this->handleErrors($response);

        return $response;
    }

    /**
     * Valida os parâmetros do corpo da requisição.
     *
     * @param array $body
     * @throws \InvalidArgumentException
     */
    private function validateBody(array $body): void
    {
        if (empty($body['voice_id'])) {
            throw new \InvalidArgumentException("O ID da voz é obrigatório.");
        }

        if (empty($body['model_id'])) {
            throw new \InvalidArgumentException("O ID do modelo é obrigatório.");
        }

        if (empty($body['transcript'])) {
            throw new \InvalidArgumentException("O texto do transcript é obrigatório.");
        }

        if (!isset($body['output_format']['container']) || !in_array($body['output_format']['container'], ['raw', 'mp3'], true)) {
            throw new \InvalidArgumentException("O formato de saída é inválido. Deve ser 'raw' ou 'mp3'.");
        }

        if (!isset($body['output_format']['encoding']) || !in_array($body['output_format']['encoding'], ['pcm_s16le', 'mp3'], true)) {
            throw new \InvalidArgumentException("A codificação de saída é inválida. Deve ser 'pcm_s16le' ou 'mp3'.");
        }

        if (empty($body['output_format']['sample_rate']) || !in_array($body['output_format']['sample_rate'], [8000, 16000, 44100], true)) {
            throw new \InvalidArgumentException("A taxa de amostragem deve ser 8000, 16000 ou 44100.");
        }
    }

    private function handleErrors(ResponseInterface $response): void
    {
        if ($response->getStatusCode() >= 400) {
            $body = json_decode((string)$response->getBody(), true);
            throw new \Exception($body['message'] ?? 'Erro desconhecido');
        }
    }
}
