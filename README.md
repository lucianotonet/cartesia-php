
# Cartesia PHP

PHP library to access Cartesia REST API.

## Instalação

Para instalar, use o Composer:

```bash
composer require lucianotonet/cartesia-php
```

## Uso

```php
require 'vendor/autoload.php';

use LucianoTonet\CartesiaPHP\CartesiaClient;

// Inicializar o cliente
$client = new CartesiaClient('your_api_key');

// Usar a função de Text-to-Speech
$response = $client->streamSpeechBytes([
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

// Salvar o áudio
file_put_contents('sonic.wav', $response->getBody());
```

## Endpoints Suportados

- `streamSpeechBytes`
- `listVoices`
- `getVoice`
- `updateVoice`
- `deleteVoice`
- `apiStatus`
- `streamSpeechWebSocket`

## Testes

Para rodar os testes, utilize:

```bash
vendor/bin/phpunit
```

## Clonar Clip de Voz

```php
require 'vendor/autoload.php';

use LucianoTonet\CartesiaPHP\CartesiaClient;

$client = new CartesiaClient('your_api_key');

$response = $client->cloneVoiceClip([
    'source_voice_id' => 'source_voice_id',
    'target_voice_id' => 'target_voice_id'
]);

echo $response->getBody();
```

## Deletar Voz

```php
$response = $client->deleteVoice('voice_id');
echo $response->getBody();
```

## Atualizar Voz

```php
$response = $client->updateVoice('voice_id', [
    'name' => 'New Voice Name',
    'description' => 'Updated description'
]);

echo $response->getBody();
```
