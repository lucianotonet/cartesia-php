---
title: "Exemplo: Listar Vozes"
---

# Exemplo: Como Listar Vozes

## Introdução

Este exemplo mostra como listar todas as vozes disponíveis utilizando o `CartesiaClient`. Listar vozes é uma operação comum que permite aos desenvolvedores visualizar e selecionar vozes específicas para tarefas de síntese de fala.

## Código de Exemplo

```php
use LucianoTonet\CartesiaPHP\CartesiaClient;

$client = new CartesiaClient('your-api-key');

try {
    $response = $client->listVoices();
    $voices = json_decode($response->getBody(), true);

    foreach ($voices as $voice) {
        echo "Nome: " . $voice['name'] . PHP_EOL;
        echo "Descrição: " . $voice['description'] . PHP_EOL;
        echo "ID: " . $voice['id'] . PHP_EOL;
        echo "Idioma: " . $voice['language'] . PHP_EOL;
        echo "------------------------" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Erro ao listar vozes: " . $e->getMessage() . PHP_EOL;
}
```

## Explicação do Código

1. **Inicialização do Cliente:**
   - O cliente é inicializado com a chave de API fornecida.

2. **Listando Vozes:**
   - O método `listVoices()` é chamado para obter a lista de vozes disponíveis. A resposta é decodificada de JSON para um array PHP.

3. **Iterando sobre as Vozes:**
   - Para cada voz na lista, o código imprime o nome, descrição, ID, e idioma da voz.

4. **Tratamento de Erros:**
   - O código está envolvido em um bloco `try-catch` para capturar e exibir qualquer erro que ocorra durante o processo de listagem de vozes.

## Resultado Esperado

A execução do código acima deve produzir uma lista de vozes disponíveis, com suas respectivas descrições e outras informações relevantes, impressas no console.

```
Nome: Voz Exemplo
Descrição: Esta é uma voz de exemplo.
ID: voz-exemplo-id
Idioma: en-US------------------------
...
```

Este exemplo fornece uma visão básica de como utilizar o método `listVoices` para obter e manipular a lista de vozes na API da Cartesia.
