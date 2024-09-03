---
title: 'Installation'
---

# Instalação

Para começar a usar o Cartesia PHP SDK, siga estas etapas simples:

## Requisitos

- PHP 7.4 ou superior
- Composer

## Instalação via Composer

A maneira recomendada de instalar o Cartesia PHP SDK é através do Composer. Execute o seguinte comando no terminal:

```bash
composer require lucianotonet/cartesia-php
```

## Configuração

Após a instalação, você precisará configurar sua chave de API. Recomendamos usar variáveis de ambiente para armazenar informações sensíveis.

1. Crie um arquivo `.env` na raiz do seu projeto (se ainda não existir).
2. Adicione sua chave de API ao arquivo `.env`:

CARTESIA_API_KEY=sua_chave_api_aqui

3. Certifique-se de que o arquivo `.env` está listado no seu `.gitignore` para evitar compartilhar informações sensíveis.

## Uso Básico

Aqui está um exemplo simples de como usar o SDK:

```php
require 'vendor/autoload.php';

use Dotenv\Dotenv;
use LucianoTonet\CartesiaPHP\CartesiaClient;

// Carrega as variáveis de ambiente
$dotenv = Dotenv::createImmutable(DIR);
$dotenv->load();

// Cria uma instância do cliente Cartesia
$client = new CartesiaClient($ENV['CARTESIA_API_KEY']);
// Agora você pode usar $client para interagir com a API da Cartesia
```

Para mais informações sobre como usar o SDK, consulte nossa [Documentação de Início Rápido](./quick-start.md).

