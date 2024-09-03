---
title: "Início Rápido"
---

# Início Rápido

## Introdução

O `CartesiaPHP` é um pacote PHP que permite interagir facilmente com a API da Cartesia para realizar operações como síntese de fala, gerenciamento de vozes, e muito mais. Este guia rápido irá mostrar como começar a usar o pacote em poucos passos.

## Instalação

Você pode instalar o `CartesiaPHP` usando o Composer, o gerenciador de dependências para PHP.

```bash
composer require lucianotonet/cartesia-php
```

## Configuração

Após instalar o pacote, você precisa configurar o cliente fornecendo sua chave de API.

```php
use LucianoTonet\CartesiaPHP\CartesiaClient;

$client = new CartesiaClient('your-api-key', [
    'baseUrl' => 'https://api.cartesia.ai'
]);
```

Substitua `'your-api-key'` pela sua chave de API real.

## Exemplo Básico

Aqui está um exemplo simples de como listar todas as vozes disponíveis utilizando o `CartesiaClient`.

```php
try {
    $response = $client->listVoices();
    $voices = json_decode($response->getBody(), true);

    foreach ($voices as $voice) {
        echo "Nome: " . $voice['name'] . PHP_EOL;
        echo "Descrição: " . $voice['description'] . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Erro ao listar vozes: " . $e->getMessage() . PHP_EOL;
}
```

## Próximos Passos

Depois de configurar o cliente e testar a listagem de vozes, você pode explorar outras funcionalidades como criar, atualizar e excluir vozes, ou realizar síntese de fala. Confira a documentação completa para mais detalhes.

---

Este guia rápido deve ajudá-lo a começar a usar o `CartesiaPHP` em seus projetos. Para mais informações, consulte os guias e exemplos detalhados na documentação.
