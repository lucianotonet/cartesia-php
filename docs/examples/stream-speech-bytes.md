---
title: "Exemplo: Stream de Fala (Bytes)"
---

# Exemplo: Como Usar Stream de Fala (Bytes)

## Introdução

Este exemplo mostra como realizar o streaming de síntese de fala utilizando o método de bytes do `CartesiaClient`. Isso permite a transmissão contínua de dados de áudio enquanto eles estão sendo gerados.

## Código de Exemplo

```php
use LucianoTonet\CartesiaPHP\CartesiaClient;

$client = new CartesiaClient('your-api-key');

try {
    $response = $client->streamSpeechBytes([
        'text' => 'Olá, este é um exemplo de streaming de fala!',
        'voice' => 'pt-BR-Wavenet-A'
    ]);

    $audioStream = $response->getBody();

    // Salvar o stream em um arquivo de áudio
    file_put_contents('output_stream.wav', $audioStream);

} catch (Exception $e) {
    echo "Erro ao realizar o streaming de fala: " . $e->getMessage() . PHP_EOL;
}
```

## Explicação do Código

1. **Inicialização do Cliente:**
   - O cliente é inicializado com a chave de API fornecida.

2. **Realizando o Streaming de Fala:**
   - O método `streamSpeechBytes()` é chamado para iniciar o streaming de síntese de fala com o texto e a voz especificados. A resposta da API é um stream de bytes de áudio.

3. **Salvando o Stream de Áudio:**
   - O stream de bytes de áudio é salvo diretamente em um arquivo `.wav` usando `file_put_contents`.

4. **Tratamento de Erros:**
   - O código está envolvido em um bloco `try-catch` para capturar e exibir qualquer erro que ocorra durante o processo de streaming.

## Resultado Esperado

A execução do código acima deve produzir um arquivo de áudio `output_stream.wav` contendo o texto sintetizado.

Este exemplo fornece uma visão básica de como utilizar o método `streamSpeechBytes` para realizar a síntese de fala e transmitir os dados de áudio em tempo real.
