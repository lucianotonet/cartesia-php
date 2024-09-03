---
title: "Exemplo: Stream de Fala (WebSockets)"
---

# Exemplo: Como Usar Stream de Fala (WebSockets)

## Introdução

Este exemplo mostra como realizar o streaming de síntese de fala utilizando WebSockets com o `CartesiaClient`. WebSockets são ideais para comunicação bidirecional em tempo real entre cliente e servidor.

## Código de Exemplo

```php
use LucianoTonet\CartesiaPHP\CartesiaClient;

$client = new CartesiaClient('your-api-key');

try {
    $wsConnection = $client->streamSpeechWebSocket([
        'text' => 'Este é um exemplo de streaming de fala via WebSocket.',
        'voice' => 'pt-BR-Wavenet-A'
    ]);

    $wsConnection->on('message', function($message) {
        // Processa a mensagem recebida, que pode ser um chunk de áudio
        echo $message->getData(); // Exemplo de processamento
    });

    $wsConnection->on('close', function() {
        echo "Conexão WebSocket encerrada." . PHP_EOL;
    });

    // Manter a conexão aberta até que todas as mensagens sejam recebidas
    $wsConnection->run();

} catch (Exception $e) {
    echo "Erro ao realizar o streaming de fala via WebSocket: " . $e->getMessage() . PHP_EOL;
}
```

## Explicação do Código

1. **Inicialização do Cliente:**
   - O cliente é configurado com a chave de API necessária para conectar-se à API da Cartesia.

2. **Realizando o Streaming de Fala via WebSocket:**
   - O método `streamSpeechWebSocket()` inicia uma conexão WebSocket, retornando um objeto de conexão que pode ser usado para manipular mensagens em tempo real.

3. **Manipulação de Mensagens:**
   - Usamos eventos como `on('message')` para processar cada mensagem recebida, que pode conter dados de áudio.

4. **Fechamento da Conexão:**
   - O evento `on('close')` é utilizado para lidar com o encerramento da conexão WebSocket.

5. **Tratamento de Erros:**
   - Um bloco `try-catch` garante que qualquer erro seja capturado e tratado adequadamente.

## Resultado Esperado

Ao executar este exemplo, uma conexão WebSocket é estabelecida para realizar o streaming de fala em tempo real. As mensagens de áudio recebidas serão processadas e a conexão será mantida aberta até que todo o conteúdo seja transmitido.

Este exemplo é ideal para aplicações que requerem comunicação em tempo real e bidirecional.
