---
title: "Gerenciamento de Vozes"
---

# Guia de Gerenciamento de Vozes

## Introdução

O gerenciamento de vozes envolve a criação, atualização, listagem e exclusão de vozes dentro da API da Cartesia. Este guia fornece uma visão detalhada de como realizar essas operações utilizando o `CartesiaClient`.

## Métodos Disponíveis para Gerenciamento de Vozes

### `createVoice`

Cria uma nova voz na API.

**Assinatura:**

```php
public function createVoice(array $body): ResponseInterface
```

**Parâmetros:**

- `body` (array): Dados para criar a nova voz. Deve conter:
  - `name` (string): Nome da nova voz.
  - `description` (string): Descrição da nova voz.
  - `embedding` (array): Array de números representando a embedding da voz.

**Retorno:**

- `ResponseInterface`: A resposta da API com os detalhes da nova voz criada.

**Exemplo de Uso:**

```php
$response = $client->createVoice([
    'name' => 'Minha Nova Voz',
    'description' => 'Descrição da nova voz',
    'embedding' => [0.1, 0.5, 0.9]
]);

$voiceDetails = json_decode($response->getBody(), true);
```

### `updateVoice`

Atualiza os detalhes de uma voz existente.

**Assinatura:**

```php
public function updateVoice(string $voiceId, array $body): ResponseInterface
```

**Parâmetros:**

- `voiceId` (string): ID da voz a ser atualizada.
- `body` (array): Dados para atualizar a voz. Deve conter:
  - `name` (string): Novo nome da voz.
  - `description` (string): Nova descrição da voz.

**Retorno:**

- `ResponseInterface`: A resposta da API com os detalhes da voz atualizada.

**Exemplo de Uso:**

```php
$response = $client->updateVoice('voice-id', [
    'name' => 'Nome Atualizado',
    'description' => 'Descrição atualizada'
]);

$updatedVoiceDetails = json_decode($response->getBody(), true);
```

### `deleteVoice`

Exclui uma voz da API.

**Assinatura:**

```php
public function deleteVoice(string $voiceId): ResponseInterface
```

**Parâmetros:**

- `voiceId` (string): ID da voz a ser excluída.

**Retorno:**

- `ResponseInterface`: A resposta da API indicando o sucesso ou falha da operação.

**Exemplo de Uso:**

```php
$response = $client->deleteVoice('voice-id');

if ($response->getStatusCode() === 204) {
    echo 'Voz excluída com sucesso!';
}
```

### `listVoices`

Lista todas as vozes disponíveis.

**Assinatura:**

```php
public function listVoices(): ResponseInterface
```

**Retorno:**

- `ResponseInterface`: A resposta da API contendo uma lista de vozes.

**Exemplo de Uso:**

```php
$response = $client->listVoices();

$voices = json_decode($response->getBody(), true);
foreach ($voices as $voice) {
    echo $voice['name'] . ' - ' . $voice['description'] . PHP_EOL;
}
```

## Exemplos Práticos

### Exemplo 1: Criando e Listando Vozes

```php
// Criar uma nova voz
$createResponse = $client->createVoice([
    'name' => 'Nova Voz de Exemplo',
    'description' => 'Uma voz criada para fins de demonstração.',
    'embedding' => [0.2, 0.4, 0.6]
]);

// Listar vozes disponíveis
$listResponse = $client->listVoices();
$voices = json_decode($listResponse->getBody(), true);

foreach ($voices as $voice) {
    echo $voice['name'] . ' - ' . $voice['description'] . PHP_EOL;
}
```

### Exemplo 2: Atualizando e Excluindo uma Voz

```php
// Atualizar uma voz existente
$updateResponse = $client->updateVoice('voice-id', [
    'name' => 'Voz Atualizada',
    'description' => 'Descrição atualizada para a voz.'
]);

// Excluir uma voz
$deleteResponse = $client->deleteVoice('voice-id');
if ($deleteResponse->getStatusCode() === 204) {
    echo 'Voz excluída com sucesso!';
}
```
---

Este guia cobre as operações básicas de gerenciamento de vozes utilizando o `CartesiaClient`. Para mais detalhes e exemplos avançados, consulte a documentação completa da API.
