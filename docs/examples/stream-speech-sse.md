---
title: "Exemplo: Stream de Fala (SSE)"
---

# Exemplo: Como Usar Stream de Fala (SSE)

## Introdução

Este exemplo demonstra como utilizar o método de streaming de fala via Server-Sent Events (SSE) do `CartesiaClient`. SSE é uma tecnologia que permite ao servidor enviar atualizações automáticas para o cliente, ideal para fluxos contínuos como a síntese de fala.

## Código de Exemplo

```php
use LucianoTonet\CartesiaPHP\CartesiaClient;

$client = new CartesiaClient('your-api-key');

try {
    $response = $client->streamSpeechSSE([
        'text' => 'Este é um exemplo de streaming de fala via SSE.',
        'voice' => 'pt-BR-Wavenet-A'
    ]);

    $sseStream = $response->getBody();

    // Ler o stream em tempo real
    while (!$sseStream->eof()) {
        $chunk = $sseStream->read(1024);
        echo $chunk; // Processar o chunk conforme necessário
    }

} catch (Exception $e) {
    echo "Erro ao realizar o streaming de fala via SSE: " . $e->getMessage() . PHP_EOL;
}
```

## Explicação do Código

1. **Inicialização do Cliente:**
   - O cliente é configurado com a chave de API necessária para acessar a API da Cartesia.

2. **Realizando o Streaming de Fala via SSE:**
   - O método `streamSpeechSSE()` inicia o streaming de síntese de fala, retornando um stream SSE que pode ser lido em tempo real.

3. **Processamento do Stream:**
   - Utilizamos um loop para ler o stream em pedaços (`chunks`) de 1024 bytes. Esses pedaços podem ser processados conforme necessário.

4. **Tratamento de Erros:**
   - O código está protegido por um bloco `try-catch` para capturar possíveis exceções e garantir que erros sejam manuseados adequadamente.

## Resultado Esperado

Este exemplo realiza a transmissão em tempo real do texto convertido em fala através de SSE. A saída deve ser processada em tempo real no lado do cliente.

Um exemplo completo de como receber e processar o stream SSE no cliente pode ser visto [aqui](/examples/stream_speech_sse.js), e como montar os eventos SSE no servidor [aqui](/examples/stream_speech_sse_server.php).
