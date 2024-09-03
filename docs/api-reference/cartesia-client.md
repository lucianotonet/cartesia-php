---
title: "Referência da API do CartesiaClient"
---

# Referência da API do CartesiaClient

## Introdução

O `CartesiaClient` é a classe principal para interagir com a API da Cartesia. Ele permite operações como criação, atualização, exclusão e clonagem de vozes, além de outras funcionalidades relacionadas.

### Inicialização

```php
use LucianoTonet\CartesiaPHP\CartesiaClient;

$client = new CartesiaClient('your-api-key', [
    'baseUrl' => 'https://api.cartesia.ai'
]);
```

## Métodos Disponíveis

### `cloneVoiceClip`

Clona um clipe de voz.

**Assinatura:**

```php
public function cloneVoiceClip(array $body): ResponseInterface
```

**Parâmetros:**

- `body` (array): Dados do clipe de voz a ser clonado. Deve conter o campo `clip`.

**Retorno:**

- `ResponseInterface`: A resposta da API.

**Exemplo de Uso:**

```php
$response = $client->cloneVoiceClip([
    'clip' => $audioData
]);
```

### `deleteVoice`

Exclui uma voz pelo ID.

**Assinatura:**

```php
public function deleteVoice(string $voiceId): ResponseInterface
```

**Parâmetros:**

- `voiceId` (string): ID da voz a ser excluída.

**Retorno:**

- `ResponseInterface`: A resposta da API.

**Exemplo de Uso:**

```php
$response = $client->deleteVoice('voice-id');
```

### `updateVoice`

Atualiza uma voz existente.

**Assinatura:**

```php
public function updateVoice(string $voiceId, array $body): ResponseInterface
```

**Parâmetros:**

- `voiceId` (string): ID da voz a ser atualizada.
- `body` (array): Dados para atualizar a voz. Deve conter os campos `id`, `name`, e `description`.

**Retorno:**

- `ResponseInterface`: A resposta da API.

**Exemplo de Uso:**

```php
$response = $client->updateVoice('voice-id', [
    'name' => 'New Voice Name',
    'description' => 'Updated description'
]);
```

### `createVoice`

Cria uma nova voz.

**Assinatura:**

```php
public function createVoice(array $body): ResponseInterface
```

**Parâmetros:**

- `body` (array): Dados para criar a nova voz. Deve conter os campos `name`, `description`, e `embedding`.

**Retorno:**

- `ResponseInterface`: A resposta da API.

**Exemplo de Uso:**

```php
$response = $client->createVoice([
    'name' => 'New Voice',
    'description' => 'Voice description',
    'embedding' => [0.1, 0.5, ...]
]);
```

## Constantes

O `CartesiaClient` define várias constantes para facilitar o uso de parâmetros experimentais:

- `EMOTION_HAPPY`: Indica uma emoção feliz.
- `EMOTION_SAD`: Indica uma emoção triste.
- `SPEED_FASTEST`: Indica a velocidade mais rápida.
- `SPEED_SLOWEST`: Indica a velocidade mais lenta.

E assim por diante.

## Exceções

A classe pode lançar as seguintes exceções:

- `CartesiaClientException`: Para erros relacionados ao cliente.
- `GuzzleException`: Para erros na requisição HTTP.
---

Essa é uma documentação básica que cobre as funcionalidades principais. Exemplos mais detalhados podem ser adicionados conforme necessário.
