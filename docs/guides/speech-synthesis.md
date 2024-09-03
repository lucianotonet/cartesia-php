---
title: "Síntese de Fala"
---

# Guia de Síntese de Fala

## Introdução

A síntese de fala é o processo de conversão de texto em fala (TTS - Text-to-Speech). Este pacote PHP permite que você interaja com a API da Cartesia para gerar áudio a partir de texto utilizando diferentes vozes e ajustes de parâmetros.

## Configuração Inicial

Para realizar a síntese de fala, primeiro você precisa configurar o cliente Cartesia:

```php
use LucianoTonet\CartesiaPHP\CartesiaClient;

$client = new CartesiaClient('your-api-key', [
    'baseUrl' => 'https://api.cartesia.ai'
]);
```

## Métodos Disponíveis para Síntese de Fala

### `synthesizeSpeech`

Este método permite converter texto em fala.

**Assinatura:**

```php
public function synthesizeSpeech(array $body): ResponseInterface
```

**Parâmetros:**

- `body` (array): Dados para a síntese de fala. Deve conter:
  - `text` (string): O texto que será convertido em fala.
  - `voice` (string): A voz a ser utilizada para a síntese.
  - `speed` (string, opcional): A velocidade da fala, podendo ser `slowest`, `slow`, `normal`, `fast`, ou `fastest`.
  - `emotion` (string, opcional): A emoção da fala, como `happy` ou `sad`.

**Retorno:**

- `ResponseInterface`: A resposta da API contendo os bytes de áudio gerados.

**Exemplo de Uso:**

```php
$response = $client->synthesizeSpeech([
    'text' => 'Hello, world!',
    'voice' => 'en-US-Wavenet-D',
    'speed' => 'normal',
    'emotion' => 'happy'
]);

// Salvando a resposta em um arquivo de áudio
file_put_contents('output.wav', $response->getBody());
```

### Ajustes de Parâmetros

Você pode ajustar a emoção e a velocidade da fala utilizando as constantes fornecidas pelo pacote:

```php
use LucianoTonet\CartesiaPHP\CartesiaClient;

$emotion = CartesiaClient::EMOTION_HAPPY;
$speed = CartesiaClient::SPEED_FASTEST;

$response = $client->synthesizeSpeech([
    'text' => 'This is a fast and happy speech!',
    'voice' => 'en-US-Wavenet-D',
    'speed' => $speed,
    'emotion' => $emotion
]);

file_put_contents('fast_happy_output.wav', $response->getBody());
```

## Exemplos Práticos

### Exemplo 1: Síntese de Fala com Voz Padrão

```php
$response = $client->synthesizeSpeech([
    'text' => 'Olá, este é um exemplo de síntese de fala!',
    'voice' => 'pt-BR-Wavenet-A'
]);

file_put_contents('exemplo_output.wav', $response->getBody());
```

### Exemplo 2: Ajustando a Velocidade e Emoção

```php
$response = $client->synthesizeSpeech([
    'text' => 'Este é um exemplo de fala rápida e animada.',
    'voice' => 'pt-BR-Wavenet-A',
    'speed' => CartesiaClient::SPEED_FAST,
    'emotion' => CartesiaClient::EMOTION_HAPPY
]);

file_put_contents('fast_happy_speech.wav', $response->getBody());
```
---

Este guia fornece uma visão geral básica de como realizar a síntese de fala usando o pacote CartesiaPHP. Para mais detalhes, consulte a referência completa da API.
